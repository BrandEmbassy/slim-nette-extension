<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim;

use BrandEmbassy\Slim\SlimApp;
use BrandEmbassy\Slim\SlimApplicationFactory;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use Psr\Http\Message\ResponseInterface;
use function assert;
use function md5;

/**
 * @final
 */
class SlimAppTester
{
    public static function createSlimApp(string $configPath = __DIR__ . '/config.neon'): SlimApp
    {
        $factory = self::createContainer($configPath)->getByType(SlimApplicationFactory::class);

        return $factory->create();
    }


    public static function runSlimApp(string $configPath = __DIR__ . '/config.neon'): ResponseInterface
    {
        $slimApp = self::createSlimApp($configPath);

        return $slimApp->run(true);
    }


    public static function createContainer(string $configPath = __DIR__ . '/config.neon'): Container
    {
        $loader = new ContainerLoader(__DIR__ . '/temp', true);
        $class = $loader->load(
            static function (Compiler $compiler) use ($configPath): void {
                $compiler->loadConfig($configPath);
                $compiler->addExtension('extensions', new ExtensionsExtension());
            },
            md5($configPath)
        );
        /** @var Container $container */
        $container = new $class();

        return $container;
    }
}
