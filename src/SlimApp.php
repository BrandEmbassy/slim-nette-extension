<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use ArrayAccess;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Throwable;
use function assert;
use function reset;

class SlimApp extends App
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param bool $silent
     *
     * @throws Throwable
     */
    public function run($silent = false): ResponseInterface
    {
        $response = parent::run($silent);

        $contentTypes = $response->getHeader('Content-Type');
        $contentType = reset($contentTypes);

        if ($contentType === 'text/html; charset=UTF-8' && $response->getBody()->getSize() === 0) {
            $response = $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        }

        if (!$silent) {
            $this->respond($response);
        }

        return $response;
    }


    /**
     * @return ContainerInterface&ArrayAccess<string, mixed>
     */
    public function getContainer(): ContainerInterface
    {
        $container = parent::getContainer();
        assert($container instanceof ArrayAccess);

        return $container;
    }
}
