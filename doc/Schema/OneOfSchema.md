# OneOfSchema

The `OneOfSchema` validates a value against several schemas and requires **exactly one** to match: parsing fails when no schema matches, and also when two or more match. It covers the JSON Schema `oneOf` keyword.

## Basic Usage

```php
use Chubbyphp\Parsing\Parser;

$p = new Parser();

$schema = $p->oneOf([$p->string(), $p->int()]);

$data = $schema->parse('test'); // Returns: 'test' (only the string schema matches)
$data = $schema->parse(42);     // Returns: 42 (only the int schema matches)
$data = $schema->parse(1.5);    // Throws error (no schema matches, both errors reported)

$schema = $p->oneOf([$p->int(), $p->float()]);

$data = $schema->parse(1);      // Throws error (both schemas match)
```

## How It Works

1. **Every** schema is evaluated against the original input — unlike [UnionSchema](UnionSchema.md), which stops at the first success
2. Exactly one match: that schema's output is returned — exactly-one means the applied coercion is unambiguous, so unlike [AllOfSchema](AllOfSchema.md) the output is kept
3. No match: every schema's errors are reported, in schema order
4. Two or more matches: a single `oneOf.multipleMatch` error reports the matching schema indexes — the schemas *succeeded*, so there are no inner errors to report

## `oneOf` vs `union` (anyOf) vs `discriminatedUnion`

| Schema | Matches required | Evaluation |
|--------|------------------|------------|
| `union()` | at least one | stops at first success |
| `oneOf()` | exactly one | always evaluates all schemas |
| `discriminatedUnion()` | the one selected by the discriminator field | evaluates only the selected schema |

Prefer `discriminatedUnion()` when a discriminator field exists — it is faster and produces better errors. `oneOf()` is for genuinely overlapping schemas where exclusivity itself is the constraint, and for compiling JSON Schema documents that use `oneOf` without a discriminator.

## Edge Cases

- **Overlapping schemas are a modeling smell**: `oneOf([int(), float()])` fails for `1` because both match. If "either is fine" is meant, that is `union()`, not `oneOf()`.
- **A matched `nullable()` branch may output `null`**: `oneOf([$p->string()->nullable(), $p->int()])` parses `null` to `null` via the first schema — a `null` output is not a failed match.
- **Schemas that cannot fail**: a subschema with `catch()` or `default()` may always succeed and then causes `oneOf.multipleMatch` as soon as any other schema also matches.

## Error Codes

| Code | Description |
|------|-------------|
| `oneOf.multipleMatch` | Input matches more than one schema |

When no schema matches, the subschemas' own errors propagate unchanged.
