# chubbyphp-parsing

[![CI](https://github.com/chubbyphp/chubbyphp-parsing/actions/workflows/ci.yml/badge.svg)](https://github.com/chubbyphp/chubbyphp-parsing/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-parsing/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-parsing?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchubbyphp%2Fchubbyphp-parsing%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/chubbyphp/chubbyphp-parsing/master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-parsing/v)](https://packagist.org/packages/chubbyphp/chubbyphp-parsing)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-parsing/downloads)](https://packagist.org/packages/chubbyphp/chubbyphp-parsing)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-parsing/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-parsing)

[![bugs](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=bugs)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![code_smells](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=code_smells)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![coverage](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=coverage)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![duplicated_lines_density](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=duplicated_lines_density)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![ncloc](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=ncloc)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![sqale_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![alert_status](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=alert_status)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![reliability_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![security_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=security_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![sqale_index](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)
[![vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-parsing&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-parsing)


## Description

Allows parsing data of various structures, meaning the population and validation of data into a defined structure. For example, converting an API request into a Data Transfer Object (DTO).

Heavily inspired by the well-known TypeScript library [zod](https://github.com/colinhacks/zod).

## Requirements

 * php: ^8.3

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-parsing][1].

```sh
composer require chubbyphp/chubbyphp-parsing "^2.2"
```

## Quick Start

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

// Define a schema
$userSchema = $p->object([
    'name' => $p->string()->minLength(1)->maxLength(100),
    'email' => $p->string()->email(),
    'age' => $p->int()->gte(0)->lte(150),
]);

// Parse and validate data
$user = $userSchema->parse([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30,
]);
```

## Schema Types

### Primitives

| Schema | Description | Documentation |
|--------|-------------|---------------|
| `string()` | String validation with length, pattern, format checks | [StringSchema](doc/Schema/StringSchema.md) |
| `int()` | Integer validation with numeric constraints | [IntSchema](doc/Schema/IntSchema.md) |
| `float()` | Float validation with numeric constraints | [FloatSchema](doc/Schema/FloatSchema.md) |
| `bool()` | Boolean validation | [BoolSchema](doc/Schema/BoolSchema.md) |
| `dateTime()` | DateTime validation with range constraints | [DateTimeSchema](doc/Schema/DateTimeSchema.md) |

### Complex Types

| Schema | Description | Documentation |
|--------|-------------|---------------|
| `array()` | Arrays with item validation | [ArraySchema](doc/Schema/ArraySchema.md) |
| `assoc()` | Associative arrays with field schemas | [AssocSchema](doc/Schema/AssocSchema.md) |
| `object()` | Objects/DTOs with field schemas | [ObjectSchema](doc/Schema/ObjectSchema.md) |
| `tuple()` | Fixed-length arrays with positional types | [TupleSchema](doc/Schema/TupleSchema.md) |
| `record()` | Key-value maps with uniform value types | [RecordSchema](doc/Schema/RecordSchema.md) |

### Union Types

| Schema | Description | Documentation |
|--------|-------------|---------------|
| `union()` | Value matches one of several schemas | [UnionSchema](doc/Schema/UnionSchema.md) |
| `discriminatedUnion()` | Tagged unions with a discriminator field | [DiscriminatedUnionSchema](doc/Schema/DiscriminatedUnionSchema.md) |

### Special Types

| Schema | Description | Documentation |
|--------|-------------|---------------|
| `literal()` | Exact value matching | [LiteralSchema](doc/Schema/LiteralSchema.md) |
| `backedEnum()` | PHP BackedEnum validation | [BackedEnumSchema](doc/Schema/BackedEnumSchema.md) |
| `lazy()` | Recursive/self-referencing schemas | [LazySchema](doc/Schema/LazySchema.md) |
| `respectValidation()` | Integration with Respect/Validation | [RespectValidationSchema](doc/Schema/RespectValidationSchema.md) |

## Common Schema Methods

All schemas support these methods:

```php
$schema->nullable();       // Allow null values
$schema->default($value);  // Provide default when input is null
$schema->preParse($fn);    // Transform input before parsing
$schema->postParse($fn);   // Transform output after parsing
$schema->catch($fn);       // Handle errors and provide fallback
$schema->parse($input);    // Parse and throw on error
$schema->safeParse($input); // Parse and return Result object
```

## Error Handling

```php
use Chubbyphp\Parsing\ErrorsException;

try {
    $schema->parse($input);
} catch (ErrorsException $e) {
    $e->errors->jsonSerialize();                   // JSON structure
    $e->errors->toApiProblemInvalidParameters();   // RFC 7807 format
    $e->errors->toTree();                          // Hierarchical structure
}
```

See [Error Handling](doc/ErrorHandling.md) for detailed documentation.

## Documentation

- **Schema Types**: [doc/Schema/](doc/Schema/)
- **Error Handling**: [doc/ErrorHandling.md](doc/ErrorHandling.md)
- **Migration**: [1.x to 2.x](doc/Migration/1.x-2.x.md)

## Copyright

2026 Dominik Zogg

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-parsing
