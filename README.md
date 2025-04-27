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

 * php: ^8.2

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-parsing][1].

```sh
composer require chubbyphp/chubbyphp-parsing "^1.4"
```

## Usage

```php
use Chubbyphp\Parsing\Schema\SchemaInterface;

/** @var SchemaInterface $schema */
$schema = ...;

$schema->nullable();
$schema->preParse(static fn ($input) => $input);
$schema->postParse(static fn (string $output) => $output);
$schema->parse('test');
$schema->safeParse('test');
$schema->catch(static fn (string $output, ParserErrorException $e) => $output);
```

### array

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->array($p->int());

$data = $schema->parse([1, 2, 3, 4, 5]);

// validations
$schema->length(5);
$schema->minLength(5);
$schema->maxLength(5);
$schema->includes(5);

// transformations
$schema->filter(static fn (int $value) => 0 === $value % 2);
$schema->map(static fn (int $value) => $value * 2);
$schema->sort();
$schema->sort(static fn (int $a, int $b) => $b - $a);

// conversions
$schema->reduce(static fn (int $sum, int $current) => $sum + $current, 0);
```

### backedEnum

```php
use Chubbyphp\Parsing\Parser;

enum BackedSuit: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

$p = new Parser();

$schema = $p->backedEnum(BackedSuit::class);

$data = $schema->parse('D');

// validations

// transformations

// conversions
$schema->toInt();
$schema->toString();
```

### bool

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->bool();

$data = $schema->parse(true);

// validations

// transformations

// conversions
$schema->toInt();
$schema->toString();
```

### dateTime

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->dateTime();

$data = $schema->parse(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'));

// validations
$schema->from(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'));
$schema->to(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'));

// transformations

// conversions
$schema->toInt();
$schema->toString();
```

### discriminatedUnion

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->discriminatedUnion([
    $p->object(['_type' => $p->literal('email'), 'address' => $p->string()]),
    $p->object(['_type' => $p->literal('phone'), 'number' => $p->string()]),
]);

$data = $schema->parse(['_type' => 'phone', 'number' => '+41790000000']);
```

### float

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->float();

$data = $schema->parse(4.2);

// validations
$schema->gt(5.0);
$schema->gte(5.0);
$schema->lt(5.0);
$schema->lte(5.0);
$schema->positive();
$schema->nonNegative();
$schema->negative();
$schema->nonPositive();

// transformations

// conversions
$schema->toInt();
$schema->toString();
```

### int

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->int();

$data = $schema->parse(1337);

// validations
$schema->gt(5);
$schema->gte(5);
$schema->lt(5);
$schema->lte(5);
$schema->positive();
$schema->nonNegative();
$schema->negative();
$schema->nonPositive();

// transformations

// conversions
$schema->toDateTime();
$schema->toFloat();
$schema->toString();
```

### lazy

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->lazy(static function () use ($p, &$schema) {
    return $p->object([
        'name' => $p->string(),
        'child' => $schema,
    ])->nullable();
});

$data = $schema->parse([
    'name' => 'name1',
    'child' => [
        'name' => 'name2',
        'child' => null
    ],
]);
```

### literal

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->literal('email'); // supports string|float|int|bool

$data = $schema->parse('email');
```

### object

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->object(['name' => $p->string()]);

// create a new schema based on a existing once
$schema2 = $p->object([...$schema->getFieldToSchema(), 'value' => $p->string()]);

// stdClass object
$data = $schema->parse(['name' => 'example']);

// SampleClass object
$data = $schema->parse(['name' => 'example'], SampleNamespace\SampleClass::class);

// if the key 'name' does not exist on input, it won't exists on the output
$schema->optional(['name']);

// validations
$schema->strict();
$schema->strict(['_id']); // strip _id if given, but complain about any other additional field

// transformations

// conversions
```

### record

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->record($p->string());

$data = $schema->parse([
    'key1' => 'value1',
    'key2' => 'value2'
]);
```

### respectValidation

```sh
composer require respect/validation "^2.4"
```

```php
use Chubbyphp\Parsing\Parser;
use Respect\Validation\Validator as v;

$p = new Parser();

$schema = $p->respectValidation(v::numericVal()->positive()->between(1, 255));

$data = $schema->parse(5);
```

### string

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->string();

$data = $schema->parse('example');

// validations
$schema->length(5);
$schema->minLength(5);
$schema->maxLength(5);
$schema->includes('amp');
$schema->startsWith('exa');
$schema->endsWith('mpl');
$schema->match('/^[a-z]+$/i');
$schema->email();
$schema->ipV4();
$schema->ipV6();
$schema->url();
$schema->uuidV4();
$schema->uuidV5();

// transformations
$schema->trim();
$schema->trimStart();
$schema->trimEnd();
$schema->toLowerCase();
$schema->toUpperCase();

// conversions
$schema->toDateTime();
$schema->toFloat();
$schema->toInt();

// examples
$notBlankSchema = $schema->trim()->minSize(1);
```

### tuple

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->tuple([$p->float(), $p->float()]);

$data = $schema->parse([47.1, 8.2]);
```

### union

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->union([$p->string(), $p->int()]);

$data = $schema->parse('42');
```

## Copyright

2025 Dominik Zogg

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-parsing
