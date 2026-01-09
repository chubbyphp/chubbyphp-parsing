# BoolSchema

The `BoolSchema` validates boolean values.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->bool();

$data = $schema->parse(true);  // Returns: true
$data = $schema->parse(false); // Returns: false
```

## Conversions

```php
$schema->toInt();    // Convert to integer (true=1, false=0)
$schema->toFloat();  // Convert to float (true=1.0, false=0.0)
$schema->toString(); // Convert to string (true='1', false='')
```

## Common Patterns

### Boolean to Integer

```php
$schema = $p->bool()->toInt();

$schema->parse(true);  // Returns: 1
$schema->parse(false); // Returns: 0
```

### Feature Flags

```php
$configSchema = $p->object([
    'debug' => $p->bool(),
    'cache' => $p->bool(),
    'maintenance' => $p->bool(),
]);
```

### With Default Value

```php
$schema = $p->bool()->nullable()->default(false);

$schema->parse(true);  // Returns: true
$schema->parse(null);  // Returns: false
```

## Error Codes

| Code | Description |
|------|-------------|
| `bool.type` | Value is not a boolean |
