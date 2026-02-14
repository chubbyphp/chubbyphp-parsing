# FloatSchema

The `FloatSchema` validates floating-point values with numeric constraints.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->float();

$data = $schema->parse(4.2); // Returns: 4.2
```

## Validations

### Comparison Constraints

```php
$schema->minimum(5.0);  // Greater than or equal to 5.0
$schema->exclusiveMinimum(5.0);   // Greater than 5.0
$schema->exclusiveMaximum(10.0);  // Less than 10.0
$schema->maximum(10.0); // Less than or equal to 10.0
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
$schema->toInt();    // Convert to integer (truncates decimal)
$schema->toString(); // Convert to string
```

## Common Patterns

### Price Validation

```php
$priceSchema = $p->float()->nonNegative();

$priceSchema->parse(19.99); // Returns: 19.99
$priceSchema->parse(0.0);   // Returns: 0.0
$priceSchema->parse(-5.0);  // Throws: nonNegative validation error
```

### Percentage

```php
$percentageSchema = $p->float()->minimum(0.0)->maximum(100.0);

$percentageSchema->parse(75.5); // Returns: 75.5
$percentageSchema->parse(-1.0); // Throws: minimum validation error
$percentageSchema->parse(150.0); // Throws: maximum validation error
```

### Coordinates

```php
$latitudeSchema = $p->float()->minimum(-90.0)->maximum(90.0);
$longitudeSchema = $p->float()->minimum(-180.0)->maximum(180.0);

$coordinatesSchema = $p->object([
    'lat' => $latitudeSchema,
    'lng' => $longitudeSchema,
]);

$coordinatesSchema->parse(['lat' => 47.1, 'lng' => 8.2]);
```

## Error Codes

| Code | Description |
|------|-------------|
| `float.type` | Value is not a float |
| `float.minimum` | Value is not greater than or equal to threshold (used by `minimum` and `nonNegative()`) |
| `float.exclusiveMinimum` | Value is not greater than threshold (used by `exclusiveMinimum()` and `positive()`) |
| `float.exclusiveMaximum` | Value is not less than threshold (used by `exclusiveMaximum()` and `negative()`) |
| `float.maximum` | Value is not less than or equal to threshold (used by `maximum()` and `nonPositive()`) |
| `float.int` | Cannot convert float to int without precision loss (for `toInt()`) |
