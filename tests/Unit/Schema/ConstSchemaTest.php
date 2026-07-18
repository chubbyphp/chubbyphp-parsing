<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\ConstSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\ConstSchema
 *
 * @internal
 */
final class ConstSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new ConstSchema('test');

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default('test'));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (string $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (string $output, ErrorsException $e) => $output));
    }

    public function testParseSuccessWithBool(): void
    {
        $input = true;

        $schema = new ConstSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithInt(): void
    {
        $input = 1;

        $schema = new ConstSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithFloat(): void
    {
        $input = 1.5;

        $schema = new ConstSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithString(): void
    {
        $input = 'test';

        $schema = new ConstSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithIntConstAndFloatInput(): void
    {
        $schema = new ConstSchema(1);

        self::assertSame(1.0, $schema->parse(1.0));
    }

    public function testParseSuccessWithFloatConstAndIntInput(): void
    {
        $schema = new ConstSchema(1.0);

        self::assertSame(1, $schema->parse(1));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = 'test';

        $schema = (new ConstSchema($input))->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new ConstSchema('test'))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseSuccessWithNullConst(): void
    {
        $schema = new ConstSchema(null);

        self::assertNull($schema->parse(null));
    }

    public function testParseSuccessWithArray(): void
    {
        $input = [1, 'two', true];

        $schema = new ConstSchema($input);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithIntArrayConstAndFloatArrayInput(): void
    {
        $schema = new ConstSchema([1, 2.0]);

        self::assertSame([1.0, 2], $schema->parse([1.0, 2]));
    }

    public function testParseSuccessWithObjectConstAndDifferentPropertyOrderStdClassInput(): void
    {
        $schema = new ConstSchema(['b' => 2, 'a' => ['c' => 3.0]]);

        $input = new \stdClass();
        $input->a = ['c' => 3];
        $input->b = 2.0;

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new ConstSchema('test');

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => 'test',
                            'given' => null,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithNullConstAndStringInput(): void
    {
        $schema = new ConstSchema(null);

        try {
            $schema->parse('test');

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => null,
                            'given' => 'test',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithNotSupportedInput(): void
    {
        $schema = new ConstSchema('test');

        try {
            $schema->parse(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'));

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.type',
                        'template' => 'Type should be "array|bool|float|int|\stdClass|string|null", {{given}} given',
                        'variables' => [
                            'given' => \DateTimeImmutable::class,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithDifferentArrayOrder(): void
    {
        $schema = new ConstSchema([1, 2]);

        try {
            $schema->parse([2, 1]);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => [1, 2],
                            'given' => [2, 1],
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithAdditionalObjectProperty(): void
    {
        $schema = new ConstSchema(['a' => 1]);

        try {
            $schema->parse(['a' => 1, 'b' => 2]);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => ['a' => 1],
                            'given' => ['a' => 1, 'b' => 2],
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithDifferentStringConstValue(): void
    {
        $schema = new ConstSchema('test1');

        try {
            $schema->parse('test2');

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => 'test1',
                            'given' => 'test2',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithDifferentFloatConstValue(): void
    {
        $schema = new ConstSchema(4.2);

        try {
            $schema->parse(4.1);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => 4.2,
                            'given' => 4.1,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithDifferentIntConstValue(): void
    {
        $schema = new ConstSchema(1337);

        try {
            $schema->parse(1336);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => 1337,
                            'given' => 1336,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithDifferentBoolConstValue(): void
    {
        $schema = new ConstSchema(true);

        try {
            $schema->parse(false);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => true,
                            'given' => false,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithStringConstAndIntInput(): void
    {
        $schema = new ConstSchema('1');

        try {
            $schema->parse(1);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => '1',
                            'given' => 1,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithBoolConstAndIntInput(): void
    {
        $schema = new ConstSchema(true);

        try {
            $schema->parse(1);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => true,
                            'given' => 1,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithIntConstAndStringInput(): void
    {
        $schema = new ConstSchema(1);

        try {
            $schema->parse('1');

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'const.equals',
                        'template' => 'Input should be {{expected}}, {{given}} given',
                        'variables' => [
                            'expected' => 1,
                            'given' => '1',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = 'test1';

        $schema = (new ConstSchema($input))->preParse(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = 'test1';

        $schema = (new ConstSchema($input))->postParse(static fn (string $output) => $output.'1');

        self::assertSame('test11', $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new ConstSchema('test'))
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'const.equals',
                            'template' => 'Input should be {{expected}}, {{given}} given',
                            'variables' => [
                                'expected' => 'test',
                                'given' => null,
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
        $input = 'test';

        $schema = new ConstSchema($input);

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new ConstSchema('test');

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'const.equals',
                    'template' => 'Input should be {{expected}}, {{given}} given',
                    'variables' => [
                        'expected' => 'test',
                        'given' => null,
                    ],
                ],
            ],
        ], $schema->safeParse(null)->exception->errors->jsonSerialize());
    }
}
