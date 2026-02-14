# DiscriminatedUnionSchema

The `DiscriminatedUnionSchema` validates tagged unions where a discriminator field determines which object schema to use. This is more efficient than `UnionSchema` for objects because it doesn't need to try each schema sequentially.

It supports both `ObjectSchema` and `AssocSchema` as union members.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

// Using ObjectSchema (returns stdClass)
$schema = $p->discriminatedUnion([
    $p->object(['_type' => $p->const('email'), 'address' => $p->string()->email()]),
    $p->object(['_type' => $p->const('phone'), 'number' => $p->string()]),
], '_type');

$email = $schema->parse(['_type' => 'email', 'address' => 'user@example.com']);
$phone = $schema->parse(['_type' => 'phone', 'number' => '+41790000000']);

// Using AssocSchema (returns array)
$schemaAssoc = $p->discriminatedUnion([
    $p->assoc(['_type' => $p->const('email'), 'address' => $p->string()->email()]),
    $p->assoc(['_type' => $p->const('phone'), 'number' => $p->string()]),
], '_type');

$emailArr = $schemaAssoc->parse(['_type' => 'email', 'address' => 'user@example.com']);
// Returns: ['_type' => 'email', 'address' => 'user@example.com']
```

## How It Works

1. The schema reads the discriminator field from the input
2. It finds the matching object schema based on the const value
3. The input is validated against that specific schema

This is O(1) lookup vs O(n) sequential trying in regular unions.

## Discriminator Field

The default discriminator field is `_type`, but you can customize it:

```php
// Using 'kind' as discriminator
$schema = $p->discriminatedUnion([
    $p->object(['kind' => $p->const('circle'), 'radius' => $p->float()]),
    $p->object(['kind' => $p->const('rectangle'), 'width' => $p->float(), 'height' => $p->float()]),
], 'kind');
```

## Common Patterns

### Contact Information

```php
$contactSchema = $p->discriminatedUnion([
    $p->object([
        '_type' => $p->const('email'),
        'address' => $p->string()->email(),
        'verified' => $p->bool(),
    ]),
    $p->object([
        '_type' => $p->const('phone'),
        'number' => $p->string()->pattern('/^\+\d{10,15}$/'),
        'country' => $p->string()->length(2),
    ]),
    $p->object([
        '_type' => $p->const('address'),
        'street' => $p->string(),
        'city' => $p->string(),
        'zipCode' => $p->string(),
    ]),
]);
```

### Shape Types (Geometry)

```php
$shapeSchema = $p->discriminatedUnion([
    $p->object([
        'type' => $p->const('circle'),
        'radius' => $p->float()->positive(),
    ]),
    $p->object([
        'type' => $p->const('rectangle'),
        'width' => $p->float()->positive(),
        'height' => $p->float()->positive(),
    ]),
    $p->object([
        'type' => $p->const('triangle'),
        'base' => $p->float()->positive(),
        'height' => $p->float()->positive(),
    ]),
], 'type');
```

### Event Types

```php
$eventSchema = $p->discriminatedUnion([
    $p->object([
        'event' => $p->const('user.created'),
        'userId' => $p->string()->uuidV4(),
        'email' => $p->string()->email(),
    ]),
    $p->object([
        'event' => $p->const('user.updated'),
        'userId' => $p->string()->uuidV4(),
        'changes' => $p->record($p->string()),
    ]),
    $p->object([
        'event' => $p->const('user.deleted'),
        'userId' => $p->string()->uuidV4(),
        'deletedAt' => $p->string()->toDateTime(),
    ]),
], 'event');
```

### Payment Methods

```php
$paymentSchema = $p->discriminatedUnion([
    $p->object([
        'method' => $p->const('credit_card'),
        'cardNumber' => $p->string()->pattern('/^\d{16}$/'),
        'expiry' => $p->string()->pattern('/^\d{2}\/\d{2}$/'),
        'cvv' => $p->string()->pattern('/^\d{3,4}$/'),
    ]),
    $p->object([
        'method' => $p->const('bank_transfer'),
        'iban' => $p->string(),
        'bic' => $p->string(),
    ]),
    $p->object([
        'method' => $p->const('paypal'),
        'email' => $p->string()->email(),
    ]),
], 'method');
```

### Parsing to Custom Classes

```php
class EmailContact { public string $address; }
class PhoneContact { public string $number; }

$schema = $p->discriminatedUnion([
    $p->object(['_type' => $p->const('email'), 'address' => $p->string()], EmailContact::class),
    $p->object(['_type' => $p->const('phone'), 'number' => $p->string()], PhoneContact::class),
]);

$contact = $schema->parse(['_type' => 'email', 'address' => 'test@example.com']);
// Returns: EmailContact instance
```

## Error Codes

| Code | Description |
|------|-------------|
| `discriminatedUnion.type` | Input is not a valid object type |
| `discriminatedUnion.discriminator` | Discriminator field value doesn't match any schema |

If the discriminator matches but the object fails validation, you'll get the specific field errors from the matched schema.
