<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\BoolSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\BoolSchema
 *
 * @internal
 */
final class BoolSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new BoolSchema();

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->default(true));
        self::assertNotSame($schema, $schema->middleware(static fn (bool $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (bool $output, ParserErrorException $e) => $output));
    }

    public function testParseSuccess(): void
    {
        $input = true;

        $schema = new BoolSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = true;

        $schema = (new BoolSchema())->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new BoolSchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new BoolSchema();

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'bool.type',
                    'template' => 'Type should be "bool", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithMiddleware(): void
    {
        $input = true;

        $schema = (new BoolSchema())->middleware(static fn (bool $output) => (bool) $output);

        self::assertSame((bool) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new BoolSchema())

            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'bool.type',
                        'template' => 'Type should be "bool", "{{given}}" given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ], $this->errorsToSimpleArray($parserErrorException->getErrors()));

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = true;

        $schema = new BoolSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new BoolSchema();

        self::assertSame([
            [
                'code' => 'bool.type',
                'template' => 'Type should be "bool", "{{given}}" given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }

    public function testParseWithToFloat(): void
    {
        $input = true;

        $schema = (new BoolSchema())->toFloat();

        self::assertSame((float) $input, $schema->parse($input));
    }

    public function testParseWithToInt(): void
    {
        $input = true;

        $schema = (new BoolSchema())->toInt();

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseWithToString(): void
    {
        $input = true;

        $schema = (new BoolSchema())->toString();

        self::assertSame((string) $input, $schema->parse($input));
    }
}
