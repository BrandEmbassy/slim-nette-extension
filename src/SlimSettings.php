<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

/**
 * @final
 */
class SlimSettings
{
    public const DETECT_TYPOS_IN_ROUTE_CONFIGURATION = 'detectTyposInRouteConfiguration';
    public const REGISTER_ONLY_NECESSARY_ROUTES = 'registerOnlyNecessaryRoutes';
    public const USE_APCU_CACHE = 'useApcuCache';
}
