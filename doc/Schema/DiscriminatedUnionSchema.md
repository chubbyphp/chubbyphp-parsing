# DiscriminatedUnionSchema

The `DiscriminatedUnionSchema` validates tagged unions where a discriminator field determines which object schema to use. This is more efficient than `UnionSchema` for objects because it doesn't need to try each schema sequentially.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->discriminatedUnion([
    $p->object(['_type' => $p->literal('email'), 'address' => $p->string()->email()]),
    $p->object(['_type' => $p->literal('phone'), 'number' => $p->string()]),
], '_type'); // '_type' is the default discriminator field

$email = $schema->parse(['_type' => 'email', 'address' => 'user@example.com']);
$phone = $schema->parse(['_type' => 'phone', 'number' => '+41790000000']);
```

## How It Works

1. The schema reads the discriminator field from the input
2. It finds the matching object schema based on the literal value
3. The input is validated against that specific schema

This is O(1) lookup vs O(n) sequential trying in regular unions.

## Discriminator Field

The default discriminator field is `_type`, but you can customize it:

```php
// Using 'kind' as discriminator
$schema = $p->discriminatedUnion([
    $p->object(['kind' => $p->literal('circle'), 'radius' => $p->float()]),
    $p->object(['kind' => $p->literal('rectangle'), 'width' => $p->float(), 'height' => $p->float()]),
], 'kind');
```

## Common Patterns

### Contact Information

```php
$contactSchema = $p->discriminatedUnion([
    $p->object([
        '_type' => $p->literal('email'),
        'address' => $p->string()->email(),
        'verified' => $p->bool(),
    ]),
    $p->object([
        '_type' => $p->literal('phone'),
        'number' => $p->string()->match('/^\+\d{10,15}$/'),
        'country' => $p->string()->length(2),
    ]),
    $p->object([
        '_type' => $p->literal('address'),
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
        'type' => $p->literal('circle'),
        'radius' => $p->float()->positive(),
    ]),
    $p->object([
        'type' => $p->literal('rectangle'),
        'width' => $p->float()->positive(),
        'height' => $p->float()->positive(),
    ]),
    $p->object([
        'type' => $p->literal('triangle'),
        'base' => $p->float()->positive(),
        'height' => $p->float()->positive(),
    ]),
], 'type');
```

### Event Types

```php
$eventSchema = $p->discriminatedUnion([
    $p->object([
        'event' => $p->literal('user.created'),
        'userId' => $p->string()->uuidV4(),
        'email' => $p->string()->email(),
    ]),
    $p->object([
        'event' => $p->literal('user.updated'),
        'userId' => $p->string()->uuidV4(),
        'changes' => $p->record($p->string()),
    ]),
    $p->object([
        'event' => $p->literal('user.deleted'),
        'userId' => $p->string()->uuidV4(),
        'deletedAt' => $p->string()->toDateTime(),
    ]),
], 'event');
```

### Payment Methods

```php
$paymentSchema = $p->discriminatedUnion([
    $p->object([
        'method' => $p->literal('credit_card'),
        'cardNumber' => $p->string()->match('/^\d{16}$/'),
        'expiry' => $p->string()->match('/^\d{2}\/\d{2}$/'),
        'cvv' => $p->string()->match('/^\d{3,4}$/'),
    ]),
    $p->object([
        'method' => $p->literal('bank_transfer'),
        'iban' => $p->string(),
        'bic' => $p->string(),
    ]),
    $p->object([
        'method' => $p->literal('paypal'),
        'email' => $p->string()->email(),
    ]),
], 'method');
```

### Parsing to Custom Classes

```php
class EmailContact { public string $address; }
class PhoneContact { public string $number; }

$schema = $p->discriminatedUnion([
    $p->object(['_type' => $p->literal('email'), 'address' => $p->string()], EmailContact::class),
    $p->object(['_type' => $p->literal('phone'), 'number' => $p->string()], PhoneContact::class),
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
