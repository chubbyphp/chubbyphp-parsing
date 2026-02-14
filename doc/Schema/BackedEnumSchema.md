# BackedEnumSchema

The `BackedEnumSchema` validates PHP 8.1+ backed enum values. It accepts the backing value (string or int) and returns the corresponding enum case.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

enum Suit: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

$p = new Parser();

$schema = $p->backedEnum(Suit::class);

$data = $schema->parse('H'); // Returns: Suit::Hearts
$data = $schema->parse('D'); // Returns: Suit::Diamonds
```

## String-Backed Enums

```php
enum Status: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

$schema = $p->backedEnum(Status::class);

$schema->parse('approved'); // Returns: Status::Approved
$schema->parse('invalid');  // Throws error
```

## Integer-Backed Enums

```php
enum Priority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
    case Critical = 4;
}

$schema = $p->backedEnum(Priority::class);

$schema->parse(3);  // Returns: Priority::High
$schema->parse(99); // Throws error
```

## Conversions

### To String

For string-backed enums, convert the enum back to its string value:

```php
$schema = $p->backedEnum(Status::class)->toString();

$schema->parse('approved'); // Returns: 'approved' (string, not enum)
```

### To Int

For int-backed enums, convert the enum to its integer value:

```php
$schema = $p->backedEnum(Priority::class)->toInt();

$schema->parse(3); // Returns: 3 (int, not enum)
```

## Common Patterns

### HTTP Methods

```php
enum HttpMethod: string
{
    case Get = 'GET';
    case Post = 'POST';
    case Put = 'PUT';
    case Patch = 'PATCH';
    case Delete = 'DELETE';
}

$requestSchema = $p->object([
    'method' => $p->backedEnum(HttpMethod::class),
    'url' => $p->string()->url(),
    'body' => $p->string()->nullable(),
]);
```

### User Roles

```php
enum Role: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';
}

$userSchema = $p->object([
    'name' => $p->string(),
    'email' => $p->string()->email(),
    'role' => $p->backedEnum(Role::class),
]);
```

### Order Status

```php
enum OrderStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}

$orderSchema = $p->object([
    'id' => $p->string()->uuidV4(),
    'status' => $p->backedEnum(OrderStatus::class),
    'items' => $p->array($p->object([...])),
]);
```

### Log Levels

```php
enum LogLevel: int
{
    case Debug = 100;
    case Info = 200;
    case Warning = 300;
    case Error = 400;
    case Critical = 500;
}

$logSchema = $p->object([
    'level' => $p->backedEnum(LogLevel::class),
    'message' => $p->string(),
    'timestamp' => $p->string()->toDateTime(),
]);
```

### Card Game

```php
enum Suit: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

enum Rank: int
{
    case Two = 2;
    case Three = 3;
    // ...
    case Jack = 11;
    case Queen = 12;
    case King = 13;
    case Ace = 14;
}

$cardSchema = $p->object([
    'suit' => $p->backedEnum(Suit::class),
    'rank' => $p->backedEnum(Rank::class),
]);

$cardSchema->parse(['suit' => 'H', 'rank' => 14]);
// Returns object with Suit::Hearts and Rank::Ace
```

### With Nullable

```php
$schema = $p->backedEnum(Status::class)->nullable();

$schema->parse('approved'); // Returns: Status::Approved
$schema->parse(null);       // Returns: null
```

### Array of Enums

```php
$tagsSchema = $p->array($p->backedEnum(Tag::class));

$tagsSchema->parse(['featured', 'new', 'sale']);
// Returns: [Tag::Featured, Tag::New, Tag::Sale]
```

## BackedEnum vs Const Union

Use **BackedEnumSchema** when:
- You already have a PHP enum defined
- You want type safety with enum instances
- You need enum methods/functionality

Use **Const union** when:
- Values are ad-hoc or temporary
- You don't need enum type safety
- You're matching simple string/int values

```php
// BackedEnum: type-safe enum instances
enum Status: string { case Active = 'active'; case Inactive = 'inactive'; }
$schema = $p->backedEnum(Status::class);
// Returns Status enum instances

// Const union: simple string values
$schema = $p->union([$p->const('active'), $p->const('inactive')]);
// Returns strings
```

## Error Codes

| Code | Description |
|------|-------------|
| `backedEnum.type` | Value is not a string or int (expected backing type) |
| `backedEnum.value` | Value is not one of the valid enum case values |
