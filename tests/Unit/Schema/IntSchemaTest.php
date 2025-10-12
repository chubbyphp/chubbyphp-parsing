<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\IntSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\IntSchema
 *
 * @internal
 */
final class IntSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new IntSchema();

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(42));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (int $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (int $output, ErrorsException $e) => $output));
    }

    public function testParseSuccess(): void
    {
        $input = 1;

        $schema = new IntSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = 1;
        $input2 = 2;

        $schema = (new IntSchema())->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new IntSchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new IntSchema();

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.type',
                        'template' => 'Type should be "int", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = 1;

        $schema = (new IntSchema())->preParse(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = 1;

        $schema = (new IntSchema())->postParse(static fn (int $output) => (string) $output);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new IntSchema())
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'int.type',
                            'template' => 'Type should be "int", {{given}} given',
                            'variables' => [
                                'given' => 'NULL',
                            ],
                        ],
                    ],
                ], $errorsException->errors->jsonSerialize());

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = 1;

        $schema = new IntSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new IntSchema();

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'int.type',
                    'template' => 'Type should be "int", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
        ], $schema->safeParse(null)->exception->errors->jsonSerialize());
    }

    public function testParseWithValidGt(): void
    {
        $input = 5;
        $gt = 4;

        $schema = (new IntSchema())->gt($gt);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidGtEqual(): void
    {
        $input = 5;
        $gt = 5;

        $schema = (new IntSchema())->gt($gt);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.gt',
                        'template' => 'Value should be greater than {{gt}}, {{given}} given',
                        'variables' => [
                            'gt' => $gt,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithInvalidGtLesser(): void
    {
        $input = 4;
        $gt = 5;

        $schema = (new IntSchema())->gt($gt);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.gt',
                        'template' => 'Value should be greater than {{gt}}, {{given}} given',
                        'variables' => [
                            'gt' => $gt,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidGte(): void
    {
        $input = 5;
        $gte = 5;

        $schema = (new IntSchema())->gte($gte);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidGte(): void
    {
        $input = 4;
        $gte = 5;

        $schema = (new IntSchema())->gte($gte);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.gte',
                        'template' => 'Value should be greater than or equal {{gte}}, {{given}} given',
                        'variables' => [
                            'gte' => $gte,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidLt(): void
    {
        $input = 4;
        $lt = 5;

        $schema = (new IntSchema())->lt($lt);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidLtEqual(): void
    {
        $input = 5;
        $lt = 5;

        $schema = (new IntSchema())->lt($lt);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.lt',
                        'template' => 'Value should be lesser than {{lt}}, {{given}} given',
                        'variables' => [
                            'lt' => $lt,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithInvalidLtLesser(): void
    {
        $input = 5;
        $lt = 4;

        $schema = (new IntSchema())->lt($lt);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.lt',
                        'template' => 'Value should be lesser than {{lt}}, {{given}} given',
                        'variables' => [
                            'lt' => $lt,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidLte(): void
    {
        $input = 5;
        $lte = 5;

        $schema = (new IntSchema())->lte($lte);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidLte(): void
    {
        $input = 5;
        $lte = 4;

        $schema = (new IntSchema())->lte($lte);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.lte',
                        'template' => 'Value should be lesser than or equal {{lte}}, {{given}} given',
                        'variables' => [
                            'lte' => $lte,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidPositive(): void
    {
        $input = 1;

        $schema = (new IntSchema())->positive();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidPositive(): void
    {
        $input = 0;

        $schema = (new IntSchema())->positive();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.gt',
                        'template' => 'Value should be greater than {{gt}}, {{given}} given',
                        'variables' => [
                            'gt' => 0,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidNonNegative(): void
    {
        $input = 0;

        $schema = (new IntSchema())->nonNegative();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidNonNegative(): void
    {
        $input = -1;

        $schema = (new IntSchema())->nonNegative();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.gte',
                        'template' => 'Value should be greater than or equal {{gte}}, {{given}} given',
                        'variables' => [
                            'gte' => 0,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidNegative(): void
    {
        $input = -1;

        $schema = (new IntSchema())->negative();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidNegative(): void
    {
        $input = 0;

        $schema = (new IntSchema())->negative();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.lt',
                        'template' => 'Value should be lesser than {{lt}}, {{given}} given',
                        'variables' => [
                            'lt' => 0,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidNonPositive(): void
    {
        $input = 0;

        $schema = (new IntSchema())->nonPositive();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidNonPositive(): void
    {
        $input = 1;

        $schema = (new IntSchema())->nonPositive();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.lte',
                        'template' => 'Value should be lesser than or equal {{lte}}, {{given}} given',
                        'variables' => [
                            'lte' => 0,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidToDateTime(): void
    {
        $input = 1705742100;

        $schema = (new IntSchema())->toDateTime()->from(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'));

        self::assertEquals(new \DateTimeImmutable('@'.$input), $schema->parse($input));
    }

    public function testParseWithToDateTimeNullable(): void
    {
        $schema = (new IntSchema())->nullable()->toDateTime();

        self::assertNull($schema->parse(null));
    }

    public function testParseWithToFloat(): void
    {
        $input = 42;

        $schema = (new IntSchema())->toFloat()->gte(42.0);

        self::assertSame((float) $input, $schema->parse($input));
    }

    public function testParseWithToFloatNullable(): void
    {
        $schema = (new IntSchema())->nullable()->toFloat();

        self::assertNull($schema->parse(null));
    }

    public function testParseWithToString(): void
    {
        $input = 42;

        $schema = (new IntSchema())->toString()->length(2);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseWithToStringNullable(): void
    {
        $schema = (new IntSchema())->nullable()->toString();

        self::assertNull($schema->parse(null));
    }
}
