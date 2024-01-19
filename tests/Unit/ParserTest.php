<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\Parser;
use Chubbyphp\Parsing\Schema\ArraySchema;
use Chubbyphp\Parsing\Schema\DiscriminatedUnionSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\LiteralSchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Parsing\Schema\UnionSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Parser
 *
 * @internal
 */
final class ParserTest extends TestCase
{
    public function testArray(): void
    {
        $p = new Parser();

        $arraySchema = $p->array($p->string());

        self::assertInstanceOf(ArraySchema::class, $arraySchema);
    }

    public function testDiscriminatedUnion(): void
    {
        $p = new Parser();

        $discriminatedUnionSchema = $p->discriminatedUnion([
            $p->object([
                '_type' => $p->literal('person'),
            ]),
        ], '_type');

        self::assertInstanceOf(DiscriminatedUnionSchema::class, $discriminatedUnionSchema);
    }

    public function testInt(): void
    {
        $p = new Parser();

        $intSchema = $p->int();

        self::assertInstanceOf(IntSchema::class, $intSchema);
    }

    public function testLiteral(): void
    {
        $p = new Parser();

        $literalSchema = $p->literal('person');

        self::assertInstanceOf(LiteralSchema::class, $literalSchema);
    }

    public function testObject(): void
    {
        $p = new Parser();

        $objectSchema = $p->object([
            'field' => $p->string(),
        ]);

        self::assertInstanceOf(ObjectSchema::class, $objectSchema);
    }

    public function testString(): void
    {
        $p = new Parser();

        $stringSchema = $p->string();

        self::assertInstanceOf(StringSchema::class, $stringSchema);
    }

    public function testUnion(): void
    {
        $p = new Parser();

        $unionSchema = $p->union([$p->string(), $p->int()]);

        self::assertInstanceOf(UnionSchema::class, $unionSchema);
    }
}
