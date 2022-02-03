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
