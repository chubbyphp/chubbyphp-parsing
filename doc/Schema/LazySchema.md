# LazySchema

The `LazySchema` enables recursive and self-referencing schema definitions. It defers schema resolution until parse time, allowing you to create schemas that reference themselves.

## Basic Usage

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
    'name' => 'root',
    'child' => [
        'name' => 'child1',
        'child' => [
            'name' => 'child2',
            'child' => null,
        ],
    ],
]);
```

## How It Works

1. The closure is stored but not immediately executed
2. When `parse()` is called, the closure is invoked to get the actual schema
3. The schema reference (`$schema`) is available inside the closure via PHP's `use` with reference (`&$schema`)
4. This allows self-referential definitions

## Common Patterns

### Tree Structure

```php
$treeSchema = $p->lazy(static function () use ($p, &$treeSchema) {
    return $p->object([
        'value' => $p->string(),
        'children' => $p->array($treeSchema),
    ]);
});

$treeSchema->parse([
    'value' => 'root',
    'children' => [
        [
            'value' => 'child1',
            'children' => [],
        ],
        [
            'value' => 'child2',
            'children' => [
                [
                    'value' => 'grandchild',
                    'children' => [],
                ],
            ],
        ],
    ],
]);
```

### Linked List

```php
$nodeSchema = $p->lazy(static function () use ($p, &$nodeSchema) {
    return $p->object([
        'value' => $p->int(),
        'next' => $nodeSchema->nullable(),
    ]);
});

$nodeSchema->parse([
    'value' => 1,
    'next' => [
        'value' => 2,
        'next' => [
            'value' => 3,
            'next' => null,
        ],
    ],
]);
```

### Comment Thread

```php
$commentSchema = $p->lazy(static function () use ($p, &$commentSchema) {
    return $p->object([
        'id' => $p->string()->uuidV4(),
        'author' => $p->string(),
        'text' => $p->string(),
        'createdAt' => $p->string()->toDateTime(),
        'replies' => $p->array($commentSchema),
    ]);
});

$commentSchema->parse([
    'id' => '550e8400-e29b-41d4-a716-446655440000',
    'author' => 'Alice',
    'text' => 'Great post!',
    'createdAt' => '2024-01-20T10:00:00Z',
    'replies' => [
        [
            'id' => '660e8400-e29b-41d4-a716-446655440001',
            'author' => 'Bob',
            'text' => 'Thanks!',
            'createdAt' => '2024-01-20T11:00:00Z',
            'replies' => [],
        ],
    ],
]);
```

### Filesystem Structure

```php
$fileSchema = $p->object([
    'type' => $p->const('file'),
    'name' => $p->string(),
    'size' => $p->int()->nonNegative(),
]);

$folderSchema = $p->lazy(static function () use ($p, &$folderSchema, $fileSchema) {
    return $p->object([
        'type' => $p->const('folder'),
        'name' => $p->string(),
        'children' => $p->array(
            $p->union([$fileSchema, $folderSchema])
        ),
    ]);
});

$rootSchema = $p->union([$fileSchema, $folderSchema]);

$rootSchema->parse([
    'type' => 'folder',
    'name' => 'root',
    'children' => [
        ['type' => 'file', 'name' => 'readme.txt', 'size' => 1024],
        [
            'type' => 'folder',
            'name' => 'src',
            'children' => [
                ['type' => 'file', 'name' => 'index.php', 'size' => 2048],
            ],
        ],
    ],
]);
```

### Organization Hierarchy

```php
$employeeSchema = $p->lazy(static function () use ($p, &$employeeSchema) {
    return $p->object([
        'name' => $p->string(),
        'title' => $p->string(),
        'directReports' => $p->array($employeeSchema),
    ]);
});

$employeeSchema->parse([
    'name' => 'CEO',
    'title' => 'Chief Executive Officer',
    'directReports' => [
        [
            'name' => 'CTO',
            'title' => 'Chief Technology Officer',
            'directReports' => [
                [
                    'name' => 'Developer',
                    'title' => 'Software Engineer',
                    'directReports' => [],
                ],
            ],
        ],
    ],
]);
```

### JSON Schema-like Structure

```php
$jsonSchemaType = $p->lazy(static function () use ($p, &$jsonSchemaType) {
    return $p->discriminatedUnion([
        $p->object([
            'type' => $p->const('string'),
            'minLength' => $p->int()->nonNegative(),
        ])->optional(['minLength']),
        $p->object([
            'type' => $p->const('number'),
            'minimum' => $p->float(),
        ])->optional(['minimum']),
        $p->object([
            'type' => $p->const('object'),
            'properties' => $p->record($jsonSchemaType),
        ]),
        $p->object([
            'type' => $p->const('array'),
            'items' => $jsonSchemaType,
        ]),
    ], 'type');
});
```

## Important Notes

### Reference Syntax

Always use `&$schema` (reference) in the `use` clause:

```php
// Correct: use reference
$schema = $p->lazy(static function () use ($p, &$schema) {
    return $p->object(['child' => $schema])->nullable();
});

// Wrong: without reference, $schema would be null inside the closure
$schema = $p->lazy(static function () use ($p, $schema) {
    return $p->object(['child' => $schema])->nullable(); // $schema is null!
});
```

### Nullable for Termination

Recursive structures typically need a termination condition. Use `nullable()` to allow `null` values that end the recursion:

```php
$schema = $p->lazy(static function () use ($p, &$schema) {
    return $p->object([
        'value' => $p->string(),
        'child' => $schema,  // Without nullable, recursion never ends!
    ])->nullable();          // Allow null to terminate
});
```

Or use an array that can be empty:

```php
$schema = $p->lazy(static function () use ($p, &$schema) {
    return $p->object([
        'value' => $p->string(),
        'children' => $p->array($schema), // Empty array terminates
    ]);
});
```
