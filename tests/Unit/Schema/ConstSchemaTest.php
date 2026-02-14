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
                        'code' => 'const.type',
                        'template' => 'Type should be "bool|float|int|string", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
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
                            'code' => 'const.type',
                            'template' => 'Type should be "bool|float|int|string", {{given}} given',
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
                    'code' => 'const.type',
                    'template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
        ], $schema->safeParse(null)->exception->errors->jsonSerialize());
    }
}
