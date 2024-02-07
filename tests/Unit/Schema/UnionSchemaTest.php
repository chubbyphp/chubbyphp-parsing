<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Parsing\Schema\UnionSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\UnionSchema
 *
 * @internal
 */
final class UnionSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default('test'));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (int|string $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (int|string $output, ParserErrorException $e) => $output));
    }

    public function testConstructWithWrongArgument(): void
    {
        try {
            new UnionSchema([new StringSchema(), 'string', new IntSchema()]);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 value of #1 ($schemas) must be of type Chubbyphp\Parsing\Schema\SchemaInterface, string given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testParseSuccessWithString(): void
    {
        $input = 'test';

        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithInt(): void
    {
        $input = 1;

        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithStringAndDefault(): void
    {
        $input1 = 'test';
        $input2 = 5;

        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithIntAndDefault(): void
    {
        $input = 1;

        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
                [
                    'code' => 'int.type',
                    'template' => 'Type should be "int", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithStringAndparse(): void
    {
        $input = '1';

        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->postParse(static fn (string $output) => (int) $output);

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseSuccessWithIntAndparse(): void
    {
        $input = 1;

        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))->postParse(static fn (int $output) => (string) $output);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new UnionSchema([new StringSchema(), new IntSchema()]))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", "{{given}}" given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                    [
                        'code' => 'int.type',
                        'template' => 'Type should be "int", "{{given}}" given',
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

    public function testSafeParseSuccessWithString(): void
    {
        $input = 'test';

        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseSuccessWithInt(): void
    {
        $input = 1;

        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new UnionSchema([new StringSchema(), new IntSchema()]);

        self::assertSame([
            [
                'code' => 'string.type',
                'template' => 'Type should be "string", "{{given}}" given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
            [
                'code' => 'int.type',
                'template' => 'Type should be "int", "{{given}}" given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }
}
