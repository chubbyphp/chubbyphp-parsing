# RecordSchema

The `RecordSchema` validates key-value maps where all values conform to a single schema. The keys are strings and the values are validated against the provided schema.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->record($p->string());

$data = $schema->parse([
    'key1' => 'value1',
    'key2' => 'value2',
]);
// Returns: ['key1' => 'value1', 'key2' => 'value2']
```

## How It Works

A record schema:
- Accepts any string keys
- Validates all values against the same schema
- Returns an associative array

## Common Patterns

### String Dictionary

```php
$dictionarySchema = $p->record($p->string());

$dictionarySchema->parse([
    'greeting' => 'Hello',
    'farewell' => 'Goodbye',
]);
```

### Environment Variables

```php
$envSchema = $p->record($p->string());

$envSchema->parse([
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'DATABASE_URL' => 'mysql://localhost/db',
]);
```

### Configuration Map

```php
$configSchema = $p->record($p->union([
    $p->string(),
    $p->int(),
    $p->bool(),
]));

$configSchema->parse([
    'timeout' => 30,
    'debug' => true,
    'host' => 'localhost',
]);
```

### Sort Parameters

```php
$sortSchema = $p->record(
    $p->union([
        $p->literal('asc'),
        $p->literal('desc'),
    ])
);

$sortSchema->parse([
    'name' => 'asc',
    'createdAt' => 'desc',
]);
```

### Scores or Counts

```php
$scoresSchema = $p->record($p->int()->nonNegative());

$scoresSchema->parse([
    'player1' => 100,
    'player2' => 85,
    'player3' => 92,
]);
```

### Metadata

```php
$metadataSchema = $p->record($p->string());

$documentSchema = $p->object([
    'id' => $p->string()->uuidV4(),
    'content' => $p->string(),
    'metadata' => $metadataSchema,
]);

$documentSchema->parse([
    'id' => '550e8400-e29b-41d4-a716-446655440000',
    'content' => 'Document content here',
    'metadata' => [
        'author' => 'John Doe',
        'version' => '1.0',
        'tags' => 'draft,review',
    ],
]);
```

### Translations

```php
$translationsSchema = $p->record($p->string());

$translationsSchema->parse([
    'en' => 'Hello',
    'de' => 'Hallo',
    'fr' => 'Bonjour',
    'es' => 'Hola',
]);
```

### Nested Records

```php
$nestedSchema = $p->record(
    $p->record($p->string())
);

$nestedSchema->parse([
    'section1' => [
        'key1' => 'value1',
        'key2' => 'value2',
    ],
    'section2' => [
        'key3' => 'value3',
    ],
]);
```

## Record vs Object vs Assoc

Use **RecordSchema** when:
- Keys are dynamic/unknown at schema definition time
- All values have the same type
- You're working with dictionary-like structures

Use **ObjectSchema** when:
- Keys are known and fixed
- Different fields have different types
- You want field-specific validation
- You need an object (`stdClass` or custom class) as output

Use **AssocSchema** when:
- Keys are known and fixed
- Different fields have different types
- You want field-specific validation
- You need an associative array as output

```php
// Record: unknown keys, all string values
$translations = $p->record($p->string());

// Object: known keys with specific types, returns stdClass
$user = $p->object([
    'name' => $p->string(),
    'age' => $p->int(),
    'email' => $p->string()->email(),
]);

// Assoc: known keys with specific types, returns array
$userData = $p->assoc([
    'name' => $p->string(),
    'age' => $p->int(),
    'email' => $p->string()->email(),
]);
```

## Error Codes

| Code | Description |
|------|-------------|
| `record.type` | Value is not a valid record type |

Value-specific errors include the key in the error path (e.g., `key1`, `settings.debug`).
