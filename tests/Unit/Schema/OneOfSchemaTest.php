<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\FloatSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\OneOfSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\OneOfSchema
 *
 * @internal
 */
final class OneOfSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new OneOfSchema([new StringSchema(), new IntSchema()]);

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default('test'));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (mixed $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (mixed $output, ErrorsException $e) => $output));
    }

    public function testConstructWithWrongArgument(): void
    {
        try {
            new OneOfSchema([new StringSchema(), 'string']);

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
        $input = 'test';

        $schema = new OneOfSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessReturnsMatchedOutput(): void
    {
        $schema = new OneOfSchema([new IntSchema(), new StringSchema()]);

        // exactly one schema matches, so its coercion (1.0 to 1) applies
        self::assertSame(1, $schema->parse(1.0));
    }

    public function testParseSuccessWithNullMatchedOutput(): void
    {
        $schema = new OneOfSchema([(new StringSchema())->nullable(), new IntSchema()]);

        self::assertNull($schema->parse(null));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = 'test';
        $input2 = 'other';

        $schema = (new OneOfSchema([new StringSchema(), new IntSchema()]))->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new OneOfSchema([new StringSchema(), new IntSchema()]))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithoutMatch(): void
    {
        $schema = new OneOfSchema([new StringSchema(), new IntSchema()]);

        try {
            $schema->parse(1.5);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", {{given}} given',
                        'variables' => [
                            'given' => 'double',
                        ],
                    ],
                ],
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.type',
                        'template' => 'Type should be "int", {{given}} given',
                        'variables' => [
                            'given' => 'double',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithMultipleMatch(): void
    {
        $schema = new OneOfSchema([new IntSchema(), new FloatSchema(), new StringSchema()]);

        try {
            $schema->parse(1);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'oneOf.multipleMatch',
                        'template' => 'Input should match exactly one schema, schemas at indexes {{indexes}} matched, {{given}} given',
                        'variables' => [
                            'indexes' => [0, 1],
                            'given' => 1,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPostParse(): void
    {
        $schema = (new OneOfSchema([new StringSchema(), new IntSchema()]))
            ->postParse(static fn (string $output) => strtoupper($output))
        ;

        self::assertSame('TEST', $schema->parse('test'));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new OneOfSchema([new IntSchema(), new FloatSchema()]))
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertSame(1, $input);

                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'oneOf.multipleMatch',
                            'template' => 'Input should match exactly one schema, schemas at indexes {{indexes}} matched, {{given}} given',
                            'variables' => [
                                'indexes' => [0, 1],
                                'given' => 1,
                            ],
                        ],
                    ],
                ], $errorsException->errors->jsonSerialize());

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(1));
    }

    public function testSafeParseSuccess(): void
    {
        $input = 'test';

        $schema = new OneOfSchema([new StringSchema(), new IntSchema()]);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new OneOfSchema([new StringSchema()]);

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'integer',
                    ],
                ],
            ],
        ], $schema->safeParse(1)->exception->errors->jsonSerialize());
    }
}
