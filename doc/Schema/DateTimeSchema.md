# DateTimeSchema

The `DateTimeSchema` validates `DateTimeInterface` objects with date range constraints.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->dateTime();

$data = $schema->parse(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'));
// Returns: DateTimeImmutable instance
```

## Validations

### Date Range

```php
$minDate = new \DateTimeImmutable('2024-01-01T00:00:00+00:00');
$maxDate = new \DateTimeImmutable('2024-12-31T23:59:59+00:00');

$schema->from($minDate); // Must be on or after this date
$schema->to($maxDate);   // Must be on or before this date
```

## Conversions

```php
$schema->toInt();    // Convert to Unix timestamp
$schema->toString(); // Convert to ISO 8601 string
```

## Common Patterns

### Date Range Validation

```php
$startOf2024 = new \DateTimeImmutable('2024-01-01T00:00:00+00:00');
$endOf2024 = new \DateTimeImmutable('2024-12-31T23:59:59+00:00');

$dateIn2024Schema = $p->dateTime()
    ->from($startOf2024)
    ->to($endOf2024);
```

### Future Date Only

```php
$futureDateSchema = $p->dateTime()
    ->from(new \DateTimeImmutable());
```

### Past Date Only

```php
$pastDateSchema = $p->dateTime()
    ->to(new \DateTimeImmutable());
```

### Convert String to DateTime

Use `StringSchema` with `toDateTime()` for parsing date strings:

```php
$schema = $p->string()->toDateTime();

$date = $schema->parse('2024-01-20T09:15:00+00:00');
// Returns: DateTimeImmutable instance
```

### DateTime to Timestamp

```php
$timestampSchema = $p->dateTime()->toInt();

$timestamp = $timestampSchema->parse(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'));
// Returns: 1705744500
```

### DateTime to ISO String

```php
$isoSchema = $p->dateTime()->toString();

$iso = $isoSchema->parse(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'));
// Returns: '2024-01-20T09:15:00+00:00'
```

### Event Date Range

```php
$eventSchema = $p->object([
    'title' => $p->string()->minLength(1),
    'startDate' => $p->string()->toDateTime(),
    'endDate' => $p->string()->toDateTime(),
]);
```

## Error Codes

| Code | Description |
|------|-------------|
| `dateTime.type` | Value is not a DateTimeInterface |
| `dateTime.from` | Date is before the minimum allowed date |
| `dateTime.to` | Date is after the maximum allowed date |
