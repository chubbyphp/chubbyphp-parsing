<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Integration;

use Chubbyphp\Parsing\Parser;
use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\SchemaInterface;
use PHPUnit\Framework\TestCase;

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
                "bool": true,
                "dateTime": {
                    "date": "2024-01-20 09:15:00.000000",
                    "timezone_type": 2,
                    "timezone": "Z"
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
                "string": "test",
                "union": 42
            }
            EOD;

        self::assertEquals($ouputAsJson, json_encode($schema->parse([
            'array' => ['test1', 'test2'],
            'bool' => true,
            'dateTime' => new \DateTimeImmutable('2024-01-20T09:15:00Z'),
            'discriminatedUnion' => ['literal' => 'type1', 'string' => 'test'],
            'float' => 1.5,
            'int' => 5,
            'literal' => 1337,
            'object' => ['string' => 'test'],
            'string' => 'test',
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
                    0 => 'Type should be "array" "NULL" given',
                ],
                'bool' => [
                    0 => 'Type should be "bool" "NULL" given',
                ],
                'dateTime' => [
                    0 => 'Type should be "DateTimeInterface" "NULL" given',
                ],
                'discriminatedUnion' => [
                    0 => 'Type should be "array" "NULL" given',
                ],
                'float' => [
                    0 => 'Type should be "float" "NULL" given',
                ],
                'int' => [
                    0 => 'Type should be "int" "NULL" given',
                ],
                'literal' => [
                    0 => 'Type should be "bool|float|int|string" "NULL" given',
                ],
                'object' => [
                    0 => 'Type should be "array" "NULL" given',
                ],
                'string' => [
                    0 => 'Type should be "string" "NULL" given',
                ],
                'union' => [
                    0 => 'Type should be "string" "NULL" given',
                    1 => 'Type should be "int" "NULL" given',
                ],
            ], $parserErrorException->getErrors());
        }
    }

    protected function getSchema(): SchemaInterface
    {
        $discriminatedUnion = new class() {
            public bool|float|int|string $literal;
            public string $string;
            public string $default = 'defaultOnClass';
        };

        $object = new class() {
            public array $array;
            public bool $bool;
            public \DateTimeImmutable $dateTime;
            public object $discriminatedUnion;
            public float $float;
            public int $int;
            public bool|float|int|string $literal;
            public object $object;
            public string $string;
            public int|string $union;
        };

        $p = new Parser();

        return $p->object([
            'array' => $p->array($p->string()),
            'bool' => $p->bool(),
            'dateTime' => $p->dateTime(),
            'discriminatedUnion' => $p->discriminatedUnion([
                $p->object(['literal' => $p->literal('type1'), 'string' => $p->string()], $discriminatedUnion::class),
            ], 'literal'),
            'float' => $p->float(),
            'int' => $p->int(),
            'literal' => $p->literal(1337),
            'object' => $p->object(['string' => $p->string()]),
            'string' => $p->string(),
            'union' => $p->union([$p->string(), $p->int()]),
        ], $object::class);
    }
}
