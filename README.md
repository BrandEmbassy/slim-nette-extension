[![CircleCI](https://circleci.com/gh/BrandEmbassy/slim-nette-extension.svg?style=svg)](https://circleci.com/gh/BrandEmbassy/slim-nette-extension)
[![Total Downloads](https://poser.pugx.org/BrandEmbassy/slim-nette-extension/downloads)](https://packagist.org/packages/brandembassy/slim-nette-extension)
[![Latest Stable Version](https://poser.pugx.org/BrandEmbassy/slim-nette-extension/v/stable)](https://github.com/BrandEmbassy/slim-nette-extension/releases)

# Nette Extension for integration of SLIM for API

This extension brings the power of [Slim](https://www.slimframework.com/) for applications using [Nette DI](https://github.com/nette/di). It enables you to easily work with Slim middleware stack and develop your API easily.

**This package now uses Slim Framework 4.x**

The general idea has been discussed in this [article](https://petrhejna.org/blog/api-chain-of-responsibility-approach). (Czech language) 

## Philosophy

### Single Responsibility
The main idea is to delegate responsibilities of the code handling requests to separated middlewares. For example:
* authentication
* validation
* business logic

How middlewares in Slim work is described [here](https://www.slimframework.com/docs/v4/concepts/middleware.html).

### Easy configuration
Empowered by Nette DI and it's `neon` configuration syntax this package provides powerful and easy way to define your API.

## Usage
So let's start!
```
composer require brandembassy/slim-nette-extension
```

### Extension
Now register new extension by adding this code into your `config.neon`:
```yaml
extensions:
    slimApi: BrandEmbassy\Slim\DI\SlimApiExtension # Register extension

slimApi: # Configure it
    slimConfiguration:
        settings:
            removeDefaultHandlers: true # It's recommended to disable original error handling 
                                        # and use your own error handlers suited for needs of your app. 

    apiDefinitionKey: api # Your API definition will be under this key in "parameters" section. 
```


### First API endpoint
Now let's say you want to make a REST endpoint creating channels, `[POST] /new-api/2.0/channels`

You need to define in `parameters.api` section in `config.neon`.

> **Both services and middlewares must be registered services in DI Container.**

```yaml
slimApi:
    handlers:
        notFound: App\NotFoundHandler # Called when not route isn't matched by URL
        notAllowed: App\NotAllowedHandler # Called when route isn't matched by method
        error: App\ApiErrorHandler # Called when unhandled exception bubbles out

    routes:
        "2.0": # Version of your API
            "channels": # Matched URL will be "your-domain.org/2.0/channels"
                post:
                    # This is service will be invoked to handle the request
                    service: App\CreateChannelAction
                    
                    # Here middleware stack is defined. It's evaluated from bottom to top. 
                    middlewares:
                        - App\SomeOtherMiddleware # last in row
                        - App\UsuallyRequestDataValidationMiddleware # second in row
                        - App\SomeAuthMiddleware # this one is called first 

    beforeRouteMiddlewares:
        # this is called for each route, before route middlewares
        - App\SomeBeforeRouteMiddleware 
        
    afterRouteMiddlewares:
        # this is called for each route, after the route middlewares
        - App\SomeAfterRouteMiddleware

    beforeRequestMiddlewares:
        # this is called for each request, even when route does NOT exist (404 requests)
        - App\SomeBeforeRequestMiddleware
```

You can also reference the named service by its name.

See `tests/SlimApplicationFactoryTest.php` and `tests/config.neon` for more examples.

### Execution
Now you can simply get `SlimApplicationFactory` class from your DI Container (or better autowire it), create app and run it.

```php
$factory = $container->getByType(SlimApplicationFactory::class);
$factory->create()->run();
```

## Migrating from Slim 3 to Slim 4

Version 4.x of this package uses Slim Framework 4 instead of Slim Framework 3. The migration should be mostly transparent for users as the package maintains backward compatibility where possible.

### Key Changes

1. **Dependencies**: Slim 4 uses PSR-7, PSR-15, and PSR-17 standards more strictly
2. **Container**: Slim 4 no longer provides its own container, but the package provides a compatibility layer
3. **Middleware**: The double-pass middleware style (`$request, $response, $next`) is still supported
4. **Routing**: Routes are now registered on the App instance directly (handled internally by the package)

### What You Need to Do

For most users, the upgrade should be seamless:

1. Update your `composer.json` to require the new version
2. Run `composer update brandembassy/slim-nette-extension`
3. Clear your cache directories (`temp/`, `tests/temp/`)
4. Test your application

### Breaking Changes

- **RequestInterface**: 13 convenience methods removed (`getField`, `findField`, `hasField`, `findQueryParam`, `getQueryParamStrict`, `findQueryParamAsString`, `getQueryParamAsString`, `hasQueryParam`, `findAttribute`, `getAttributeStrict`, `hasAttribute`, `getDateTimeQueryParam`, `isHtml`, `getParsedBodyAsArray`). Use the underlying PSR-7 request methods directly (e.g. `getQueryParams()`, `getParsedBody()`, `getAttribute()`)
- **ResponseInterface**: 3 methods removed (`withJson()`, `withRedirect()`, `getParsedBodyAsArray()`). Consumers need their own JSON response helper instead of `withJson()`, use `withHeader('Location', $url)->withStatus(302)` instead of `withRedirect()`
- **JsonResponse removed**: The `BrandEmbassy\Slim\Response\JsonResponse` helper class has been removed. Consumers should implement their own JSON response utility
- **Exceptions removed**: `QueryParamMissingException`, `RequestFieldMissingException`, `RequestAttributeMissingException`
- **Dependency removed**: `adbario/php-dot-notation` no longer needed
- If you were directly accessing Slim internals (like `Slim\Container` or `Slim\Router`), you'll need to update your code
- Custom middleware that relied on Slim 3 specific features may need updates
- The package now requires PHP 8.2+

### Backward Compatibility

The following are maintained for backward compatibility:
- Request and Response types remain PSR-7 compatible; core PSR-7 methods are unchanged
- `getQueryParam()` on RequestInterface is deprecated but still available (190+ usages — migrate separately)
- Route methods on RequestInterface remain: `getRoute()`, `getRouteArguments()`, `hasRouteArgument()`, `getRouteArgument()`, `findRouteArgument()`
- Middleware signature remains the same (double-pass style)
- Route and handler registration via NEON configuration is unchanged
- Container access via `$app->getContainer()` works as before

For more details on Slim 4 changes, see the [official Slim 4 upgrade guide](https://www.slimframework.com/docs/v4/start/upgrade.html).
