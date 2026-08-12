# REST API patterns

The boilerplate includes two deliberately different REST examples.

## Public read-only endpoint

`RestApiModule` demonstrates a public GET route:

```text
GET /wp-json/wp24h-boilerplate/v1/message
```

It uses `permission_callback => __return_true` because the response contains only intentionally public data.

Use this pattern only when the resource is genuinely public. A public permission callback must never be used as a shortcut around authentication for private or administrative data.

## Protected POST endpoint

`ProtectedRestModule` is disabled by default and demonstrates an administrative POST route:

```text
POST /wp-json/wp24h-boilerplate/v1/protected-message
```

The route requires the `manage_options` capability and defines validation and sanitization for its `message` argument.

The example intentionally separates three concerns:

1. **Authorization** — `can_manage()` decides whether the current user may use the endpoint.
2. **Validation** — `validate_message()` rejects empty, non-string or oversized input.
3. **Sanitization** — `sanitize_message()` normalizes accepted input through WordPress APIs.

The callback then receives the already-processed request value and returns a REST response.

## Example authenticated request

When testing with a WordPress authentication method that supports REST requests, send a JSON body such as:

```json
{
  "message": "Hello from an authenticated client"
}
```

Do not hard-code credentials in examples, scripts or repositories. Use an authentication mechanism appropriate to the target environment and keep secrets outside version control.

## Choosing a permission callback

Prefer the narrowest capability that represents the real operation. `manage_options` is used here because this is an administrative teaching example; a production plugin should normally define or reuse a capability aligned with the feature instead of automatically requiring administrator-level access.

## Scaffolding

When a plugin is generated with `composer scaffold`, the REST namespace, hooks, option keys and text domain are renamed from the target slug. Both REST modules therefore follow the generated plugin identity automatically.
