# IfThenElseSchema

The `IfThenElseSchema` applies one of two schemas depending on a condition: when the `if` schema matches, the `then` schema is applied, otherwise the `else` schema. It covers the JSON Schema `if` / `then` / `else` keywords.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->if($p->string())
    ->then($p->string()->minLength(3))
    ->else($p->int()->minimum(0))
;

$data = $schema->parse('test'); // Returns: 'test' (string, validated by then)
$data = $schema->parse(42);     // Returns: 42 (not a string, validated by else)
$data = $schema->parse('ab');   // Throws error (string, but then fails)
$data = $schema->parse(-1);     // Throws error (not a string, and else fails)
```

## How It Works

1. The input is checked against the `if` schema — as a **pure condition**: its coercions and transformations never leak, the chosen branch receives the original input
2. If the `if` schema matches, the `then` schema is applied and its output returned, otherwise the `else` schema
3. An absent branch means no constraint — the input is returned unchanged (JSON Schema: an absent `then` / `else` acts as `true`)
4. When the applied branch fails, its errors propagate unchanged — `IfThenElseSchema` produces no errors of its own

## Common Patterns

### Conditional Object Shape

```php
$schema = $p->if($p->assoc(['country' => $p->const('CH')])->additionalProperties($p->any()))
    ->then($p->assoc(['country' => $p->string(), 'postalCode' => $p->string()->pattern('/^\d{4}$/')]))
    ->else($p->assoc(['country' => $p->string(), 'postalCode' => $p->string()]))
;
```

### Conditional Without Else

```php
// only constrains strings, everything else passes unchanged
$schema = $p->if($p->string())->then($p->string()->minLength(3));

$schema->parse('test'); // Returns: 'test'
$schema->parse(42);     // Returns: 42 (untouched)
```

An `else` schema works without a `then` schema just as well: `$p->if($ifSchema)->else($elseSchema)`.

## Edge Cases

- **`if` is condition-only**: `$p->if($p->string()->toUpperCase())->then($p->const('test'))` parses `'test'` successfully — the `then` branch receives the original `'test'`, not `'TEST'`.
- **`null` input**: `null` is evaluated against the `if` schema like any other value; with `nullable()`, `null` short-circuits before the condition is checked.
- **`if` schemas that cannot fail**: an `if` schema with `catch()` or `default()` may always succeed, which makes the `else` branch unreachable.
- **Error details**: only the applied branch's errors are reported; the `if` schema's outcome is a decision, not an error.

## Error Codes

`IfThenElseSchema` itself never produces an error — the applied branch schema's errors propagate unchanged.
