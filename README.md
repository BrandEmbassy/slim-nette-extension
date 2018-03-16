[![CircleCI](https://circleci.com/gh/BrandEmbassy/api-slim-nette-extension.svg?style=svg)](https://circleci.com/gh/BrandEmbassy/api-slim-nette-extension)

# Nette Extension for integration of SLIM for API

This extension brings power of [Slim](https://www.slimframework.com/) for applications using [Nette DI](https://github.com/nette/di). It enables you to easily work with Slim middleware stack and develop your API easily.

General idea is discussed in this [article](https://petrhejna.org/blog/api-chain-of-responsibility-approach). (Czech language) 

Let's start!
```
composer require brandembassy/api-slim-nette-extension
```

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

Now let's say you want create REST endpoint to creation Channels, `[POST] /new-api/2.0/channels`

You need define in `parameters.api` section in `config.neon`.

* Both services and middlewares must be registered services in DI Container.

```yaml
parameters:
    api:
        handlers:
            notFound: App\NotFoundHandler # Called when no route is matched by URL
            notAllowed: App\NotAllowedHandler # Called when route is not matched by method
            error: App\ApiErrorHandler # Called when unhandled exception bubbles out

        routes:
            new-api: # This is name of your API
                "2.0": # Version of your API
                    '/channels': # Matched URL will be "your-domain.org/new-api/2.0/channels"
                        post:
                            # This is service will be invoked to handle the request
                            service: App\CreateChannelAction
                            
                            # Here middleware stack is defined. It evaluate from bottom to top 
                            middleware:
                                - App\SomeOtherMiddleware # last in row
                                - App\UsuallyRequestDataValidationMiddleware # second in row
                                - App\SomeAuthMiddleware # this one is called first 
                                
```

See `tests/SlimApplicationFactoryTest.php` and `tests/config.neon` for more examples.
