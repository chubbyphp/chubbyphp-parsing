# UnionSchema

The `UnionSchema` validates values that can match one of several schemas. It tries each schema in order and returns the result of the first successful parse.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->union([
    $p->string(),
    $p->int(),
]);

$data = $schema->parse('hello'); // Returns: 'hello'
$data = $schema->parse(42);      // Returns: 42
```

## How It Works

The union schema attempts to parse the input against each schema in the order they are defined:

1. Try the first schema
2. If it fails, try the second schema
3. Continue until a schema succeeds or all schemas fail
4. If all fail, throw an error containing all validation failures

## Common Patterns

### String or Number

```php
$stringOrNumberSchema = $p->union([
    $p->string(),
    $p->int(),
    $p->float(),
]);
```

### Nullable Alternative

While `nullable()` is preferred for simple null handling, unions can express more complex nullability:

```php
$schema = $p->union([
    $p->string()->minLength(1),
    $p->const(null),
]);

$schema->parse('hello'); // Returns: 'hello'
$schema->parse(null);    // Returns: null
$schema->parse('');      // Throws error (empty string fails minLength)
```

### ID Types

```php
$idSchema = $p->union([
    $p->int()->positive(),
    $p->string()->uuidV4(),
]);

$idSchema->parse(123);                                    // Returns: 123
$idSchema->parse('550e8400-e29b-41d4-a716-446655440000'); // Returns: UUID string
```

### API Response

```php
$successSchema = $p->object([
    'status' => $p->const('success'),
    'data' => $p->object([...]),
]);

$errorSchema = $p->object([
    'status' => $p->const('error'),
    'message' => $p->string(),
]);

$responseSchema = $p->union([$successSchema, $errorSchema]);
```

### Mixed Array Items

```php
$mixedArraySchema = $p->array(
    $p->union([
        $p->string(),
        $p->int(),
        $p->bool(),
    ])
);

$mixedArraySchema->parse(['hello', 42, true, 'world']);
```

## Order Matters

The order of schemas matters! Put more specific schemas first:

```php
// Good: Specific first
$schema = $p->union([
    $p->int(),    // Will match integers
    $p->float(),  // Will match floats that aren't integers
]);

// Less ideal: Float would match integers too (in some cases)
$schema = $p->union([
    $p->float(),
    $p->int(),
]);
```

## Difference from DiscriminatedUnion

Use `union` when:
- Types are primitives or don't share a common discriminator
- You want "try until success" behavior

Use `discriminatedUnion` when:
- All types are objects with a shared discriminator field
- You want explicit type selection based on a field value

## Error Codes

| Code | Description |
|------|-------------|
| `union.type` | Value doesn't match any of the union schemas |

The error details will include all individual schema errors to help diagnose which validations failed.
