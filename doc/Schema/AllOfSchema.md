# AllOfSchema

The `AllOfSchema` validates a value against **all** of the given schemas: parsing succeeds only when every schema matches. It covers the JSON Schema `allOf` keyword.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->allOf([
    $p->string()->minLength(2),
    $p->string()->maxLength(4),
]);

$data = $schema->parse('test');     // Returns: 'test'
$data = $schema->parse('too long'); // Throws error (maxLength fails)
$data = $schema->parse(42);         // Throws error (both fail, both reported)
```

## How It Works

1. Every schema validates the **original input** — coercions and transformations of the subschemas never leak, the input is returned unchanged (pure validation, like [NotSchema](NotSchema.md))
2. Unlike [UnionSchema](UnionSchema.md), which stops at the first success, **every** failing schema contributes its errors — all violations are reported at once, at their unchanged instance paths
3. An empty schema list is vacuously valid

## Common Patterns

### Combining Independent Constraints

```php
$schema = $p->allOf([
    $p->string()->pattern('^[a-z]+$', PatternDialect::ecma262),
    $p->not($p->const('forbidden')),
]);
```

### Conditional Constraints Per Key (JSON Schema `dependentSchemas`)

```php
// if "creditCard" is present, "billingAddress" is required
$schema = $p->allOf([
    $p->if($p->assoc(['creditCard' => $p->any()])->required(['creditCard'])->additionalProperties($p->any()))
        ->then($p->assoc(['billingAddress' => $p->string()])->required(['billingAddress'])->additionalProperties($p->any())),
]);
```

### Validation Plus Population

`allOf` is pure validation. To validate against several schemas **and** get a populated result, follow up with the populating schema in `postParse()`:

```php
$assocSchema = $p->assoc(['name' => $p->string()]);

$schema = $p->allOf([...])->postParse(static fn (mixed $output) => $assocSchema->parse($output));
```

## Edge Cases

- **Output**: always the unchanged input — a subschema's coercion (e.g. `int()` accepting `1.0`) does not alter the result.
- **Error volume**: with overlapping constraints, several schemas can report an error for the same violation; the errors keep their original codes and paths.
- **Empty list**: `allOf([])` accepts everything — the all-quantifier over zero schemas holds. The JSON Schema spec requires a non-empty `allOf` array, enforcing that is the compiler's job.
- **Schemas that cannot fail**: a subschema with `catch()` or `default()` may always succeed and then contributes nothing.

## Error Codes

`AllOfSchema` itself never produces an error — the failing subschemas' errors propagate unchanged.
