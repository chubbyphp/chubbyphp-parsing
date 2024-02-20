<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\Parser;
use Chubbyphp\Parsing\Schema\ArraySchema;
use Chubbyphp\Parsing\Schema\BackedEnumSchema;
use Chubbyphp\Parsing\Schema\BoolSchema;
use Chubbyphp\Parsing\Schema\DateTimeSchema;
use Chubbyphp\Parsing\Schema\DiscriminatedUnionSchema;
use Chubbyphp\Parsing\Schema\FloatSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\LazySchema;
use Chubbyphp\Parsing\Schema\LiteralSchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\RecordSchema;
use Chubbyphp\Parsing\Schema\RespectValidationSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Parsing\Schema\TupleSchema;
use Chubbyphp\Parsing\Schema\UnionSchema;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as v;

enum BackedSuit: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

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

    public function testBackedEnum(): void
    {
        $p = new Parser();

        $BoolSchema = $p->backedEnum(BackedSuit::class);

        self::assertInstanceOf(BackedEnumSchema::class, $BoolSchema);
    }

    public function testBool(): void
    {
        $p = new Parser();

        $BoolSchema = $p->bool();

        self::assertInstanceOf(BoolSchema::class, $BoolSchema);
    }

    public function testDateTime(): void
    {
        $p = new Parser();

        $dateTimeSchema = $p->dateTime();

        self::assertInstanceOf(DateTimeSchema::class, $dateTimeSchema);
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

    public function testFloat(): void
    {
        $p = new Parser();

        $floatSchema = $p->float();

        self::assertInstanceOf(FloatSchema::class, $floatSchema);
    }

    public function testInt(): void
    {
        $p = new Parser();

        $intSchema = $p->int();

        self::assertInstanceOf(IntSchema::class, $intSchema);
    }

    public function testLazy(): void
    {
        $p = new Parser();

        $lazySchema = $p->lazy(static function () use ($p, &$lazySchema) {
            return $p->object([
                'child' => $lazySchema,
            ])->nullable();
        });

        self::assertInstanceOf(LazySchema::class, $lazySchema);
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

    public function testRecord(): void
    {
        $p = new Parser();

        $recordSchema = $p->record($p->string());

        self::assertInstanceOf(RecordSchema::class, $recordSchema);
    }

    public function testTuple(): void
    {
        $p = new Parser();

        $tupleSchema = $p->tuple([$p->float(), $p->float()]);

        self::assertInstanceOf(TupleSchema::class, $tupleSchema);
    }

    public function testUnion(): void
    {
        $p = new Parser();

        $unionSchema = $p->union([$p->string(), $p->int()]);

        self::assertInstanceOf(UnionSchema::class, $unionSchema);
    }

    public function testRespectValidation(): void
    {
        $p = new Parser();

        $respectValidationSchema = $p->respectValidation(v::numericVal()->positive()->between(1, 255));

        self::assertInstanceOf(RespectValidationSchema::class, $respectValidationSchema);
    }
}
