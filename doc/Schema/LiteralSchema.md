# LiteralSchema

The `LiteralSchema` validates that a value matches an exact literal value. It supports string, integer, float, and boolean literals.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

// String literal
$schema = $p->literal('email');
$data = $schema->parse('email'); // Returns: 'email'

// Integer literal
$schema = $p->literal(42);
$data = $schema->parse(42); // Returns: 42

// Boolean literal
$schema = $p->literal(true);
$data = $schema->parse(true); // Returns: true
```

## Supported Types

```php
$p->literal('string');  // String literal
$p->literal(42);        // Integer literal
$p->literal(3.14);      // Float literal
$p->literal(true);      // Boolean literal
$p->literal(false);     // Boolean literal
```

## Common Patterns

### Type Discriminators

Used with `DiscriminatedUnionSchema` to identify object types:

```php
$contactSchema = $p->discriminatedUnion([
    $p->object([
        'type' => $p->literal('email'),
        'address' => $p->string()->email(),
    ]),
    $p->object([
        'type' => $p->literal('phone'),
        'number' => $p->string(),
    ]),
], 'type');
```

### Status Values

```php
$statusSchema = $p->union([
    $p->literal('pending'),
    $p->literal('approved'),
    $p->literal('rejected'),
]);

$statusSchema->parse('approved'); // Returns: 'approved'
$statusSchema->parse('invalid');  // Throws error
```

### Magic Numbers

```php
$httpOkSchema = $p->literal(200);
$httpNotFoundSchema = $p->literal(404);

$responseSchema = $p->object([
    'status' => $p->union([
        $p->literal(200),
        $p->literal(201),
        $p->literal(400),
        $p->literal(404),
        $p->literal(500),
    ]),
    'body' => $p->string(),
]);
```

### Boolean Flags

```php
$enabledSchema = $p->literal(true);
$disabledSchema = $p->literal(false);

// Only accept explicitly true
$mustBeEnabled = $p->object([
    'feature' => $p->literal(true),
]);
```

### Sort Direction

```php
$sortSchema = $p->record(
    $p->union([
        $p->literal('asc'),
        $p->literal('desc'),
    ])
);

$sortSchema->parse([
    'name' => 'asc',
    'date' => 'desc',
]);
```

### API Version

```php
$requestSchema = $p->object([
    'version' => $p->literal('2.0'),
    'method' => $p->string(),
    'params' => $p->record($p->string()),
]);
```

### Null Literal

While `nullable()` is preferred for optional null values, you can use literal for explicit null:

```php
$nullSchema = $p->literal(null);

// Useful in unions where null has specific meaning
$valueOrNotSet = $p->union([
    $p->string(),
    $p->literal(null),
]);
```

## Literal vs Enum

Use **LiteralSchema** when:
- You have a single specific value
- You're building discriminators for unions
- You need to match magic numbers or specific strings

Use **BackedEnumSchema** when:
- You have multiple related values
- You want PHP enum type safety
- The values represent a closed set of options

```php
// Literal: single value or ad-hoc unions
$type = $p->literal('user');
$direction = $p->union([$p->literal('asc'), $p->literal('desc')]);

// BackedEnum: related values with type safety
enum Direction: string {
    case Asc = 'asc';
    case Desc = 'desc';
}
$direction = $p->backedEnum(Direction::class);
```

## Error Codes

| Code | Description |
|------|-------------|
| `literal.type` | Value doesn't match the expected literal |

The error will include the expected literal value and the actual value received.
