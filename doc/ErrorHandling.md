# Error Handling

chubbyphp-parsing provides a comprehensive error handling system with structured errors, path tracking, and multiple output formats.

## Core Concepts

### Error

A single validation error containing:
- `code` - Machine-readable error identifier (e.g., `string.minLength`)
- `template` - Human-readable message template with placeholders
- `variables` - Values to substitute into the template

```php
use Chubbyphp\Parsing\Error;

$error = new Error(
    code: 'string.minLength',
    template: 'Length should be at least {{min}}, {{given}} given',
    variables: ['min' => 5, 'given' => 2]
);

echo $error; // "Length should be at least 5, 2 given"
```

### Errors

A collection of errors with path tracking. Paths use dot notation to indicate nested locations (e.g., `user.address.city`).

### ErrorsException

A runtime exception that wraps an `Errors` collection. Thrown when `parse()` fails.

## Two Parsing Modes

### parse() - Throws on Failure

```php
use Chubbyphp\Parsing\ErrorsException;

try {
    $data = $schema->parse($input);
} catch (ErrorsException $e) {
    // Handle validation errors
    echo $e->getMessage();       // String representation
    echo $e->errors;             // Same as getMessage()
    var_dump($e->errors->jsonSerialize()); // Structured data
}
```

### safeParse() - Returns Result Object

```php
$result = $schema->safeParse($input);

if ($result->success) {
    $data = $result->data;
} else {
    $errors = $result->exception->errors;
    // Handle errors
}
```

## Error Output Formats

### String Format

Simple string representation with path prefix:

```php
echo $errors;
// Output:
// name: Length should be at least 3, 2 given
// email: Type should be "string", "int" given
// address.city: Length should be at least 1, 0 given
```

### JSON Format

Structured array suitable for serialization:

```php
$errors->jsonSerialize();
// Returns:
[
    [
        'path' => 'name',
        'error' => [
            'code' => 'string.minLength',
            'template' => 'Length should be at least {{min}}, {{given}} given',
            'variables' => ['min' => 3, 'given' => 2]
        ]
    ],
    [
        'path' => 'email',
        'error' => [
            'code' => 'string.type',
            'template' => 'Type should be "string", {{given}} given',
            'variables' => ['given' => 'int']
        ]
    ]
]
```

### API Problem Format (RFC 7807)

Format suitable for API error responses following the [RFC 7807](https://datatracker.ietf.org/doc/html/rfc7807) standard:

```php
$errors->toApiProblemInvalidParameters();
// Returns:
[
    [
        'name' => 'name',
        'reason' => 'Length should be at least 3, 2 given',
        'details' => [
            '_template' => 'Length should be at least {{min}}, {{given}} given',
            'min' => 3,
            'given' => 2
        ]
    ],
    [
        'name' => 'address[city]',  // Bracket notation for nested paths
        'reason' => 'Length should be at least 1, 0 given',
        'details' => [...]
    ]
]
```

### Tree Format

Hierarchical structure matching the input data shape:

```php
$errors->toTree();
// Returns:
[
    'name' => ['Length should be at least 3, 2 given'],
    'address' => [
        'city' => ['Length should be at least 1, 0 given'],
        'zipCode' => ['Value does not match pattern']
    ],
    'tags' => [
        '0' => ['Type should be "string", "int" given'],
        '2' => ['Length should be at least 1, 0 given']
    ]
]
```

## Error Handling Patterns

### Collecting All Errors

Parse operations collect all validation errors, not just the first one:

```php
$schema = $p->object([
    'name' => $p->string()->minLength(3),
    'email' => $p->string()->email(),
    'age' => $p->int()->positive(),
]);

try {
    $schema->parse([
        'name' => 'ab',           // Too short
        'email' => 'invalid',     // Not an email
        'age' => -5,              // Not positive
    ]);
} catch (ErrorsException $e) {
    // All three errors are captured
    foreach ($e->errors->jsonSerialize() as $error) {
        echo "{$error['path']}: {$error['error']['code']}\n";
    }
}
```

### Using catch() for Graceful Degradation

Handle errors inline and provide fallback values:

```php
$schema = $p->string()
    ->email()
    ->catch(static fn ($output, $exception) => 'invalid@fallback.com');

$schema->parse('not-an-email'); // Returns: 'invalid@fallback.com'
```

### Nested Error Paths

Errors in nested structures include the full path:

```php
$schema = $p->object([
    'user' => $p->object([
        'profile' => $p->object([
            'name' => $p->string()->minLength(1),
        ]),
    ]),
]);

// Error path: 'user.profile.name'
```

### Array Index Paths

Array item errors include the index:

```php
$schema = $p->array($p->string()->minLength(3));

// If items[2] fails: path is '2'
// Nested: 'users.2.name'
```

## API Response Example

Complete example of handling errors in an API:

```php
use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$requestSchema = $p->object([
    'name' => $p->string()->trim()->minLength(1)->maxLength(100),
    'email' => $p->string()->trim()->toLowerCase()->email(),
    'age' => $p->int()->minimum(0)->maximum(150),
])->strict();

function handleRequest(array $input): array
{
    global $requestSchema;

    try {
        $data = $requestSchema->parse($input);
        return [
            'status' => 'success',
            'data' => $data,
        ];
    } catch (ErrorsException $e) {
        return [
            'status' => 'error',
            'type' => 'https://example.com/validation-error',
            'title' => 'Validation Failed',
            'invalid-params' => $e->errors->toApiProblemInvalidParameters(),
        ];
    }
}
```

## Checking for Errors

```php
$errors = new Errors();
$errors->has(); // false

$errors->add(new Error('test.code', 'Test error', []));
$errors->has(); // true
```

## Error Codes Reference

Each schema type uses a consistent error code prefix:

| Schema | Prefix | Example Codes |
|--------|--------|---------------|
| string | `string.` | `string.type`, `string.minLength`, `string.email` |
| int | `int.` | `int.type`, `int.minimum`, `int.positive` |
| float | `float.` | `float.type`, `float.minimum`, `float.negative` |
| bool | `bool.` | `bool.type` |
| const | `const.` | `const.type` |
| array | `array.` | `array.type`, `array.minLength` |
| assoc | `assoc.` | `assoc.type`, `assoc.unknownField` |
| object | `object.` | `object.type`, `object.unknownField` |
| dateTime | `dateTime.` | `dateTime.type`, `dateTime.from` |
| tuple | `tuple.` | `tuple.type`, `tuple.length` |
| record | `record.` | `record.type` |
| union | `union.` | `union.type` |
| discriminatedUnion | `discriminatedUnion.` | `discriminatedUnion.type`, `discriminatedUnion.discriminator` |
| backedEnum | `backedEnum.` | `backedEnum.type` |
| lazy | (delegates to inner schema) | |
| respectValidation | `respectValidation.` | `respectValidation.assert` |
