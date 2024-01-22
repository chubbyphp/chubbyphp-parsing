<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\ArraySchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\ArraySchema
 *
 * @internal
 */
final class ArraySchemaTest extends AbstractTestCase
{
    public function testParseSuccess(): void
    {
        $input = ['test'];

        $schema = new ArraySchema(new StringSchema());

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = ['test'];

        $schema = (new ArraySchema(new StringSchema()))->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new ArraySchema(new StringSchema()))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new ArraySchema(new StringSchema());

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(
                [
                    [
                        'code' => 'array.type',
                        'template' => 'Type should be "array", "{{given}}" given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
                $this->errorsToSimpleArray($parserErrorException->getErrors())
            );
        }
    }

    public function testParseFailedWithoutStringInArray(): void
    {
        $input = ['test', 1, true];

        $schema = new ArraySchema(new StringSchema());

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                1 => [
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", "{{given}}" given',
                        'variables' => [
                            'given' => 'integer',
                        ],
                    ],
                ],
                2 => [
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", "{{given}}" given',
                        'variables' => [
                            'given' => 'boolean',
                        ],
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = ['test1'];

        $schema = (new ArraySchema(new StringSchema()))
            ->transform(static fn (array $output) => array_merge($output, ['test2']))
        ;

        self::assertSame(array_merge($input, ['test2']), $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new ArraySchema(new StringSchema()))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);

                self::assertSame(
                    [
                        [
                            'code' => 'array.type',
                            'template' => 'Type should be "array", "{{given}}" given',
                            'variables' => [
                                'given' => 'NULL',
                            ],
                        ],
                    ],
                    $this->errorsToSimpleArray($parserErrorException->getErrors())
                );

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = ['test'];

        $schema = new ArraySchema(new StringSchema());

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new ArraySchema(new StringSchema());

        self::assertSame(
            [
                [
                    'code' => 'array.type',
                    'template' => 'Type should be "array", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
            $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors())
        );
    }
}
