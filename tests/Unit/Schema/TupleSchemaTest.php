<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Parsing\Schema\TupleSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\TupleSchema
 *
 * @internal
 */
final class TupleSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new TupleSchema([new StringSchema(), new StringSchema()]);

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(['test']));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (array $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (array $output, ErrorsException $e) => $output));
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
        } catch (ErrorsException $errorsException) {
            self::assertSame(
                [
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'tuple.type',
                            'template' => 'Type should be "array", {{given}} given',
                            'variables' => [
                                'given' => 'NULL',
                            ],
                        ],
                    ],
                ],
                $errorsException->errors->jsonSerialize()
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
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '1',
                    'error' => [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", {{given}} given',
                        'variables' => [
                            'given' => 'integer',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedCauseMissingIndex(): void
    {
        $input = ['test'];

        $schema = new TupleSchema([new StringSchema(), new StringSchema(), new StringSchema()]);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '1',
                    'error' => [
                        'code' => 'tuple.missingIndex',
                        'template' => 'Missing input at index {{index}}',
                        'variables' => [
                            'index' => 1,
                        ],
                    ],
                ],
                [
                    'path' => '2',
                    'error' => [
                        'code' => 'tuple.missingIndex',
                        'template' => 'Missing input at index {{index}}',
                        'variables' => [
                            'index' => 2,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedCauseAdditionalIndex(): void
    {
        $input = ['test', 'test', 'test'];

        $schema = new TupleSchema([new StringSchema(), new StringSchema()]);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '2',
                    'error' => [
                        'code' => 'tuple.additionalIndex',
                        'template' => 'Additional input at index {{index}}',
                        'variables' => [
                            'index' => 2,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = ['test', 'test'];

        $schema = (new TupleSchema([new StringSchema(), new StringSchema()]))->preParse(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = ['test', 'test'];

        $schema = (new TupleSchema([new StringSchema(), new StringSchema()]))
            ->postParse(static fn (array $output) => array_merge($output, ['test2']))
        ;

        self::assertSame(array_merge($input, ['test2']), $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new TupleSchema([new StringSchema(), new StringSchema()]))
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertNull($input);

                self::assertSame(
                    [
                        [
                            'path' => '',
                            'error' => [
                                'code' => 'tuple.type',
                                'template' => 'Type should be "array", {{given}} given',
                                'variables' => [
                                    'given' => 'NULL',
                                ],
                            ],
                        ],
                    ],
                    $errorsException->errors->jsonSerialize()
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
                    'path' => '',
                    'error' => [
                        'code' => 'tuple.type',
                        'template' => 'Type should be "array", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
            ],
            $schema->safeParse(null)->exception->errors->jsonSerialize()
        );
    }
}
