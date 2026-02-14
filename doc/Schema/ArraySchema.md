# ArraySchema

The `ArraySchema` validates arrays where all items conform to a single schema. It supports length validation, filtering, mapping, sorting, and reducing.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->array($p->int());

$data = $schema->parse([1, 2, 3, 4, 5]); // Returns: [1, 2, 3, 4, 5]
```

## Validations

### Length Constraints

```php
$schema->exactItems(5);  // Exact count of 5 items
$schema->minItems(1);    // At least 1 item
$schema->maxItems(10);   // At most 10 items
```

### Content Check

```php
$schema->contains(5); // Array must contain value 5
```

## Transformations

Transformations process the array after item validation:

### Filter

Remove items that don't match a predicate:

```php
$evenNumbersSchema = $p->array($p->int())
    ->filter(static fn (int $value) => 0 === $value % 2);

$evenNumbersSchema->parse([1, 2, 3, 4, 5]); // Returns: [2, 4]
```

### Map

Transform each item:

```php
$doubledSchema = $p->array($p->int())
    ->map(static fn (int $value) => $value * 2);

$doubledSchema->parse([1, 2, 3]); // Returns: [2, 4, 6]
```

### Sort

Sort items (ascending by default):

```php
// Ascending sort
$sortedSchema = $p->array($p->int())->sort();
$sortedSchema->parse([3, 1, 4, 1, 5]); // Returns: [1, 1, 3, 4, 5]

// Custom sort (descending)
$descendingSchema = $p->array($p->int())
    ->sort(static fn (int $a, int $b) => $b - $a);
$descendingSchema->parse([3, 1, 4]); // Returns: [4, 3, 1]
```

## Conversions

### Reduce

Convert the array to a single value:

```php
$sumSchema = $p->array($p->int())
    ->reduce(static fn (int $sum, int $current) => $sum + $current, 0);

$sumSchema->parse([1, 2, 3, 4, 5]); // Returns: 15
```

## Common Patterns

### Non-Empty Array

```php
$nonEmptySchema = $p->array($p->string())->minItems(1);
```

### Unique Tags with Limit

```php
$tagsSchema = $p->array($p->string()->trim()->minLength(1))
    ->maxItems(10)
    ->map(static fn (string $tag) => strtolower($tag));
```

### Array of Objects

```php
$usersSchema = $p->array(
    $p->object([
        'id' => $p->int()->positive(),
        'name' => $p->string()->minLength(1),
        'email' => $p->string()->email(),
    ])
);

$usersSchema->parse([
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
]);
```

### Processing Pipeline

```php
$processedSchema = $p->array($p->int())
    ->filter(static fn (int $v) => $v > 0)      // Keep positive
    ->map(static fn (int $v) => $v * 2)         // Double values
    ->sort()                                      // Sort ascending
    ->reduce(static fn (int $sum, int $v) => $sum + $v, 0); // Sum

$processedSchema->parse([-1, 3, 1, -2, 2]); // Returns: 12 (1+2+3 doubled = 2+4+6)
```

### Nested Arrays

```php
$matrixSchema = $p->array(
    $p->array($p->float())
);

$matrixSchema->parse([
    [1.0, 2.0, 3.0],
    [4.0, 5.0, 6.0],
]);
```

## Error Codes

| Code | Description |
|------|-------------|
| `array.type` | Value is not an array |
| `array.exactItems` | Array items count doesn't match exact count |
| `array.minItems` | Array has fewer items than minimum |
| `array.maxItems` | Array has more items than maximum |
| `array.contains` | Array doesn't contain required value |

Item-level errors will include the array index in the error path (e.g., `items.0`, `items.1`).
