<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\AllOfSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AllOfSchema
 *
 * @internal
 */
final class AllOfSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new AllOfSchema([new StringSchema()]);

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
            new AllOfSchema([new StringSchema(), 'string']);

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

        $schema = new AllOfSchema([
            (new StringSchema())->minLength(2),
            (new StringSchema())->maxLength(4),
        ]);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessReturnsInputUnchanged(): void
    {
        $input = 1.0;

        $schema = new AllOfSchema([new IntSchema()]);

        // the int schema coerces 1.0 to 1, but allOf is pure validation
        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithEmptySchemas(): void
    {
        $input = 'test';

        $schema = new AllOfSchema([]);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithNull(): void
    {
        $schema = new AllOfSchema([]);

        self::assertNull($schema->parse(null));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = 'test';
        $input2 = 'other';

        $schema = (new AllOfSchema([new StringSchema()]))->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new AllOfSchema([new StringSchema()]))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailed(): void
    {
        $schema = new AllOfSchema([
            (new StringSchema())->minLength(2),
            (new StringSchema())->maxLength(4),
        ]);

        try {
            $schema->parse('too long');

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.maxLength',
                        'template' => 'Max length {{max}}, {{given}} given',
                        'variables' => [
                            'maxLength' => 4,
                            'given' => 8,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedCollectsAllErrors(): void
    {
        $schema = new AllOfSchema([
            (new StringSchema())->minLength(10),
            new IntSchema(),
        ]);

        try {
            $schema->parse('test');

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.minLength',
                        'template' => 'Min length {{min}}, {{given}} given',
                        'variables' => [
                            'minLength' => 10,
                            'given' => 4,
                        ],
                    ],
                ],
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.type',
                        'template' => 'Type should be "int", {{given}} given',
                        'variables' => [
                            'given' => 'string',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPostParse(): void
    {
        $schema = (new AllOfSchema([new StringSchema()]))
            ->postParse(static fn (string $output) => strtoupper($output))
        ;

        self::assertSame('TEST', $schema->parse('test'));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new AllOfSchema([new IntSchema()]))
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertSame('test', $input);

                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'int.type',
                            'template' => 'Type should be "int", {{given}} given',
                            'variables' => [
                                'given' => 'string',
                            ],
                        ],
                    ],
                ], $errorsException->errors->jsonSerialize());

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse('test'));
    }

    public function testSafeParseSuccess(): void
    {
        $input = 'test';

        $schema = new AllOfSchema([new StringSchema()]);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new AllOfSchema([new IntSchema()]);

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'int.type',
                    'template' => 'Type should be "int", {{given}} given',
                    'variables' => [
                        'given' => 'string',
                    ],
                ],
            ],
        ], $schema->safeParse('test')->exception->errors->jsonSerialize());
    }
}
