<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\ConstSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\NotSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\NotSchema
 *
 * @internal
 */
final class NotSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new NotSchema(new StringSchema());

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(1));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (mixed $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (mixed $output, ErrorsException $e) => $output));
    }

    public function testParseSuccess(): void
    {
        $input = 1;

        $schema = new NotSchema(new StringSchema());

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithNull(): void
    {
        $schema = new NotSchema(new StringSchema());

        self::assertNull($schema->parse(null));
    }

    public function testParseSuccessWithArrayUnchanged(): void
    {
        $input = ['key2' => 'value2', 'key1' => 'value1'];

        $schema = new NotSchema(new StringSchema());

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = 'test';
        $input2 = 'other';

        $schema = (new NotSchema(new IntSchema()))->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new NotSchema(new ConstSchema(null)))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new NotSchema(new ConstSchema(null));

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'not.match',
                        'template' => 'Input should not match the given schema, {{given}} given',
                        'variables' => [
                            'given' => null,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailed(): void
    {
        $schema = new NotSchema(new StringSchema());

        try {
            $schema->parse('test');

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'not.match',
                        'template' => 'Input should not match the given schema, {{given}} given',
                        'variables' => [
                            'given' => 'test',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = 1;

        $schema = (new NotSchema(new StringSchema()))->postParse(static fn (mixed $output) => (string) $output);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new NotSchema(new StringSchema()))
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertSame('test', $input);

                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'not.match',
                            'template' => 'Input should not match the given schema, {{given}} given',
                            'variables' => [
                                'given' => 'test',
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
        $input = 1;

        $schema = new NotSchema(new StringSchema());

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new NotSchema(new StringSchema());

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'not.match',
                    'template' => 'Input should not match the given schema, {{given}} given',
                    'variables' => [
                        'given' => 'test',
                    ],
                ],
            ],
        ], $schema->safeParse('test')->exception->errors->jsonSerialize());
    }
}
