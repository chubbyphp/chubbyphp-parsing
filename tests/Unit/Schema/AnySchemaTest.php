<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\AnySchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AnySchema
 *
 * @internal
 */
final class AnySchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new AnySchema();

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(1));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (mixed $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (mixed $output, ErrorsException $e) => $output));
    }

    public function testParseSuccess(): void
    {
        $schema = new AnySchema();

        $object = new \stdClass();

        self::assertSame('test', $schema->parse('test'));
        self::assertSame(1, $schema->parse(1));
        self::assertSame(1.5, $schema->parse(1.5));
        self::assertTrue($schema->parse(true));
        self::assertSame($object, $schema->parse($object));
    }

    public function testParseSuccessWithNull(): void
    {
        $schema = new AnySchema();

        self::assertNull($schema->parse(null));
    }

    public function testParseSuccessWithArrayUnchanged(): void
    {
        $input = ['key2' => 'value2', 'key1' => 'value1'];

        $schema = new AnySchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = 'test';
        $input2 = 'other';

        $schema = (new AnySchema())->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new AnySchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = 1;

        $schema = (new AnySchema())->postParse(static fn (mixed $output) => (string) $output);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseFailedWithPostParseAndCatch(): void
    {
        $schema = (new AnySchema())
            ->postParse(static function (mixed $output): void {
                throw new ErrorsException(
                    new Error('any.postParse', 'postParse {{given}} given', ['given' => $output])
                );
            })
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertSame('test', $input);

                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'any.postParse',
                            'template' => 'postParse {{given}} given',
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

        $schema = new AnySchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }
}
