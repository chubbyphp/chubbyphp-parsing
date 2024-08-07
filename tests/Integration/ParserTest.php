<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Integration;

use Chubbyphp\Parsing\Parser;
use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\SchemaInterface;
use PHPUnit\Framework\TestCase;

enum BackedSuit: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

/**
 * @internal
 *
 * @coversNothing
 */
final class ParserTest extends TestCase
{
    public function testSuccess(): void
    {
        $schema = $this->getSchema();

        $ouputAsJson = <<<'EOD'
            {
                "array": [
                    "test1",
                    "test2"
                ],
                "backedEnum": "D",
                "bool": true,
                "dateTime": {
                    "date": "2024-01-20 09:15:00.000000",
                    "timezone_type": 1,
                    "timezone": "+00:00"
                },
                "discriminatedUnion": {
                    "literal": "type1",
                    "string": "test",
                    "default": "defaultOnClass"
                },
                "float": 1.5,
                "int": 5,
                "literal": 1337,
                "object": {
                    "string": "test"
                },
                "record": {
                    "key1": "value1",
                    "key2": "value2"
                },
                "string": "test",
                "tuple": [
                    41.5,
                    8.5
                ],
                "union": 42
            }
            EOD;

        self::assertEquals($ouputAsJson, json_encode($schema->parse([
            'array' => ['test1', 'test2'],
            'backedEnum' => 'D',
            'bool' => true,
            'dateTime' => new \DateTimeImmutable('2024-01-20T09:15:00+00:00'),
            'discriminatedUnion' => ['literal' => 'type1', 'string' => 'test'],
            'float' => 1.5,
            'int' => 5,
            'literal' => 1337,
            'object' => ['string' => 'test'],
            'record' => ['key1' => 'value1', 'key2' => 'value2'],
            'string' => 'test',
            'tuple' => [41.5, 8.5],
            'union' => 42,
        ]), JSON_PRETTY_PRINT));
    }

    public function testFailureWithEmptyArray(): void
    {
        $schema = $this->getSchema();

        try {
            $schema->parse([]);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                'array' => [
                    [
                        'code' => 'array.type',
                        'template' => 'Type should be "array", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'backedEnum' => [
                    [
                        'code' => 'backedEnum.type',
                        'template' => 'Type should be "int|string", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'bool' => [
                    [
                        'code' => 'bool.type',
                        'template' => 'Type should be "bool", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'dateTime' => [
                    [
                        'code' => 'datetime.type',
                        'template' => 'Type should be "\DateTimeInterface", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'discriminatedUnion' => [
                    [
                        'code' => 'discriminatedUnion.type',
                        'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'float' => [
                    [
                        'code' => 'float.type',
                        'template' => 'Type should be "float", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'int' => [
                    [
                        'code' => 'int.type',
                        'template' => 'Type should be "int", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'literal' => [
                    [
                        'code' => 'literal.type',
                        'template' => 'Type should be "bool|float|int|string", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'object' => [
                    [
                        'code' => 'object.type',
                        'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'record' => [
                    [
                        'code' => 'record.type',
                        'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'string' => [
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'tuple' => [
                    [
                        'code' => 'tuple.type',
                        'template' => 'Type should be "array", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                'union' => [
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                    [
                        'code' => 'int.type',
                        'template' => 'Type should be "int", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
            ], json_decode(json_encode($parserErrorException->getErrors()), true));
        }
    }

    protected function getSchema(): SchemaInterface
    {
        $discriminatedUnion = new class {
            public bool|float|int|string $literal;
            public string $string;
            public string $default = 'defaultOnClass';
        };

        $object = new class {
            public array $array;
            public BackedSuit $backedEnum;
            public bool $bool;
            public \DateTimeImmutable $dateTime;
            public object $discriminatedUnion;
            public float $float;
            public int $int;
            public bool|float|int|string $literal;
            public object $object;
            public array $record;
            public string $string;
            public array $tuple;
            public int|string $union;
        };

        $p = new Parser();

        return $p->object([
            'array' => $p->array($p->string()),
            'backedEnum' => $p->backedEnum(BackedSuit::class),
            'bool' => $p->bool(),
            'dateTime' => $p->dateTime(),
            'discriminatedUnion' => $p->discriminatedUnion([
                $p->object(['literal' => $p->literal('type1'), 'string' => $p->string()], $discriminatedUnion::class),
            ], 'literal'),
            'float' => $p->float(),
            'int' => $p->int(),
            'literal' => $p->literal(1337),
            'object' => $p->object(['string' => $p->string()]),
            'record' => $p->record($p->string()),
            'string' => $p->string(),
            'tuple' => $p->tuple([$p->float(), $p->float()]),
            'union' => $p->union([$p->string(), $p->int()]),
        ], $object::class);
    }
}
