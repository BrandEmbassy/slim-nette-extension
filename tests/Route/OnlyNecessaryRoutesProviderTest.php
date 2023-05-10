<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Route;

use BrandEmbassy\MockeryTools\FileLoader;
use BrandEmbassy\Slim\Route\OnlyNecessaryRoutesProvider;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class OnlyNecessaryRoutesProviderTest extends TestCase
{
    public function testGetRoutesReturnsFilteredRoutes(): void
    {
        $sampleRequestUri = '/chat/1.0/brand/1000/channel/chat_f00189ec-6a4b-4c76-b504-03c7ebcadb9c?parameter=12345';

        $sampleRoutes = FileLoader::loadArrayFromJsonFile(__DIR__ . '/__fixtures__/all_routes.json');
        $expectedRoutes = FileLoader::loadArrayFromJsonFile(__DIR__ . '/__fixtures__/expected_routes.json');

        $provider = new OnlyNecessaryRoutesProvider();

        $routes = $provider->getRoutes($sampleRequestUri, $sampleRoutes, false);

        Assert::assertSame($expectedRoutes, $routes);
    }


    public function testGetRoutesReturnsAllRoutesWhenRequestUriIsNull(): void
    {
        $sampleRoutes = FileLoader::loadArrayFromJsonFile(__DIR__ . '/__fixtures__/all_routes.json');

        $provider = new OnlyNecessaryRoutesProvider();

        $routes = $provider->getRoutes(null, $sampleRoutes, false);

        Assert::assertSame($sampleRoutes, $routes);
    }
}
