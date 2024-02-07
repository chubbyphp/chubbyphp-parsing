<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Parsing\Schema\TupleSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\TupleSchema
 *
 * @internal
 */
final class TupleSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new TupleSchema([new StringSchema(), new StringSchema()]);

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->default(['test']));
        self::assertNotSame($schema, $schema->preMiddleware(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postMiddleware(static fn (array $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (array $output, ParserErrorException $e) => $output));
    }

    public function testConstructWithoutSchema(): void
    {
        try {
            new TupleSchema([new StringSchema(), '']);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 value of #1 ($schemas) must be of type Chubbyphp\Parsing\Schema\SchemaInterface, string given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testParseSuccess(): void
    {
        $input = ['test', 'test'];

        $schema = new TupleSchema([new StringSchema(), new StringSchema()]);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = ['test1', 'test1'];
        $input2 = ['test2', 'test2'];

        $schema = (new TupleSchema([new StringSchema(), new StringSchema()]))->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new TupleSchema([new StringSchema(), new StringSchema()]))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new TupleSchema([new StringSchema(), new StringSchema()]);

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(
                [
                    [
                        'code' => 'tuple.type',
                        'template' => 'Type should be "array", "{{given}}" given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                $this->errorsToSimpleArray($parserErrorException->getErrors())
            );
        }
    }

    public function testParseFailedWithoutStringInArray(): void
    {
        $input = ['test', 1];

        $schema = new TupleSchema([new StringSchema(), new StringSchema()]);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                1 => [
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", "{{given}}" given',
                        'variables' => [
                            'given' => 'integer',
                        ],
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedCauseMissingIndex(): void
    {
        $input = ['test'];

        $schema = new TupleSchema([new StringSchema(), new StringSchema(), new StringSchema()]);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                1 => [
                    [
                        'code' => 'tuple.missingIndex',
                        'template' => 'Missing input at index {{index}}',
                        'variables' => [
                            'index' => 1,
                        ],
                    ],
                ],
                2 => [
                    [
                        'code' => 'tuple.missingIndex',
                        'template' => 'Missing input at index {{index}}',
                        'variables' => [
                            'index' => 2,
                        ],
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedCauseAdditionalIndex(): void
    {
        $input = ['test', 'test', 'test'];

        $schema = new TupleSchema([new StringSchema(), new StringSchema()]);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                2 => [
                    [
                        'code' => 'tuple.additionalIndex',
                        'template' => 'Additional input at index {{index}}',
                        'variables' => [
                            'index' => 2,
                        ],
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithPreMiddleware(): void
    {
        $input = ['test', 'test'];

        $schema = (new TupleSchema([new StringSchema(), new StringSchema()]))->preMiddleware(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostMiddleware(): void
    {
        $input = ['test', 'test'];

        $schema = (new TupleSchema([new StringSchema(), new StringSchema()]))
            ->postMiddleware(static fn (array $output) => array_merge($output, ['test2']))
        ;

        self::assertSame(array_merge($input, ['test2']), $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new TupleSchema([new StringSchema(), new StringSchema()]))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);

                self::assertSame(
                    [
                        [
                            'code' => 'tuple.type',
                            'template' => 'Type should be "array", "{{given}}" given',
                            'variables' => [
                                'given' => 'NULL',
                            ],
                        ],
                    ],
                    $this->errorsToSimpleArray($parserErrorException->getErrors())
                );

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = ['test', 'test'];

        $schema = new TupleSchema([new StringSchema(), new StringSchema()]);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new TupleSchema([new StringSchema(), new StringSchema()]);

        self::assertSame(
            [
                [
                    'code' => 'tuple.type',
                    'template' => 'Type should be "array", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
            $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors())
        );
    }
}
