# ObjectSchema

The `ObjectSchema` validates objects/DTOs with named fields. It can parse input data into `stdClass` objects or custom class instances.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

// Parse to stdClass
$schema = $p->object([
    'name' => $p->string(),
    'age' => $p->int(),
]);

$object = $schema->parse(['name' => 'John', 'age' => 30]);
// Returns: stdClass { name: 'John', age: 30 }
```

## Parsing to Custom Classes

```php
class User
{
    public string $name;
    public int $age;
}

$schema = $p->object([
    'name' => $p->string(),
    'age' => $p->int(),
], User::class);

$user = $schema->parse(['name' => 'John', 'age' => 30]);
// Returns: User instance with populated properties
```

## Supported Input Types

The `ObjectSchema` accepts multiple input formats:

- **Arrays** - Standard associative arrays
- **stdClass** - Anonymous objects
- **Traversable** - Objects implementing `\Traversable`
- **JsonSerializable** - Objects implementing `\JsonSerializable`

## Validations

### Strict Mode

By default, unknown fields are silently ignored. Use `strict()` to reject unknown fields:

```php
$schema = $p->object(['name' => $p->string()])->strict();

$schema->parse(['name' => 'John']);              // OK
$schema->parse(['name' => 'John', 'extra' => 1]); // Throws error
```

### Strict with Exceptions

Allow specific unknown fields to be stripped while rejecting others:

```php
$schema = $p->object(['name' => $p->string()])->strict(['_id', '_rev']);

$schema->parse(['name' => 'John', '_id' => '123']);     // OK, _id stripped
$schema->parse(['name' => 'John', 'unknown' => 'val']); // Throws error
```

### Optional Fields

Make certain fields optional (they won't appear in output if not provided):

```php
$schema = $p->object([
    'name' => $p->string(),
    'nickname' => $p->string(),
])->optional(['nickname']);

$schema->parse(['name' => 'John']);
// Returns: stdClass { name: 'John' } - no nickname property
```

## Schema Utilities

### Get Field Schema

Retrieve the schema for a specific field:

```php
$schema = $p->object(['name' => $p->string(), 'age' => $p->int()]);

$nameSchema = $schema->getFieldSchema('name'); // Returns StringSchema
```

### Extend Schema

Get all field schemas to extend or compose:

```php
$baseSchema = $p->object([
    'id' => $p->int(),
    'createdAt' => $p->dateTime(),
]);

$userSchema = $p->object([
    ...$baseSchema->getFieldToSchema(),
    'name' => $p->string(),
    'email' => $p->string()->email(),
]);
```

## Common Patterns

### API Request Validation

```php
$createUserSchema = $p->object([
    'name' => $p->string()->trim()->minLength(1)->maxLength(100),
    'email' => $p->string()->trim()->toLowerCase()->email(),
    'age' => $p->int()->gte(0)->lte(150),
])->strict();
```

### Nested Objects

```php
$addressSchema = $p->object([
    'street' => $p->string(),
    'city' => $p->string(),
    'zipCode' => $p->string()->match('/^\d{5}$/'),
]);

$personSchema = $p->object([
    'name' => $p->string(),
    'address' => $addressSchema,
]);

$personSchema->parse([
    'name' => 'John',
    'address' => [
        'street' => '123 Main St',
        'city' => 'Springfield',
        'zipCode' => '12345',
    ],
]);
```

### With Optional and Nullable

```php
$schema = $p->object([
    'id' => $p->int(),
    'name' => $p->string(),
    'bio' => $p->string()->nullable(),      // Can be null
    'website' => $p->string()->url(),        // Required if present
])->optional(['website']);                   // website field is optional

// Valid inputs:
$schema->parse(['id' => 1, 'name' => 'John', 'bio' => null]);
$schema->parse(['id' => 1, 'name' => 'John', 'bio' => 'Hello', 'website' => 'https://example.com']);
```

### Complex Real-World Example

```php
$petSchema = $p->object([
    'id' => $p->string()->uuidV4(),
    'name' => $p->string()->minLength(1),
    'vaccinations' => $p->array(
        $p->object([
            'name' => $p->string(),
            'date' => $p->dateTime(),
        ])
    ),
]);

$listRequestSchema = $p->object([
    'offset' => $p->int()->nonNegative(),
    'limit' => $p->int()->positive()->lte(100),
    'sort' => $p->record($p->literal('asc')),
    'items' => $p->array($petSchema),
]);
```

## Error Codes

| Code | Description |
|------|-------------|
| `object.type` | Value is not a valid object type |
| `object.strict` | Unknown field found in strict mode |

Field-level errors include the field name in the error path (e.g., `name`, `address.city`).
