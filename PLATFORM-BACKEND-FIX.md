# Fix Plan: Remove Mixed-Structure Handling from slim-nette-extension

## Problem

The `de-154553-cherry-pick-v4` branch has complex code to handle "mixed structures" in NEON files, where HTTP methods and route patterns can be siblings. This code has bugs (null values being passed to `isVersionLevel()`) and adds unnecessary complexity.

## Solution

**Use the clean approach from `clean-structure-from-27613f4` branch** which expects NEON files to follow strict 3-level nesting.

## Implementation Steps

### Step 1: Switch to Clean Branch

The `clean-structure-from-27613f4` branch already has the correct, simple implementation:

```php
private function registerApi(string $apiNamespace, array $routes, bool $detectTyposInRouteConfiguration): void
{
    foreach ($routes as $routePattern => $routeData) {
        $this->routeRegister->register($apiNamespace, $routePattern, $routeData, $detectTyposInRouteConfiguration);
    }
}
```

This is all that's needed - no complex mixed-structure detection.

### Step 2: Validate platform-backend NEON Files

Use the validation script to find any structure issues:

```bash
cd /path/to/slim-nette-extension
php validate-neon-structure.php /path/to/platform-backend/application/Configs/api/dfo.neon
php validate-neon-structure.php /path/to/platform-backend/application/Configs/api/engager.neon
php validate-neon-structure.php /path/to/platform-backend/application/Configs/api/internal.neon
php validate-neon-structure.php /path/to/platform-backend/application/Configs/api/app.neon
```

### Step 3: Fix Any Issues Found

Common fixes needed:

#### Fix 1: Remove Mixed Structures

**Before (WRONG):**
```yaml
'3.0':
    get:  # HTTP method at version level
        service: SomeAction
    '/channels':  # Route pattern as sibling
        post:
            service: AnotherAction
```

**After (CORRECT):**
```yaml
'3.0':
    '':  # Add empty pattern for root route
        get:
            service: SomeAction
    '/channels':  # Route patterns are siblings
        post:
            service: AnotherAction
```

#### Fix 2: Fix Indentation

Route patterns starting with `/` must be at **16 spaces** (same level as `''`), not 20 spaces.

Use this command to find incorrect indentation:
```bash
grep -n "^                    '/" application/Configs/api/*.neon
```

If it finds matches, those routes are incorrectly indented as children instead of siblings.

#### Fix 3: Remove Null Values

Find null values:
```bash
grep -n ": null" application/Configs/api/*.neon
```

Replace with proper route definitions.

### Step 4: Test Changes

After fixing NEON files, test with platform-backend:

```bash
cd platform-backend
composer require brandembassy/slim-nette-extension:dev-clean-structure-from-27613f4
docker exec be_platform-backend php ./vendor/bin/phpunit -c tests/phpunit.xml --no-coverage --filter=CustomFieldDefinitionCreateOrUpdateActionEndToEndTest
```

### Step 5: Update platform-backend composer.json

Once validated, update the dependency:

```json
{
    "require": {
        "brandembassy/slim-nette-extension": "dev-clean-structure-from-27613f4 as 4.4"
    }
}
```

## What This Fixes

1. **TypeError with null values** - Simple code doesn't try to detect structure, just processes what's there
2. **Complexity** - Removes ~100 lines of complex mixed-structure handling code
3. **Performance** - Faster route registration without structure detection
4. **Maintainability** - Much easier to understand and debug

## Commits to Remove from de-154553-cherry-pick-v4

These commits added unnecessary complexity and should NOT be in the final branch:

- `5b46600` - fix: Skip null or empty route definitions in registerApi
- `7af64c5` - Fix infinite recursion in mixed structure handling
- `ee48ef6` - Fix mixed structure handling at all nesting levels
- `a086e4a` - Fix route registration to handle mixed structures
- `2c15615` - Fix route registration to handle version nesting
- `d396160` - Fix route registration for flattened structure with nested HTTP method definitions
- `7474071` - Upgrade patch to v8: Add service key validation in route normalization
- `e699c66` - Remove unnecessary route normalization code

## Benefits

✅ Simpler code (simple loop instead of complex recursion)
✅ Faster execution (no structure detection overhead)
✅ Clearer error messages (NEON parsing errors vs runtime errors)
✅ Easier maintenance (one simple method vs complex logic)
✅ Enforces best practices (3-level nesting structure)
