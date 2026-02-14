<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\FloatSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\FloatSchema
 *
 * @internal
 */
final class FloatSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new FloatSchema();

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(4.2));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (float $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (float $output, ErrorsException $e) => $output));
    }

    public function testParseSuccess(): void
    {
        $input = 1.5;

        $schema = new FloatSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = 1.5;
        $input2 = 2.5;

        $schema = (new FloatSchema())->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new FloatSchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new FloatSchema();

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.type',
                        'template' => 'Type should be "float", {{given}} given',
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
        $input = 1.5;

        $schema = (new FloatSchema())->preParse(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = 1.5;

        $schema = (new FloatSchema())->postParse(static fn (float $output) => (string) $output);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new FloatSchema())

            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'float.type',
                            'template' => 'Type should be "float", {{given}} given',
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
        $input = 1.5;

        $schema = new FloatSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new FloatSchema();

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'float.type',
                    'template' => 'Type should be "float", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
        ], $schema->safeParse(null)->exception->errors->jsonSerialize());
    }

    public function testParseWithValidMinimum(): void
    {
        $input = 4.1;
        $minimum = 4.1;

        $schema = (new FloatSchema())->minimum($minimum);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMinimum(): void
    {
        $input = 4.1;
        $minimum = 4.2;

        $schema = (new FloatSchema())->minimum($minimum);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.minimum',
                        'template' => 'Value should be minimum {{minimum}} {{exclusiveMinimum}}, {{given}} given',
                        'variables' => [
                            'minimum' => $minimum,
                            'exclusiveMinimum' => false,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidExclusiveMinimum(): void
    {
        $input = 4.2;
        $minimum = 4.1;

        $schema = (new FloatSchema())->minimum($minimum, true);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidExclusiveMinimumEqual(): void
    {
        $input = 4.1;
        $minimum = 4.1;

        $schema = (new FloatSchema())->minimum($minimum, true);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame(
                [
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'float.minimum',
                            'template' => 'Value should be minimum {{minimum}} {{exclusiveMinimum}}, {{given}} given',
                            'variables' => [
                                'minimum' => $minimum,
                                'exclusiveMinimum' => true,
                                'given' => $input,
                            ],
                        ],
                    ],
                ],
                $errorsException->errors->jsonSerialize()
            );
        }
    }

    public function testParseWithInvalidExclusiveMinimumLesser(): void
    {
        $input = 4.1;
        $minimum = 4.2;

        $schema = (new FloatSchema())->minimum($minimum, true);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame(
                [
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'float.minimum',
                            'template' => 'Value should be minimum {{minimum}} {{exclusiveMinimum}}, {{given}} given',
                            'variables' => [
                                'minimum' => $minimum,
                                'exclusiveMinimum' => true,
                                'given' => $input,
                            ],
                        ],
                    ],
                ],
                $errorsException->errors->jsonSerialize()
            );
        }
    }

    public function testParseWithValidExclusiveMaximum(): void
    {
        $input = 4.1;
        $maximum = 4.2;

        $schema = (new FloatSchema())->maximum($maximum, true);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidExclusiveMaximumEqual(): void
    {
        $input = 4.1;
        $maximum = 4.1;

        $schema = (new FloatSchema())->maximum($maximum, true);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.maximum',
                        'template' => 'Value should be maximum {{maximum}} {{exclusiveMaximum}}, {{given}} given',
                        'variables' => [
                            'maximum' => $maximum,
                            'exclusiveMaximum' => true,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithInvalidExclusiveMaximumLesser(): void
    {
        $input = 4.2;
        $maximum = 4.1;

        $schema = (new FloatSchema())->maximum($maximum, true);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.maximum',
                        'template' => 'Value should be maximum {{maximum}} {{exclusiveMaximum}}, {{given}} given',
                        'variables' => [
                            'maximum' => $maximum,
                            'exclusiveMaximum' => true,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidMaximum(): void
    {
        $input = 4.1;
        $maximum = 4.1;

        $schema = (new FloatSchema())->maximum($maximum);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMaximum(): void
    {
        $input = 4.2;
        $maximum = 4.1;

        $schema = (new FloatSchema())->maximum($maximum);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.maximum',
                        'template' => 'Value should be maximum {{maximum}} {{exclusiveMaximum}}, {{given}} given',
                        'variables' => [
                            'maximum' => $maximum,
                            'exclusiveMaximum' => false,
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidGte(): void
    {
        $input = 4.1;
        $gte = 4.1;

        error_clear_last();

        $schema = (new FloatSchema())->gte($gte);

        $lastError = error_get_last();

        self::assertNotNull($lastError);
        self::assertArrayHasKey('type', $lastError);
        self::assertSame(E_USER_DEPRECATED, $lastError['type']);
        self::assertArrayHasKey('message', $lastError);
        self::assertSame('Use minimum($gte) instead', $lastError['message']);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidGte(): void
    {
        $input = 4.1;
        $gte = 4.2;

        $schema = (new FloatSchema())->gte($gte);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.gte',
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

    public function testParseWithValidGt(): void
    {
        $input = 4.2;
        $gt = 4.1;

        error_clear_last();

        $schema = (new FloatSchema())->gt($gt);

        $lastError = error_get_last();

        self::assertNotNull($lastError);
        self::assertArrayHasKey('type', $lastError);
        self::assertSame(E_USER_DEPRECATED, $lastError['type']);
        self::assertArrayHasKey('message', $lastError);
        self::assertSame('Use minimum($gt, true) instead', $lastError['message']);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidGtEqual(): void
    {
        $input = 4.1;
        $gt = 4.1;

        $schema = (new FloatSchema())->gt($gt);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame(
                [
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'float.gt',
                            'template' => 'Value should be greater than {{gt}}, {{given}} given',
                            'variables' => [
                                'gt' => $gt,
                                'given' => $input,
                            ],
                        ],
                    ],
                ],
                $errorsException->errors->jsonSerialize()
            );
        }
    }

    public function testParseWithInvalidGtLesser(): void
    {
        $input = 4.1;
        $gt = 4.2;

        $schema = (new FloatSchema())->gt($gt);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame(
                [
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'float.gt',
                            'template' => 'Value should be greater than {{gt}}, {{given}} given',
                            'variables' => [
                                'gt' => $gt,
                                'given' => $input,
                            ],
                        ],
                    ],
                ],
                $errorsException->errors->jsonSerialize()
            );
        }
    }

    public function testParseWithValidLt(): void
    {
        $input = 4.1;
        $lt = 4.2;

        error_clear_last();

        $schema = (new FloatSchema())->lt($lt);

        $lastError = error_get_last();

        self::assertNotNull($lastError);
        self::assertArrayHasKey('type', $lastError);
        self::assertSame(E_USER_DEPRECATED, $lastError['type']);
        self::assertArrayHasKey('message', $lastError);
        self::assertSame('Use maximum($lt, true) instead', $lastError['message']);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidLtEqual(): void
    {
        $input = 4.1;
        $lt = 4.1;

        $schema = (new FloatSchema())->lt($lt);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.lt',
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
        $input = 4.2;
        $lt = 4.1;

        $schema = (new FloatSchema())->lt($lt);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.lt',
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
        $input = 4.1;
        $lte = 4.1;

        error_clear_last();

        $schema = (new FloatSchema())->lte($lte);

        $lastError = error_get_last();

        self::assertNotNull($lastError);
        self::assertArrayHasKey('type', $lastError);
        self::assertSame(E_USER_DEPRECATED, $lastError['type']);
        self::assertArrayHasKey('message', $lastError);
        self::assertSame('Use maximum($lte) instead', $lastError['message']);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidLte(): void
    {
        $input = 4.2;
        $lte = 4.1;

        $schema = (new FloatSchema())->lte($lte);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.lte',
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
        $input = 0.1;

        $schema = (new FloatSchema())->positive();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidPositive(): void
    {
        $input = 0.0;

        $schema = (new FloatSchema())->positive();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.gt',
                        'template' => 'Value should be greater than {{gt}}, {{given}} given',
                        'variables' => [
                            'gt' => 0,
                            'given' => 0,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidNonNegative(): void
    {
        $input = 0.0;

        $schema = (new FloatSchema())->nonNegative();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidNonNegative(): void
    {
        $input = -0.1;

        $schema = (new FloatSchema())->nonNegative();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.gte',
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
        $input = -0.1;

        $schema = (new FloatSchema())->negative();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidNegative(): void
    {
        $input = 0.0;

        $schema = (new FloatSchema())->negative();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.lt',
                        'template' => 'Value should be lesser than {{lt}}, {{given}} given',
                        'variables' => [
                            'lt' => 0,
                            'given' => 0,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidNonPositive(): void
    {
        $input = 0.0;

        $schema = (new FloatSchema())->nonPositive();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidNonPositive(): void
    {
        $input = 0.1;

        $schema = (new FloatSchema())->nonPositive();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.lte',
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

    public function testParseWithValidtoInt(): void
    {
        $input = 4.0;

        $schema = (new FloatSchema())->toInt()->gte(4);

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseWithToIntNullable(): void
    {
        $schema = (new FloatSchema())->nullable()->toInt();

        self::assertNull($schema->parse(null));
    }

    public function testParseWithInvalidToInt(): void
    {
        $input = 4.2;

        $schema = (new FloatSchema())->toInt();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'float.int',
                        'template' => 'Cannot convert {{given}} to int',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithToString(): void
    {
        $input = 4.2;

        $schema = (new FloatSchema())->toString()->length(3);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseWithToStringNullable(): void
    {
        $schema = (new FloatSchema())->nullable()->toString();

        self::assertNull($schema->parse(null));
    }
}
