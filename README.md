# chubbyphp-parsing

[![CI](https://github.com/chubbyphp/chubbyphp-parsing/workflows/CI/badge.svg?branch=master)](https://github.com/chubbyphp/chubbyphp-parsing/actions?query=workflow%3ACI)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-parsing/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-parsing?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchubbyphp%2Fchubbyphp-parsing%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/chubbyphp/chubbyphp-parsing/master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-parsing/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-parsing)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-parsing/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-parsing)
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

## Requirements

 * php: ^8.1

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-parsing][1].

```sh
composer require chubbyphp/chubbyphp-parsing "^1.0"
```

## Usage

### array

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->array($p->string());

$data = $schema->parse(['John Doe']);
```

### bool

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->bool();

$data = $schema->parse(true);
```

### dateTime

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->dateTime();

$data = $schema->parse(new \DateTimeImmutable('2024-01-20T09:15:00Z'));
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
```

### int

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->int();

$data = $schema->parse(1337);
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

// stdClass object
$data = $schema->parse(['name' => 'John Doe']);

// SampleClass object
$data = $schema->parse(['name' => 'John Doe'], SampleNamespace\SampleClass::class);
```

### string

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->string();

$data = $schema->parse('John Doe');
```

### union

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->union([
    $p->string()->transform(static fn (string $output) => (int) $output),
    $p->int(),
]);

$data = $schema->parse('42');
```

## Copyright

2024 Dominik Zogg

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-parsing
