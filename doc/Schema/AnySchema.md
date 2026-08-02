# AnySchema

The `AnySchema` accepts **any** value and returns it unchanged. It covers the JSON Schema boolean schema `true` and the empty schema `{}`.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->any();

$data = $schema->parse('test');           // Returns: 'test'
$data = $schema->parse(42);               // Returns: 42
$data = $schema->parse(null);             // Returns: null
$data = $schema->parse(['key' => 'value']); // Returns: ['key' => 'value']
```

## How It Works

1. The input is returned unchanged — no type check, no coercion, no transformation
2. Parsing itself never fails; only added `preParse()` / `postParse()` closures can throw

## Common Patterns

### Keep Unknown Fields Untouched

```php
$schema = $p->assoc([
    'name' => $p->string(),
])->additionalProperties($p->any());

$schema->parse(['name' => 'test', 'extra' => 42]); // Returns: ['name' => 'test', 'extra' => 42]
```

### Record With Validated Keys Only

```php
$schema = $p->record($p->any())->propertyNames($p->string()->pattern('^[a-z]+$', PatternDialect::ecma262));
```

### Tuple With Untyped Remainder

```php
$schema = $p->tuple([$p->string()])->rest($p->any());
```

### Reject Everything (JSON Schema `false`)

```php
$schema = $p->not($p->any());

$schema->parse('anything'); // Throws error
```

## Edge Cases

- **`null` input**: accepted without `nullable()` — `any` means any value, including `null`. `nullable()` is therefore redundant, but harmless.
- **`default()`**: still applies, since it replaces `null` input before parsing — `any()->default('fallback')` returns `'fallback'` for `null`.
- **Objects**: instances are returned as-is (same instance), not converted or cloned.

## Error Codes

`AnySchema` itself never produces an error — there are no error codes.
