[![CircleCI](https://circleci.com/gh/BrandEmbassy/slim-nette-extension.svg?style=svg)](https://circleci.com/gh/BrandEmbassy/slim-nette-extension)
[![Downloads this Month](https://img.shields.io/packagist/dm/BrandEmbassy/php-math-interval.svg)](https://packagist.org/packages/BrandEmbassy/slim-nette-extension)
[![Latest Stable Version](https://poser.pugx.org/BrandEmbassy/slim-nette-extension/v/stable)](https://github.com/BrandEmbassy/slim-nette-extension/releases)

# Nette Extension for integration of SLIM for API

This extension brings power of [Slim](https://www.slimframework.com/) for applications using [Nette DI](https://github.com/nette/di). It enables you to easily work with Slim middleware stack and develop your API easily.

General idea is discussed in this [article](https://petrhejna.org/blog/api-chain-of-responsibility-approach). (Czech language) 

## Philosophy

### Single Responsibility
The main idea is to delegate responsibilities of the code handling requests to separated middlewares. For example:
* authentication
* validation
* business logic

How middlewares in Slim work is described [here](https://www.slimframework.com/docs/v3/concepts/middleware.html).

### Easy configuration
Empowered by Nette DI and it's `neon` configuration syntax this package provides powerful and easy way how to define your API.

## Usage
So let's start!
```
composer require brandembassy/api-slim-nette-extension
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


### First API enpoint
Now let's say you want create REST endpoint to creation Channels, `[POST] /new-api/2.0/channels`

You need define in `parameters.api` section in `config.neon`.

> **Both services and middlewares must be registered services in DI Container.**

```yaml
parameters:
    api:
        handlers:
            notFound: App\NotFoundHandler # Called when not route is not matched by URL
            notAllowed: App\NotAllowedHandler # Called when not route is not matched by method
            error: App\ApiErrorHandler # Called when unhandled exception bubbles out

        routes:
            new-api: # This is name of your API
                "2.0": # Version of your API
                    '/channels': # Matched URL will be "your-domain.org/new-api/2.0/channels"
                        post:
                            # This is service will be invoked to handle the request
                            service: App\CreateChannelAction
                            
                            # Here middleware stack is defined. It's evaluated from bottom to top. 
                            middleware:
                                - App\SomeOtherMiddleware # last in row
                                - App\UsuallyRequestDataValidationMiddleware # second in row
                                - App\SomeAuthMiddleware # this one is called first 
                                
```

You can also reference the named service by it's name.

See `tests/SlimApplicationFactoryTest.php` and `tests/config.neon` for more examples.

### Execution
Now you can simply get `SlimApplicationFactory` class from your DI Container (or better autowire it), create app and run it.

```php
$factory = $container->getByType(SlimApplicationFactory::class);
$factory->create()->run();
```
