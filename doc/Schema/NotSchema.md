# NotSchema

The `NotSchema` inverts another schema: parsing succeeds when the wrapped schema **fails**, and fails when it succeeds. It covers the JSON Schema `not` keyword.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->not($p->string());

$data = $schema->parse(42);     // Returns: 42
$data = $schema->parse('test'); // Throws error (input matches the string schema)
```

## How It Works

1. The input is parsed against the wrapped schema
2. If the wrapped schema fails, the **original input is returned unchanged**
3. If the wrapped schema succeeds, a `not.match` error is thrown

`not` is pure validation: the wrapped schema's coercions and transformations never leak into the output, and its output type is `mixed` — "anything except X" has no narrower type.

## Common Patterns

### Forbidden Value

```php
$schema = $p->not($p->const('forbidden'));

$schema->parse('allowed');   // Returns: 'allowed'
$schema->parse('forbidden'); // Throws error
```

### Excluding a Shape

```php
$schema = $p->not($p->object([
    'deprecatedField' => $p->string(),
]));
```

### Record Values That Are Not Strings

```php
$schema = $p->record($p->not($p->string()));

$schema->parse(['key1' => 1, 'key2' => true]); // Returns: ['key1' => 1, 'key2' => true]
$schema->parse(['key1' => 'value1']);          // Throws error
```

## Edge Cases

- **`null` input**: `not($p->string())` accepts `null`, because `null` is not a string. To reject `null` as well, combine with another schema or use `not($p->union([...]))`.
- **`nullable()`**: as on every schema, `nullable()` short-circuits `null` before validation — `not($p->const(null))->nullable()` returns `null` even though the wrapped schema matches it.
- **Wrapped schemas that cannot fail**: a wrapped schema with `catch()` or `default()` may always succeed, which makes the `not` schema always fail.
- **Error details**: when `not` fails, the wrapped schema *succeeded*, so there are no inner errors to report — the single `not.match` error is all there is.

## Error Codes

| Code | Description |
|------|-------------|
| `not.match` | Input matches the wrapped schema |
