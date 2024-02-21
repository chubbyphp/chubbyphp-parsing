<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\RespectValidationSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;
use Respect\Validation\Validator as v;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\RespectValidationSchema
 *
 * @internal
 */
final class RespectValidationSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new RespectValidationSchema(v::numericVal()->positive()->between(1, 255));

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(42));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (float|int $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (float|int $output, ParserErrorException $e) => $output));
    }

    public function testParseSuccess(): void
    {
        $input = 5;

        $schema = new RespectValidationSchema(v::numericVal()->positive()->between(1, 255));

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = 5;
        $input2 = 10;

        $schema = (new RespectValidationSchema(v::numericVal()->positive()->between(1, 255)))->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new RespectValidationSchema(v::numericVal()->positive()->between(1, 255)))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailed(): void
    {
        $input = null;

        $schema = new RespectValidationSchema(v::numericVal()->positive()->between(1, 255));

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'numericVal',
                    'template' => '`NULL` must be numeric',
                    'variables' => [
                        'input' => null,
                    ],
                ],
                [
                    'code' => 'positive',
                    'template' => '`NULL` must be positive',
                    'variables' => [
                        'input' => null,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = 5;

        $schema = (new RespectValidationSchema(v::numericVal()->positive()->between(1, 255)))
            ->preParse(static fn () => $input)
        ;

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = 5;

        $schema = (new RespectValidationSchema(v::numericVal()->positive()->between(1, 255)))
            ->postParse(static fn (int $output) => $output + 1)
        ;

        self::assertSame($input + 1, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new RespectValidationSchema(v::numericVal()->positive()->between(1, 255)))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'numericVal',
                        'template' => '`NULL` must be numeric',
                        'variables' => [
                            'input' => null,
                        ],
                    ],
                    [
                        'code' => 'positive',
                        'template' => '`NULL` must be positive',
                        'variables' => [
                            'input' => null,
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
        $input = 5;

        $schema = new RespectValidationSchema(v::numericVal()->positive()->between(1, 255));

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $input = null;

        $schema = new RespectValidationSchema(v::numericVal()->positive()->between(1, 255));

        self::assertSame([
            [
                'code' => 'numericVal',
                'template' => '`NULL` must be numeric',
                'variables' => [
                    'input' => null,
                ],
            ],
            [
                'code' => 'positive',
                'template' => '`NULL` must be positive',
                'variables' => [
                    'input' => null,
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse($input)->exception->getErrors()));
    }
}
