# Migration Guide: slim-nette-extension NEON Structure

## Required NEON Structure

The slim-nette-extension expects a **strict 3-level nesting structure** for route definitions:

```yaml
slimApi:
    routes:
        {namespace}:      # Level 1: API namespace (e.g., "dfo", "engager", "internal")
            {version}:    # Level 2: API version (e.g., "3.0", "2.0", "1.0")
                {pattern}:  # Level 3: Route pattern (e.g., "/channels", "/posts", "")
                    {method}:   # HTTP method (get, post, put, delete, patch)
                        service: ClassName
                        middlewares: []
```

### Example of Correct Structure:

```yaml
slimApi:
    routes:
        dfo:
            '3.0':
                '':  # Root route at /dfo/3.0
                    get:
                        service: ListEndpointsAction
                        middlewares: []

                '/channels':  # Route at /dfo/3.0/channels
                    get:
                        service: ChannelGetListAction
                        middlewares: []
                    post:
                        service: ChannelCreateAction
                        middlewares:
                            - ValidationMiddleware

                '/channels/{channelId}':  # Route at /dfo/3.0/channels/{channelId}
                    get:
                        service: ChannelGetAction
                        middlewares: []
```

## Common Migration Issues

### Issue 1: Mixed Structure (HTTP Methods as Siblings of Route Patterns)

**WRONG** - HTTP methods and route patterns at same level:
```yaml
'3.0':
    get:  # ❌ HTTP method at version level
        service: SomeAction
    '/channels':  # ❌ Route pattern as sibling of HTTP method
        post:
            service: AnotherAction
```

**CORRECT** - Separate with empty string pattern:
```yaml
'3.0':
    '':  # ✅ Empty pattern for root route
        get:
            service: SomeAction
    '/channels':  # ✅ Route patterns at same level
        post:
            service: AnotherAction
```

### Issue 2: Incorrect Indentation

**WRONG** - Route pattern indented as child of empty pattern:
```yaml
'3.0':
    '':
        get:
            service: SomeAction
        '/channels':  # ❌ Indented 20 spaces (child of '')
            post:
                service: AnotherAction
```

**CORRECT** - Route patterns as siblings:
```yaml
'3.0':
    '':  # 16 spaces
        get:
            service: SomeAction
    '/channels':  # 16 spaces (sibling of '')
        post:
            service: AnotherAction
```

### Issue 3: Null or Missing Values

**WRONG** - Null values in route definitions:
```yaml
'/some-route':
    get: null  # ❌ Null value
    post:  # ❌ Missing service definition
```

**CORRECT** - Always provide complete definitions:
```yaml
'/some-route':
    get:
        service: SomeGetAction
        middlewares: []
    post:
        service: SomePostAction
        middlewares: []
```

## Migration Checklist for platform-backend

1. **Verify 3-level nesting**: namespace → version → pattern → method
2. **Fix indentation**: Route patterns starting with `/` should be at 16 spaces (siblings of `''`), not 20 spaces
3. **Remove mixed structures**: HTTP methods should never be siblings of route patterns
4. **Check for null values**: All route definitions must have `service` and `middlewares` keys
5. **Use empty string for root**: Root route at version level must use `''` as pattern

## Testing After Migration

Run integration tests to ensure all routes are registered correctly:

```bash
cd platform-backend
docker exec be_platform-backend php ./vendor/bin/phpunit -c tests/phpunit.xml --no-coverage --filter=EndToEndTest
```

## Benefits of Clean Structure

- ✅ Simpler code in slim-nette-extension (less complexity)
- ✅ Faster route registration (no mixed-structure detection)
- ✅ Clearer route definitions (explicit 3-level nesting)
- ✅ Better error messages (failures happen in NEON parsing, not runtime)
- ✅ Easier to understand and maintain
