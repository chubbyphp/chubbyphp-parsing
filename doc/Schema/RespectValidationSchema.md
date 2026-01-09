# RespectValidationSchema

The `RespectValidationSchema` integrates the [Respect/Validation](https://github.com/respect/validation) library, allowing you to use its extensive validation rules within the chubbyphp-parsing ecosystem.

## Installation

```sh
composer require respect/validation "^2.4.4"
```

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;
use Respect\Validation\Validator as v;

$p = new Parser();

$schema = $p->respectValidation(v::numericVal()->positive()->between(1, 255));

$data = $schema->parse(5);   // Returns: 5
$data = $schema->parse(0);   // Throws error
$data = $schema->parse(300); // Throws error
```

## Common Patterns

### Port Number

```php
$portSchema = $p->respectValidation(
    v::intVal()->positive()->between(1, 65535)
);

$portSchema->parse(8080); // Returns: 8080
```

### Credit Card

```php
$creditCardSchema = $p->respectValidation(v::creditCard());

$creditCardSchema->parse('4111111111111111'); // Valid Visa test number
```

### Phone Number

```php
$phoneSchema = $p->respectValidation(v::phone());

$phoneSchema->parse('+1 (555) 123-4567');
```

### Domain Name

```php
$domainSchema = $p->respectValidation(v::domain());

$domainSchema->parse('example.com');
```

### Country Code

```php
$countrySchema = $p->respectValidation(v::countryCode());

$countrySchema->parse('US');
$countrySchema->parse('DE');
```

### Currency Code

```php
$currencySchema = $p->respectValidation(v::currencyCode());

$currencySchema->parse('USD');
$currencySchema->parse('EUR');
```

### Complex Numeric Rules

```php
// Positive even number between 2 and 100
$evenSchema = $p->respectValidation(
    v::intVal()->positive()->even()->between(2, 100)
);

// Multiple of 5
$multipleOf5Schema = $p->respectValidation(
    v::intVal()->multiple(5)
);
```

### Date Validation

```php
$dateSchema = $p->respectValidation(
    v::date('Y-m-d')
);

$dateSchema->parse('2024-01-20');
```

### JSON String

```php
$jsonSchema = $p->respectValidation(v::json());

$jsonSchema->parse('{"key": "value"}');
```

### File Path

```php
$fileSchema = $p->respectValidation(v::file());

$fileSchema->parse('/etc/hosts');
```

### IBAN

```php
$ibanSchema = $p->respectValidation(v::iban());

$ibanSchema->parse('DE89370400440532013000');
```

### Slug

```php
$slugSchema = $p->respectValidation(v::slug());

$slugSchema->parse('my-blog-post-title');
```

## Combining with Object Schema

```php
$serverConfigSchema = $p->object([
    'host' => $p->respectValidation(v::domain()),
    'port' => $p->respectValidation(v::intVal()->between(1, 65535)),
    'ssl' => $p->bool(),
    'timeout' => $p->respectValidation(v::intVal()->positive()),
]);

$serverConfigSchema->parse([
    'host' => 'api.example.com',
    'port' => 443,
    'ssl' => true,
    'timeout' => 30,
]);
```

## Combining with Array Schema

```php
$emailListSchema = $p->array(
    $p->respectValidation(v::email())
);

$emailListSchema->parse([
    'user1@example.com',
    'user2@example.com',
]);
```

## Custom Error Messages

Respect/Validation error messages are passed through to the chubbyphp-parsing error system:

```php
try {
    $schema->parse('invalid');
} catch (ErrorsException $e) {
    // Error contains Respect/Validation message
    echo $e->errors;
}
```

## When to Use

Use `RespectValidationSchema` when:
- You need validation rules not covered by built-in schemas
- You're already familiar with Respect/Validation
- You need locale-specific validations (phone, country, etc.)
- You need complex composite validation rules

Use built-in schemas when:
- The validation is covered (string length, email, URL, etc.)
- You want consistent error codes
- You prefer minimal dependencies

## Available Respect/Validation Rules

Some commonly used rules:

| Category | Rules |
|----------|-------|
| **Type** | `intVal`, `floatVal`, `stringType`, `boolType`, `arrayType`, `nullType` |
| **String** | `alpha`, `alnum`, `digit`, `space`, `slug`, `json`, `base64` |
| **Numeric** | `positive`, `negative`, `even`, `odd`, `multiple`, `between` |
| **Date** | `date`, `dateTime`, `time`, `leapYear` |
| **Network** | `email`, `url`, `domain`, `ip`, `macAddress` |
| **Financial** | `creditCard`, `iban`, `bic`, `currencyCode` |
| **Geographic** | `countryCode`, `languageCode`, `postalCode`, `phone` |
| **File** | `file`, `directory`, `readable`, `writable`, `executable` |

See the [Respect/Validation documentation](https://respect-validation.readthedocs.io/) for the complete list.

## Error Codes

| Code | Description |
|------|-------------|
| `respectValidation.assert` | Validation failed (includes Respect/Validation message) |
