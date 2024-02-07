<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\LiteralSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\LiteralSchema
 *
 * @internal
 */
final class LiteralSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new LiteralSchema('test');

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default('test'));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (string $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (string $output, ParserErrorException $e) => $output));
    }

    public function testParseSuccessWithBool(): void
    {
        $input = true;

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithInt(): void
    {
        $input = 1;

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithFloat(): void
    {
        $input = 1.5;

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithString(): void
    {
        $input = 'test';

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = 'test';

        $schema = (new LiteralSchema($input))->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new LiteralSchema('test'))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new LiteralSchema('test');

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'literal.type',
                    'template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithDifferentStringLiteralValue(): void
    {
        $schema = new LiteralSchema('test1');

        try {
            $schema->parse('test2');

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'literal.equals',
                    'template' => 'Input should be {{expected}}, {{given}} given',
                    'variables' => [
                        'expected' => 'test1',
                        'given' => 'test2',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithDifferentFloatLiteralValue(): void
    {
        $schema = new LiteralSchema(4.2);

        try {
            $schema->parse(4.1);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'literal.equals',
                    'template' => 'Input should be {{expected}}, {{given}} given',
                    'variables' => [
                        'expected' => 4.2,
                        'given' => 4.1,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithDifferentIntLiteralValue(): void
    {
        $schema = new LiteralSchema(1337);

        try {
            $schema->parse(1336);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'literal.equals',
                    'template' => 'Input should be {{expected}}, {{given}} given',
                    'variables' => [
                        'expected' => 1337,
                        'given' => 1336,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithDifferentBoolLiteralValue(): void
    {
        $schema = new LiteralSchema(true);

        try {
            $schema->parse(false);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'literal.equals',
                    'template' => 'Input should be {{expected}}, {{given}} given',
                    'variables' => [
                        'expected' => true,
                        'given' => false,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = 'test1';

        $schema = (new LiteralSchema($input))->preParse(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = 'test1';

        $schema = (new LiteralSchema($input))->postParse(static fn (string $output) => $output.'1');

        self::assertSame('test11', $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new LiteralSchema('test'))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'literal.type',
                        'template' => 'Type should be "bool|float|int|string", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ], $this->errorsToSimpleArray($parserErrorException->getErrors()));

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = 'test';

        $schema = new LiteralSchema($input);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new LiteralSchema('test');

        self::assertSame([
            [
                'code' => 'literal.type',
                'template' => 'Type should be "bool|float|int|string", {{given}} given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }
}
