<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\ConstSchema;
use Chubbyphp\Parsing\Schema\IfThenElseSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\IfThenElseSchema
 *
 * @internal
 */
final class IfThenElseSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new IfThenElseSchema(new StringSchema());

        self::assertNotSame($schema, $schema->then(new StringSchema()));
        self::assertNotSame($schema, $schema->else(new StringSchema()));
        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(1));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (mixed $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (mixed $output, ErrorsException $e) => $output));
    }

    public function testParseSuccessWithThen(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->toUpperCase())
            ->else(new IntSchema())
        ;

        self::assertSame('TEST', $schema->parse('test'));
    }

    public function testParseSuccessWithElse(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->toUpperCase())
            ->else((new IntSchema())->toString())
        ;

        self::assertSame('1', $schema->parse(1));
    }

    public function testParseSuccessWithoutThen(): void
    {
        $input = 1.0;

        $schema = new IfThenElseSchema(new IntSchema());

        // the if schema coerces 1.0 to 1, without a then branch the input stays unchanged
        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithoutElse(): void
    {
        $input = 1;

        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->toUpperCase())
        ;

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessBranchReceivesOriginalInput(): void
    {
        $input = 'test';

        $schema = (new IfThenElseSchema((new StringSchema())->toUpperCase()))
            ->then(new ConstSchema('test'))
        ;

        // the if schema outputs 'TEST', but the then branch must receive the original 'test'
        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithNull(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->toUpperCase())
        ;

        self::assertNull($schema->parse(null));
    }

    public function testParseSuccessWithDefault(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->toUpperCase())
            ->default('test')
        ;

        self::assertSame('TEST', $schema->parse(null));
        self::assertSame('OTHER', $schema->parse('other'));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new IfThenElseSchema(new ConstSchema(null)))
            ->then(new StringSchema())
            ->nullable()
        ;

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithThen(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->minLength(10))
        ;

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
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithElse(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->else(new IntSchema())
        ;

        try {
            $schema->parse(1.5);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
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

    public function testParseSuccessWithPostParse(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->toUpperCase())
            ->postParse(static fn (string $output) => $output.'!')
        ;

        self::assertSame('TEST!', $schema->parse('test'));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->minLength(10))
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertSame('test', $input);

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
                ], $errorsException->errors->jsonSerialize());

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse('test'));
    }

    public function testSafeParseSuccess(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->toUpperCase())
        ;

        self::assertSame('TEST', $schema->safeParse('test')->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = (new IfThenElseSchema(new StringSchema()))
            ->then((new StringSchema())->minLength(10))
        ;

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
        ], $schema->safeParse('test')->exception->errors->jsonSerialize());
    }
}
