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
  intentionally out of scope even now that `allOf()` exists (a key matching multiple
  patterns must validate against all their schemas, but per-key pattern routing still
  has no first-class expression), and it can be composed from `record(union([...]))`
  plus `propertyNames()` or a `postParse()` closure instead.
- The JSON Schema `prefixItems` combined with `items` (tuple prefix plus typed remainder)
  is covered by `TupleSchema::rest()`: extra indices are validated against the rest schema
  and kept in the output instead of failing with `tuple.additionalIndex`. The related
  `unevaluatedItems` keyword needs nothing for the same reason as `unevaluatedProperties`
  (see below): without annotation-tracking composition it degenerates to the covered
  plain-tuple rejection / `rest()` cases.
- The JSON Schema `dependentRequired` / `dependentSchemas` keywords ("if key A is
  present, key B must be too / the object must match an extra schema") are intentionally
  not implemented as an API on `AssocSchema` / `ObjectSchema`: they compose with
  `allOf([if(...)->then(...), ...])` (one presence-conditional per key) or a
  `postParse()` closure that throws an `ErrorsException`, which mirrors how Zod handles
  them (`superRefine`, no native keyword) — too rare to earn a dedicated method. The
  related composition keywords need nothing either: `unevaluatedProperties` is only
  meaningful with annotation tracking across subschemas, which `allOf()` deliberately
  does not do (it is pure validation; shapes are merged in PHP via `getFieldToSchema()`
  before construction), so it degenerates to the covered `additionalProperties`;
  `if`/`then`/`else` conditional
  shapes are covered by `if()` (see below), with `discriminatedUnion()` / `union()` as
  the preferred encodings when a discriminator exists; and pure annotations
  (`title`, `description`, `examples`, `deprecated`, `readOnly`/`writeOnly`) have no
  validation semantics and are out of scope for a parsing library. With these, the JSON
  Schema object vocabulary is fully accounted for — a spec diff flagging any of them as
  "missing" is not a gap to fix.
- The JSON Schema `allOf` keyword is covered by `allOf($schemas)` (`AllOfSchema`):
  every schema validates the **original input** — like `not()` it is pure validation,
  the subschemas' coercions/transformations never leak and the input is returned
  unchanged (don't chain outputs: the spec requires each subschema to see the
  instance itself, and object schemas would drop each other's fields). Unlike
  `union()`, which stops at the first success, every failing subschema contributes
  its errors at unchanged instance paths, so all violations are reported at once. An
  empty schema list is vacuously valid (the spec's non-empty requirement is a
  document-level constraint for a compiler, not a parse-time concern). To validate
  and also populate, follow up with the populating schema in `postParse()`.
- The JSON Schema `oneOf` keyword is covered by `oneOf($schemas)` (`OneOfSchema`),
  while `union()` stays `anyOf` (first success wins) — don't add exactly-one semantics
  to `union()`. `oneOf()` always evaluates every schema against the original input:
  with exactly one match that schema's **output** is returned (unlike `allOf()`,
  exactly-one means the applied coercion is unambiguous, so keeping the output is
  safe); with no match every schema's errors are reported in schema order; with two
  or more matches the single `oneOf.multipleMatch` error reports the matching indexes
  and there are intentionally no inner errors (the schemas succeeded, same reasoning
  as `not.match`). `discriminatedUnion()` remains the preferred encoding when a
  discriminator exists (faster, better errors).
- The JSON Schema `if`/`then`/`else` keywords are covered by `if($ifSchema)`
  (`IfThenElseSchema`) with the immutable `then()` / `else()` withers — optional
  configuration uses withers here like `rest()` / `propertyNames()` do, the constructor
  takes only the required condition (a constructor-arguments variant was tried and
  reverted on purpose; the withers clone, so they are just as immutable). The `if`
  schema is evaluated as a pure condition via `safeParse` — its
  coercions/transformations never leak, the
  chosen branch receives the **original input** (don't "optimize" by reusing the `if`
  result). The applied branch schema's output is the result; an absent branch means no
  constraint and the input is returned unchanged (json schema: absent `then`/`else` acts
  as `true`). It produces no errors of its own — a failing branch propagates its errors
  unchanged, so the instance paths stay correct; don't prefix them with a
  `then`/`else` segment. For conditional shapes with a discriminator field,
  `discriminatedUnion()` remains the preferred encoding (better errors).
- The JSON Schema boolean schema `true` and the empty schema `{}` are covered by `any()`
  (`AnySchema`): it accepts every input — including `null`, without `nullable()` — and
  returns it unchanged; parsing itself can never fail and there are no error codes. It is
  the schema to pass where the spec allows `true` (`additionalProperties: true`,
  `items: true`, `rest()` on tuples), and the boolean schema `false` is `not(any())`.
  Don't add type restrictions, coercions, or a dedicated `NeverSchema`/`false()`.
- The JSON Schema `not` keyword is covered by `not($schema)` (`NotSchema`): parsing
  succeeds iff the wrapped schema fails, and returns the input **unchanged** — `not` is
  pure validation, so the wrapped schema's coercions/transformations must never leak
  into the output; don't "improve" it to return the wrapped schema's output. When it
  fails there are no inner errors to report (the wrapped schema succeeded), so the
  single `not.match` error is intentional, not a missing feature.
- The JSON Schema `multipleOf` keyword allows non-integer values on integers
  (`{"type": "integer", "multipleOf": 0.5}`): `IntSchema::multipleOf(int|float)` accepts
  both and always routes through the exact decimal math shared with
  `FloatSchema::multipleOf()` (`isMultipleOf()` / `toDigitsAndExponent()` on
  `AbstractNumericSchema`, the common base of `IntSchema` and `FloatSchema` — numeric
  helpers live there, not on `AbstractSchemaInnerParse`). There is deliberately no `%`
  fast path for int
  multiples and no `is_int` shortcut in `toDigitsAndExponent()` — `var_export()`
  prints ints exactly, so both would be behaviorally equivalent branches that only
  survive as mutants; don't add them back for "performance".
- The JSON Schema `pattern` keyword is covered by `pattern($pattern, PatternDialect::ecma262)`:
  the delimiter-less ECMA-262 pattern is translated in `StringSchema::ecma262ToPcre()` —
  unescaped `~` delimiters are escaped respecting backslash runs, `(*UTF)` applies
  code-point semantics while keeping `\d`/`\w`/`\s` ASCII like ECMA-262 (the `u` modifier
  would not: it also sets `PCRE2_UCP`, widening those classes), and `D` keeps `$` from
  matching before a trailing newline. Matching stays unanchored, as the spec requires.
  Errors report the pattern as given, not the translated PCRE — don't move this
  translation out into adapters, and don't "simplify" `(*UTF)` + `D` to `u`.
- The JSON Schema `format: "regex"` keyword is covered by `regex()`: ECMA-262 validity is
  approximated by a PCRE compile check of the same `ecma262ToPcre()` translation, matching
  common validator practice (ajv uses `new RegExp`, PHP validators use preg). A full
  ECMA-262 grammar parser is intentionally out of scope, so some PCRE-only constructs
  pass. Not to be confused with the deprecated `regexp($regexp)`, which matches the value
  *against* a given pattern instead of validating that the value *is* one.
