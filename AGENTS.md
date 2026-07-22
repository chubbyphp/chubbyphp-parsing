# AGENTS.md

Guidance for AI agents working on this codebase.

## Design decisions

- `minProperties()` / `maxProperties()` exist only on `RecordSchema` and must not be
  added to `AssocSchema` / `ObjectSchema`: those schemas provide the set of properties
  themselves, and `required()` / `strict()` already determine which of them may or must
  be present. Property-count constraints only make sense on `record()`, where the keys
  are dynamic.
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
