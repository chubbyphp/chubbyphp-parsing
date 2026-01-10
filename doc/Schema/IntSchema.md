# IntSchema

The `IntSchema` validates integer values with numeric constraints.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->int();

$data = $schema->parse(1337); // Returns: 1337
```

## Validations

### Comparison Constraints

```php
$schema->gt(5);   // Greater than 5
$schema->gte(5);  // Greater than or equal to 5
$schema->lt(10);  // Less than 10
$schema->lte(10); // Less than or equal to 10
```

### Sign Constraints

```php
$schema->positive();    // Must be > 0
$schema->nonNegative(); // Must be >= 0
$schema->negative();    // Must be < 0
$schema->nonPositive(); // Must be <= 0
```

## Conversions

```php
$schema->toDateTime(); // Convert Unix timestamp to DateTimeImmutable
$schema->toFloat();    // Convert to float
$schema->toString();   // Convert to string
```

## Common Patterns

### Positive Integer

```php
$positiveSchema = $p->int()->positive();

$positiveSchema->parse(42);  // Returns: 42
$positiveSchema->parse(0);   // Throws: positive validation error
$positiveSchema->parse(-1);  // Throws: positive validation error
```

### Range Validation

```php
$ageSchema = $p->int()->gte(0)->lte(150);

$ageSchema->parse(25);  // Returns: 25
$ageSchema->parse(-1);  // Throws: gte validation error
$ageSchema->parse(200); // Throws: lte validation error
```

### Pagination

```php
$offsetSchema = $p->int()->nonNegative();
$limitSchema = $p->int()->positive()->lte(100);

$paginationSchema = $p->object([
    'offset' => $offsetSchema,
    'limit' => $limitSchema,
]);
```

### Unix Timestamp to DateTime

```php
$timestampSchema = $p->int()->positive()->toDateTime();

$date = $timestampSchema->parse(1705744500);
// Returns: DateTimeImmutable instance for 2024-01-20T09:15:00+00:00
```

## Error Codes

| Code | Description |
|------|-------------|
| `int.type` | Value is not an integer |
| `int.gt` | Value is not greater than threshold |
| `int.gte` | Value is not greater than or equal to threshold |
| `int.lt` | Value is not less than threshold |
| `int.lte` | Value is not less than or equal to threshold |
| `int.positive` | Value is not positive |
| `int.nonNegative` | Value is negative |
| `int.negative` | Value is not negative |
| `int.nonPositive` | Value is positive |
