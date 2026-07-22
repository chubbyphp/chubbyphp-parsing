# AssocSchema

The `AssocSchema` validates associative arrays with named fields. Unlike `ObjectSchema` which returns `stdClass` objects, `AssocSchema` returns associative arrays.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->assoc([
    'name' => $p->string(),
    'age' => $p->int(),
]);

$data = $schema->parse(['name' => 'John', 'age' => 30]);
// Returns: ['name' => 'John', 'age' => 30]
```

## Comparison with ObjectSchema

| Feature | AssocSchema | ObjectSchema |
|---------|-------------|--------------|
| Output type | `array<string, mixed>` | `stdClass` or custom class |
| Access syntax | `$data['name']` | `$data->name` |
| Custom class support | No | Yes |
| Use case | API responses, configs | DTOs, entities |

```php
// AssocSchema returns an array
$assocSchema = $p->assoc(['name' => $p->string()]);
$arr = $assocSchema->parse(['name' => 'John']);
echo $arr['name']; // 'John'

// ObjectSchema returns an object
$objectSchema = $p->object(['name' => $p->string()]);
$obj = $objectSchema->parse(['name' => 'John']);
echo $obj->name; // 'John'
```

## Supported Input Types

The `AssocSchema` accepts multiple input formats:

- **Arrays** - Standard associative arrays
- **stdClass** - Anonymous objects
- **Traversable** - Objects implementing `\Traversable`
- **JsonSerializable** - Objects implementing `\JsonSerializable`

## Validations

### Strict Mode

By default, unknown fields are silently ignored. Use `strict()` to reject unknown fields:

```php
$schema = $p->assoc(['name' => $p->string()])->strict();

$schema->parse(['name' => 'John']);              // OK
$schema->parse(['name' => 'John', 'extra' => 1]); // Throws error
```

### Strict with Exceptions

Allow specific unknown fields to be stripped while rejecting others:

```php
$schema = $p->assoc(['name' => $p->string()])->strict(['_id', '_rev']);

$schema->parse(['name' => 'John', '_id' => '123']);     // OK, _id stripped
$schema->parse(['name' => 'John', 'unknown' => 'val']); // Throws error
```

### Additional Properties

Like the JSON Schema `additionalProperties` keyword with a schema: fields without a field
schema are validated against the given schema and kept in the output. Without
`additionalProperties()` unknown fields are silently dropped, with `strict()` they are
rejected (the JSON Schema `additionalProperties: false` case) - combining the two throws
an `\InvalidArgumentException`:

```php
$schema = $p->assoc(['name' => $p->string()])->additionalProperties($p->float());

$schema->parse(['name' => 'John', 'score' => 1.5]);
// Returns: ['name' => 'John', 'score' => 1.5]

$schema->parse(['name' => 'John', 'score' => 'high']);
// Throws: score: Type should be "float", "string" given
```

### Required Fields

Like the JSON Schema `required` keyword: fields listed in `required()` must be present -
a missing one causes a dedicated `assoc.missingField` error. All other fields are
optional and won't appear in the output if not provided:

```php
$schema = $p->assoc([
    'name' => $p->string(),
    'nickname' => $p->string(),
])->required(['name']);

$schema->parse(['name' => 'John']);
// Returns: ['name' => 'John'] - no nickname key

$schema->parse(['nickname' => 'Johnny']);
// Throws: name: Missing field "name"
```

Without `required()` a missing field is parsed as null, so `nullable()` / `default()`
apply.

The deprecated `optional()` is the inverse: listed fields won't appear in the output if
not provided, while all other missing fields are parsed as null. Use `required()` instead.

## Schema Utilities

### Get Field Schema

Retrieve the schema for a specific field:

```php
$schema = $p->assoc(['name' => $p->string(), 'age' => $p->int()]);

$nameSchema = $schema->getFieldSchema('name'); // Returns StringSchema
```

### Extend Schema

Get all field schemas to extend or compose:

```php
$baseSchema = $p->assoc([
    'id' => $p->int(),
    'createdAt' => $p->dateTime(),
]);

$userSchema = $p->assoc([
    ...$baseSchema->getFieldToSchema(),
    'name' => $p->string(),
    'email' => $p->string()->email(),
]);
```

## Common Patterns

### API Response Parsing

```php
$responseSchema = $p->assoc([
    'status' => $p->string(),
    'data' => $p->assoc([
        'id' => $p->int(),
        'name' => $p->string(),
    ]),
    'meta' => $p->assoc([
        'page' => $p->int(),
        'total' => $p->int(),
    ]),
])->strict();
```

### Configuration Parsing

```php
$configSchema = $p->assoc([
    'database' => $p->assoc([
        'host' => $p->string()->default('localhost'),
        'port' => $p->int()->default(5432),
        'name' => $p->string(),
    ]),
    'cache' => $p->assoc([
        'driver' => $p->string(),
        'ttl' => $p->int()->positive(),
    ]),
]);

$config = $configSchema->parse($rawConfig);
echo $config['database']['host']; // 'localhost'
```

### Nested Associative Arrays

```php
$addressSchema = $p->assoc([
    'street' => $p->string(),
    'city' => $p->string(),
    'zipCode' => $p->string()->pattern('/^\d{5}$/'),
]);

$personSchema = $p->assoc([
    'name' => $p->string(),
    'address' => $addressSchema,
]);

$person = $personSchema->parse([
    'name' => 'John',
    'address' => [
        'street' => '123 Main St',
        'city' => 'Springfield',
        'zipCode' => '12345',
    ],
]);

echo $person['address']['city']; // 'Springfield'
```

### With Required and Nullable

```php
$schema = $p->assoc([
    'id' => $p->int(),
    'name' => $p->string(),
    'bio' => $p->string()->nullable(),      // Can be null
    'website' => $p->string()->url(),        // Validated if present
])->required(['id', 'name', 'bio']);         // website field is optional

// Valid inputs:
$schema->parse(['id' => 1, 'name' => 'John', 'bio' => null]);
$schema->parse(['id' => 1, 'name' => 'John', 'bio' => 'Hello', 'website' => 'https://example.com']);
```

### Array of Associative Arrays

```php
$usersSchema = $p->array(
    $p->assoc([
        'id' => $p->int()->positive(),
        'name' => $p->string()->minLength(1),
        'email' => $p->string()->email(),
    ])
);

$users = $usersSchema->parse([
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
]);

echo $users[0]['name']; // 'Alice'
```

### Post-Processing with Array Functions

Since the output is a native PHP array, you can use standard array functions:

```php
$schema = $p->assoc([
    'firstName' => $p->string(),
    'lastName' => $p->string(),
    'age' => $p->int(),
])->postParse(static function (array $data) {
    return [
        ...$data,
        'fullName' => $data['firstName'] . ' ' . $data['lastName'],
    ];
});

$result = $schema->parse(['firstName' => 'John', 'lastName' => 'Doe', 'age' => 30]);
// Returns: ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 30, 'fullName' => 'John Doe']
```

## Error Codes

| Code | Description |
|------|-------------|
| `assoc.type` | Value is not a valid input type |
| `assoc.unknownField` | Unknown field found in strict mode |
| `assoc.missingField` | Field listed in `required()` is missing |

Field-level errors include the field name in the error path (e.g., `name`, `address.city`).
