# ConstSchema

> **Deprecated**: Use [ConstSchema](ConstSchema.md) instead. The `ConstSchema` and `const()` method are deprecated and will be removed in a future version.

The `ConstSchema` validates that a value matches an exact const value. It supports string, integer, float, and boolean consts.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

// String const
$schema = $p->const('email');
$data = $schema->parse('email'); // Returns: 'email'

// Integer const
$schema = $p->const(42);
$data = $schema->parse(42); // Returns: 42

// Boolean const
$schema = $p->const(true);
$data = $schema->parse(true); // Returns: true
```

## Supported Types

```php
$p->const('string');  // String const
$p->const(42);        // Integer const
$p->const(3.14);      // Float const
$p->const(true);      // Boolean const
$p->const(false);     // Boolean const
```

## Common Patterns

### Type Discriminators

Used with `DiscriminatedUnionSchema` to identify object types:

```php
$contactSchema = $p->discriminatedUnion([
    $p->object([
        'type' => $p->const('email'),
        'address' => $p->string()->email(),
    ]),
    $p->object([
        'type' => $p->const('phone'),
        'number' => $p->string(),
    ]),
], 'type');
```

### Status Values

```php
$statusSchema = $p->union([
    $p->const('pending'),
    $p->const('approved'),
    $p->const('rejected'),
]);

$statusSchema->parse('approved'); // Returns: 'approved'
$statusSchema->parse('invalid');  // Throws error
```

### Magic Numbers

```php
$httpOkSchema = $p->const(200);
$httpNotFoundSchema = $p->const(404);

$responseSchema = $p->object([
    'status' => $p->union([
        $p->const(200),
        $p->const(201),
        $p->const(400),
        $p->const(404),
        $p->const(500),
    ]),
    'body' => $p->string(),
]);
```

### Boolean Flags

```php
$enabledSchema = $p->const(true);
$disabledSchema = $p->const(false);

// Only accept explicitly true
$mustBeEnabled = $p->object([
    'feature' => $p->const(true),
]);
```

### Sort Direction

```php
$sortSchema = $p->record(
    $p->union([
        $p->const('asc'),
        $p->const('desc'),
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
    'version' => $p->const('2.0'),
    'method' => $p->string(),
    'params' => $p->record($p->string()),
]);
```

### Null Const

While `nullable()` is preferred for optional null values, you can use const for explicit null:

```php
$nullSchema = $p->const(null);

// Useful in unions where null has specific meaning
$valueOrNotSet = $p->union([
    $p->string(),
    $p->const(null),
]);
```

## Const vs Enum

Use **ConstSchema** when:
- You have a single specific value
- You're building discriminators for unions
- You need to match magic numbers or specific strings

Use **BackedEnumSchema** when:
- You have multiple related values
- You want PHP enum type safety
- The values represent a closed set of options

```php
// Const: single value or ad-hoc unions
$type = $p->const('user');
$direction = $p->union([$p->const('asc'), $p->const('desc')]);

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
| `const.type` | Value doesn't match the expected const |

The error will include the expected const value and the actual value received.
