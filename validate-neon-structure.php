<?php declare(strict_types = 1);

/**
 * Script to validate NEON route structure
 *
 * Usage: php validate-neon-structure.php <path-to-neon-file>
 *
 * Checks for:
 * - Null values in route definitions
 * - Mixed structures (HTTP methods as siblings of route patterns)
 * - Missing required keys (service, middlewares)
 */

if ($argc < 2) {
    echo "Usage: php validate-neon-structure.php <path-to-neon-file>\n";
    exit(1);
}

require __DIR__ . '/vendor/autoload.php';

use Nette\Neon\Neon;

$file = $argv[1];
if (!file_exists($file)) {
    echo "Error: File not found: $file\n";
    exit(1);
}

$content = file_get_contents($file);
$data = Neon::decode($content);

$httpMethods = ['get', 'post', 'put', 'delete', 'patch', 'options', 'head'];
$errors = [];

function validateRoutes(array $routes, string $namespace, string $path = ''): array
{
    global $httpMethods, $errors;

    foreach ($routes as $key => $value) {
        $currentPath = $path . ' › ' . $key;

        // Check for null values
        if ($value === null) {
            $errors[] = "NULL value at: $currentPath";
            continue;
        }

        if (!is_array($value)) {
            continue;
        }

        // Check if this level has mixed structure (both HTTP methods and route patterns)
        $hasHttpMethod = false;
        $hasRoutePattern = false;

        foreach (array_keys($value) as $childKey) {
            if (in_array(strtolower((string)$childKey), $httpMethods, true)) {
                $hasHttpMethod = true;
            } else {
                $hasRoutePattern = true;
            }
        }

        if ($hasHttpMethod && $hasRoutePattern) {
            $errors[] = "MIXED STRUCTURE at: $currentPath (has both HTTP methods and route patterns as siblings)";
            $errors[] = "  → Fix: Add empty string pattern '' as parent for HTTP methods";
        }

        // Check if this is an HTTP method definition
        if (in_array(strtolower((string)$key), $httpMethods, true)) {
            // Validate HTTP method definition
            if (!isset($value['service'])) {
                $errors[] = "MISSING 'service' at: $currentPath";
            }
            if (!isset($value['middlewares'])) {
                $errors[] = "MISSING 'middlewares' at: $currentPath (should be array, can be empty [])";
            }
        } else {
            // Recurse into nested routes
            validateRoutes($value, $namespace, $currentPath);
        }
    }

    return $errors;
}

if (isset($data['slimApi']['routes'])) {
    foreach ($data['slimApi']['routes'] as $namespace => $versions) {
        if (!is_array($versions)) {
            echo "Warning: Namespace '$namespace' has no versions\n";
            continue;
        }

        validateRoutes($versions, $namespace, $namespace);
    }
}

if (empty($errors)) {
    echo "✅ No structure issues found in $file\n";
    exit(0);
} else {
    echo "❌ Found " . count($errors) . " issue(s) in $file:\n\n";
    foreach ($errors as $error) {
        echo "  $error\n";
    }
    exit(1);
}
