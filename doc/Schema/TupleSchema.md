# TupleSchema

The `TupleSchema` validates fixed-length arrays where each position has a specific type. Unlike `ArraySchema` where all items share the same schema, tuples define a schema for each position.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->tuple([
    $p->float(),
    $p->float(),
]);

$data = $schema->parse([47.1, 8.2]); // Returns: [47.1, 8.2]
```

## How It Works

A tuple schema:
- Validates that the input has exactly the expected number of elements
- Validates each element against its corresponding schema by position
- Fails if there are missing or extra elements

## Common Patterns

### Coordinates (Latitude, Longitude)

```php
$coordinatesSchema = $p->tuple([
    $p->float()->minimum(-90)->maximum(90),   // Latitude
    $p->float()->minimum(-180)->maximum(180), // Longitude
]);

$coordinatesSchema->parse([47.3769, 8.5417]); // Zurich coordinates
```

### RGB Color

```php
$rgbSchema = $p->tuple([
    $p->int()->minimum(0)->maximum(255), // Red
    $p->int()->minimum(0)->maximum(255), // Green
    $p->int()->minimum(0)->maximum(255), // Blue
]);

$rgbSchema->parse([255, 128, 0]); // Orange color
```

### RGBA Color

```php
$rgbaSchema = $p->tuple([
    $p->int()->minimum(0)->maximum(255),   // Red
    $p->int()->minimum(0)->maximum(255),   // Green
    $p->int()->minimum(0)->maximum(255),   // Blue
    $p->float()->minimum(0)->maximum(1),   // Alpha
]);

$rgbaSchema->parse([255, 128, 0, 0.5]); // Semi-transparent orange
```

### Range (Min, Max)

```php
$rangeSchema = $p->tuple([
    $p->int(), // Min
    $p->int(), // Max
]);

$rangeSchema->parse([10, 100]);
```

### Mixed Types

```php
$recordSchema = $p->tuple([
    $p->string()->uuidV4(),  // ID
    $p->string(),            // Name
    $p->int()->nonNegative(), // Count
    $p->bool(),              // Active
]);

$recordSchema->parse([
    '550e8400-e29b-41d4-a716-446655440000',
    'Item Name',
    42,
    true,
]);
```

### 3D Point

```php
$point3dSchema = $p->tuple([
    $p->float(), // X
    $p->float(), // Y
    $p->float(), // Z
]);

$point3dSchema->parse([1.5, 2.5, 3.5]);
```

### Date Components

```php
$datePartsSchema = $p->tuple([
    $p->int()->minimum(1)->maximum(9999), // Year
    $p->int()->minimum(1)->maximum(12),   // Month
    $p->int()->minimum(1)->maximum(31),   // Day
]);

$datePartsSchema->parse([2024, 1, 20]);
```

## Tuple vs Array

Use **TupleSchema** when:
- You have a fixed number of elements
- Each position has a specific meaning and type
- Element order matters

Use **ArraySchema** when:
- You have a variable number of elements
- All elements share the same type
- You need length validations (min/max)

```php
// Tuple: exactly 2 floats representing coordinates
$coordinates = $p->tuple([$p->float(), $p->float()]);

// Array: variable number of strings (tags)
$tags = $p->array($p->string());
```

## Error Codes

| Code | Description |
|------|-------------|
| `tuple.type` | Value is not an array |
| `tuple.length` | Array length doesn't match expected tuple length |

Position-specific errors include the index in the error path (e.g., `0`, `1`).
