# AGENTS.md

Guidance for AI agents working on this codebase.

## Design decisions

- `propertyNames()` / `minProperties()` / `maxProperties()` exist only on `RecordSchema`
  and must not be added to `AssocSchema` / `ObjectSchema`. The JSON Schema spec allows
  these keywords on any object, so a spec comparison will flag them as "missing" there —
  that is intentional, not a gap to fix: on a fixed shape the property count is already
  determined by the field definitions plus `required()` (min = required fields,
  max = defined fields), so a count constraint is redundant or contradictory, and
  `propertyNames` is pointless on keys that are literals in the schema itself (Zod draws
  the same line: `z.object()` has no such constraints either). The only inputs where they
  would carry meaning are "at least N of these optional fields" (better expressed as a
  union with different `required()` sets, or `postParse()`) and capping the total key
  count when `additionalProperties()` admits dynamic keys — if that concrete need ever
  materializes, revisit them together with `additionalProperties()`, not before.
- There is intentionally no dedicated null schema for the JSON Schema `type: "null"`:
  `const(null)` already validates exactly the value `null`, and `nullable()` covers
  optional-null on any schema. Don't propose adding a `NullSchema`.
- There is intentionally no first-class enum of arbitrary JSON values for the JSON
  Schema `enum` keyword: `union([const(...), const(...)])` is exactly how it is meant
  to be expressed (and `backedEnum()` covers PHP backed enums). Don't propose adding
  an `enum([...])` convenience.
- The JSON Schema `format: "uuid"` keyword is covered by `uuid(Uuid::any)`: it validates
  the plain RFC 9562 grammar (8-4-4-4-12 hex digits, case-insensitive) without version or
  variant constraints, so any version plus the nil and max UUIDs pass — matching common
  `format: "uuid"` validators. The versioned cases (`Uuid::v1`..`Uuid::v8`, default
  `Uuid::v4`) additionally check the version and variant fields; don't loosen them, and
  don't change the default.
- The JSON Schema `additionalProperties` keyword with a schema is covered by
  `AbstractObjectSchema::additionalProperties()` (shared state and parse loop live there,
  next to `strict()` / `required()`): fields without a field schema are validated against
  it and kept in the output. It is mutually exclusive with `strict()` — which covers
  `additionalProperties: false` — enforced in both directions with an
  `\InvalidArgumentException` at configuration time; the
  `assertAdditionalPropertiesSupport()` hook lets subclasses add guards (abstract on
  purpose: an empty default overridden by `ObjectSchema` would let a visibility mutant
  silently bypass the guards). On `ObjectSchema` the extra fields become input-driven
  dynamic properties, so the classname must accept them (`\stdClass` incl. subclasses,
  or `__set()`) and `construct: true` is rejected (an unknown named constructor argument
  would be fatal); both are enforced with an `\InvalidArgumentException` at
  `additionalProperties()` call time, not at parse time. `#[\AllowDynamicProperties]`
  classes are intentionally not accepted — the attribute is a deprecation suppressor,
  not an opt-in for input-controlled property names, and a colliding declared property
  (private or typed) would fatal outside the errors flow; such classes should compose
  via `assoc()->additionalProperties(...)->postParse(...)` instead. Don't re-add the
  attribute check.
- The JSON Schema `propertyNames` keyword is covered by `RecordSchema::propertyNames()`,
  which combined with a pattern also covers the common single-pattern `patternProperties`
  case. A multi-pattern `patternProperties` map (different value schemas per pattern) is
  intentionally out of scope: its spec semantics require `allOf` (a key matching multiple
  patterns must validate against all their schemas), and it can be composed from
  `record(union([...]))` plus `propertyNames()` or a `postParse()` closure instead.
- The JSON Schema `prefixItems` combined with `items` (tuple prefix plus typed remainder)
  is covered by `TupleSchema::rest()`: extra indices are validated against the rest schema
  and kept in the output instead of failing with `tuple.additionalIndex`. The related
  `unevaluatedItems` keyword needs nothing for the same reason as `unevaluatedProperties`
  (see below): without `allOf`/`$ref` composition it degenerates to the covered
  plain-tuple rejection / `rest()` cases.
- The JSON Schema `dependentRequired` / `dependentSchemas` keywords ("if key A is
  present, key B must be too / the object must match an extra schema") are intentionally
  not implemented as an API on `AssocSchema` / `ObjectSchema`: they compose cleanly with
  a `postParse()` closure that throws an `ErrorsException`, which mirrors how Zod handles
  them (`superRefine`, no native keyword) — too rare to earn a dedicated method. The
  related composition keywords need nothing either: `unevaluatedProperties` is only
  meaningful under `allOf`/`$ref` composition, which the library deliberately has no
  equivalent of (shapes are merged in PHP via `getFieldToSchema()` before construction),
  so it degenerates to the covered `additionalProperties`; `if`/`then`/`else` conditional
  shapes are expressed with `discriminatedUnion()` / `union()`; and pure annotations
  (`title`, `description`, `examples`, `deprecated`, `readOnly`/`writeOnly`) have no
  validation semantics and are out of scope for a parsing library. With these, the JSON
  Schema object vocabulary is fully accounted for — a spec diff flagging any of them as
  "missing" is not a gap to fix.
