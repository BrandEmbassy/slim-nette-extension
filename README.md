[![CircleCI](https://circleci.com/gh/BrandEmbassy/slim-nette-extension.svg?style=svg)](https://circleci.com/gh/BrandEmbassy/slim-nette-extension)
[![Total Downloads](https://poser.pugx.org/BrandEmbassy/slim-nette-extension/downloads)](https://packagist.org/packages/brandembassy/slim-nette-extension)
[![Latest Stable Version](https://poser.pugx.org/BrandEmbassy/slim-nette-extension/v/stable)](https://github.com/BrandEmbassy/slim-nette-extension/releases)

# Nette Extension for integration of SLIM for API

This extension brings the power of [Slim](https://www.slimframework.com/) for applications using [Nette DI](https://github.com/nette/di). It enables you to easily work with Slim middleware stack and develop your API easily.

The general idea has been discussed in this [article](https://petrhejna.org/blog/api-chain-of-responsibility-approach). (Czech language) 

## Philosophy

### Single Responsibility
The main idea is to delegate responsibilities of the code handling requests to separated middlewares. For example:
* authentication
* validation
* business logic

How middlewares in Slim work is described [here](https://www.slimframework.com/docs/v3/concepts/middleware.html).

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
```


### First API endpoint
Now let's say you want to make a REST endpoint creating channels, `[POST] /2.0/channels`

You need to define routes in the `slimApi` section in `config.neon`.

> **Both services and middlewares must be registered services in DI Container.**

```yaml
slimApi:
    handlers:
        notFoundHandler: App\NotFoundHandler # Called when route isn't matched by URL
        notAllowedHandler: App\NotAllowedHandler # Called when route isn't matched by method
        errorHandler: App\ApiErrorHandler # Called when unhandled exception bubbles out

    routes:
        "2.0": # Version of your API
            "/channels": # Matched URL will be "your-domain.org/2.0/channels"
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

## Migrating from v4.x to v5.x

Version 5.x introduced significant changes to the route configuration structure. Here's a guide to help you migrate.

### Configuration Location

**v4.x**: Routes and handlers were defined in the `parameters` section under your `apiDefinitionKey`:

```yaml
slimApi:
    apiDefinitionKey: api

parameters:
    api:
        handlers:
            notFound: App\NotFoundHandler
        routes:
            # ...
```

**v5.x**: Routes and handlers are now defined directly under the `slimApi` extension:

```yaml
slimApi:
    handlers:
        notFoundHandler: App\NotFoundHandler
    routes:
        # ...
```

### Route Structure

**v4.x**: Routes were nested as `api-name > version > url-pattern > method`:

```yaml
parameters:
    api:
        routes:
            new-api:           # API name
                "2.0":         # Version
                    '/channels':   # URL pattern (final URL: /new-api/2.0/channels)
                        post:
                            service: App\CreateChannelAction
                            middleware:
                                - App\AuthMiddleware
```

**v5.x**: Routes are now `api-namespace > url-pattern > method` (flattened structure):

```yaml
slimApi:
    routes:
        "api":                 # API namespace (used for middleware groups)
            '/channels':       # URL pattern (final URL: /api/channels)
                post:
                    service: App\CreateChannelAction
                    middlewares:
                        - App\AuthMiddleware
```

> **Note on versioning**: In v5.x, the version is no longer a separate configuration level. If you need versioned URLs, include the version in either:
> - The `apiPrefix` setting (under `slimApi:`): `apiPrefix: '/api/v2'`
> - The api-namespace: `"api/v2":`
> - The url-pattern: `"/v2/channels":`

### Middleware Key

**v4.x**: Used singular `middleware` key:
```yaml
middleware:
    - App\AuthMiddleware
```

**v5.x**: Uses plural `middlewares` key:
```yaml
middlewares:
    - App\AuthMiddleware
```

### Handler Names

**v4.x**: Handler names without "Handler" suffix:
```yaml
handlers:
    notFound: App\NotFoundHandler
    notAllowed: App\NotAllowedHandler
    error: App\ApiErrorHandler
```

**v5.x**: Handler names with "Handler" suffix:
```yaml
handlers:
    notFoundHandler: App\NotFoundHandler
    notAllowedHandler: App\NotAllowedHandler
    errorHandler: App\ApiErrorHandler
```

### New Features in v5.x

#### API Prefix
You can now set a global prefix for all routes:
```yaml
slimApi:
    apiPrefix: '/api/v1'
```

#### Middleware Groups
Define reusable middleware groups:
```yaml
slimApi:
    middlewareGroups:
        auth:
            - App\AuthMiddleware
            - App\RateLimitMiddleware
        logging:
            - App\LoggingMiddleware

    routes:
        "api":
            '/channels':
                post:
                    service: App\CreateChannelAction
                    middlewareGroups:
                        - auth
                        - logging
```

#### Ignore Version Middleware Group
Skip the API namespace middleware group for specific routes:
```yaml
slimApi:
    routes:
        "api":
            '/health':
                get:
                    service: App\HealthCheckAction
                    ignoreVersionMiddlewareGroup: true
```

#### Performance Settings
New settings for performance optimization:
```yaml
slimApi:
    slimConfiguration:
        settings:
            detectTyposInRouteConfiguration: true   # Validates route config (default: true)
            registerOnlyNecessaryRoutes: false      # Register only matching routes (default: false)
            useApcuCache: true                      # Use APCu for caching (default: true)
            disableUsingSlimContainer: false        # When true, uses Nette container instead of Slim container (default: false)
```

### Removed Features in v5.x

- `afterRouteMiddlewares` configuration option (present in v4.2) is not available in v5.x
- `apiDefinitionKey` is no longer used - configuration is now directly in `slimApi` section
- Controller-style routes (`type: controller`) have been removed
