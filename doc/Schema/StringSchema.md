# StringSchema

The `StringSchema` validates and transforms string values. It's the most feature-rich primitive schema with extensive validation and transformation capabilities.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->string();

$data = $schema->parse('example'); // Returns: 'example'
```

## Validations

### Length Constraints

```php
$schema->length(5);      // Exact length of 5
$schema->minLength(3);   // Minimum 3 characters
$schema->maxLength(100); // Maximum 100 characters
```

### Content Checks

```php
$schema->contains('amp');    // Must contain 'amp'
$schema->startsWith('exa');  // Must start with 'exa'
$schema->endsWith('ple');    // Must end with 'ple'
$schema->pattern('/^[a-z]+$/i'); // Must match regex pattern
```

### Format Validations

```php
use Chubbyphp\Parsing\Enum\Uuid;

$schema->dateTime();      // Valid RFC 3339 date-time, example: '2024-01-20T09:15:00Z'
$schema->date();          // Valid RFC 3339 full-date, example: '2024-01-20'
$schema->time();          // Valid RFC 3339 full-time, example: '09:15:00Z'
$schema->duration();      // Valid ISO 8601 duration, example: 'P1Y2M3DT4H5M6S'
$schema->hostname();      // Valid hostname
$schema->idnHostname();   // Valid internationalized hostname, example: 'bücher.example'
$schema->email();         // Valid email address
$schema->idnEmail();      // Valid internationalized email address, example: 'john.doe@bücher.example'
$schema->ipV4();          // Valid IPv4 address
$schema->ipV6();          // Valid IPv6 address
$schema->mac();           // Valid mac address
$schema->uri();           // Valid URI
$schema->uuid();          // Valid UUID v4
$schema->uuid(Uuid::v5);  // Valid UUID v5
```

`idnEmail()` and `idnHostname()` require the [intl](https://www.php.net/manual/en/book.intl.php) extension.

## Transformations

Transformations modify the string value during parsing:

```php
$schema->trim();       // Remove whitespace from both ends
$schema->trimStart();  // Remove whitespace from start
$schema->trimEnd();    // Remove whitespace from end
$schema->toLowerCase(); // Convert to lowercase
$schema->toUpperCase(); // Convert to uppercase
```

## Conversions

Convert the string to another type:

```php
$schema->toDateTime(); // Convert ISO 8601 string to DateTimeImmutable
$schema->toFloat();    // Convert numeric string to float
$schema->toInt();      // Convert numeric string to integer
$schema->toBool();     // Convert boolean string to integer ('true', 'yes', 'on', '1' to true and 'false', 'no', 'off', '0' to false)
```

## Common Patterns

### Not Blank Validator

```php
$notBlankSchema = $p->string()->trim()->minLength(1);

$notBlankSchema->parse('  hello  '); // Returns: 'hello'
$notBlankSchema->parse('   ');       // Throws: minLength validation error
```

### Email with Normalization

```php
$emailSchema = $p->string()
    ->trim()
    ->toLowerCase()
    ->email();

$emailSchema->parse('  User@Example.COM  '); // Returns: 'user@example.com'
```

### Date String Parsing

```php
$dateSchema = $p->string()->toDateTime();

$date = $dateSchema->parse('2024-01-20T09:15:00+00:00');
// Returns: DateTimeImmutable instance
```

### Chained Validations

```php
$usernameSchema = $p->string()
    ->trim()
    ->toLowerCase()
    ->minLength(3)
    ->maxLength(20)
    ->pattern('/^[a-z0-9_]+$/');

$usernameSchema->parse('  John_Doe123  '); // Returns: 'john_doe123'
```

## Error Codes

| Code | Description |
|------|-------------|
| `string.type` | Value is not a string |
| `string.length` | String length doesn't match exact length |
| `string.minLength` | String is shorter than minimum |
| `string.maxLength` | String is longer than maximum |
| `string.contains` | String doesn't contain required substring |
| `string.startsWith` | String doesn't start with required prefix |
| `string.endsWith` | String doesn't end with required suffix |
| `string.dateTime` | Invalid date-time format |
| `string.date` | Invalid date format |
| `string.time` | Invalid time format |
| `string.duration` | Invalid duration format |
| `string.hostname` | Invalid hostname format |
| `string.idnHostname` | Invalid internationalized hostname format |
| `string.email` | Invalid email format |
| `string.idnEmail` | Invalid internationalized email format |
| `string.ipV4` | Invalid IPv4 format |
| `string.ipV6` | Invalid IPv6 format |
| `string.mac` | Invalid mac format |
| `string.pattern` | String doesn't match the pattern |
| `string.uri` | Invalid URI format |
| `string.uuidV4` | Invalid UUID v4 format |
| `string.uuidV5` | Invalid UUID v5 format |
| `string.bool` | Cannot convert string to bool (for `toBool()`) |
| `string.float` | Cannot convert string to float (for `toFloat()`) |
| `string.int` | Cannot convert string to int (for `toInt()`) |
| `string.datetime` | Cannot convert string to datetime (for `toDateTime()`) |
