<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\FloatSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\FloatSchema
 *
 * @internal
 */
final class FloatSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new FloatSchema();

        self::assertNotSame($schema, $schema->transform(static fn (float $output) => $output));
        self::assertNotSame($schema, $schema->default(4.2));
        self::assertNotSame($schema, $schema->catch(static fn (float $output, ParserErrorException $e) => $output));
        self::assertNotSame($schema, $schema->nullable());
    }

    public function testParseSuccess(): void
    {
        $input = 1.5;

        $schema = new FloatSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = 1.5;

        $schema = (new FloatSchema())->default($input);

        self::assertSame($input, $schema->parse(null));
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.type',
                    'template' => 'Type should be "float", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = 1.5;

        $schema = (new FloatSchema())->transform(static fn (float $output) => (string) $output);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new FloatSchema())

            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'float.type',
                        'template' => 'Type should be "float", "{{given}}" given',
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
        $input = 1.5;

        $schema = new FloatSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new FloatSchema();

        self::assertSame([
            [
                'code' => 'float.type',
                'template' => 'Type should be "float", "{{given}}" given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }

    public function testParseWithValidGt(): void
    {
        $input = 4.2;
        $gt = 4.1;

        $schema = (new FloatSchema())->gt($gt);

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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.gt',
                    'template' => 'Value should be greater than {{gt}}, {{given}} given',
                    'variables' => [
                        'gt' => $gt,
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.gt',
                    'template' => 'Value should be greater than {{gt}}, {{given}} given',
                    'variables' => [
                        'gt' => $gt,
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidGte(): void
    {
        $input = 4.1;
        $gte = 4.1;

        $schema = (new FloatSchema())->gte($gte);

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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.gte',
                    'template' => 'Value should be greater than or equal {{gte}}, {{given}} given',
                    'variables' => [
                        'gte' => $gte,
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidLt(): void
    {
        $input = 4.1;
        $lt = 4.2;

        $schema = (new FloatSchema())->lt($lt);

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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.lt',
                    'template' => 'Value should be lesser than {{lt}}, {{given}} given',
                    'variables' => [
                        'lt' => $lt,
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.lt',
                    'template' => 'Value should be lesser than {{lt}}, {{given}} given',
                    'variables' => [
                        'lt' => $lt,
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidLte(): void
    {
        $input = 4.1;
        $lte = 4.1;

        $schema = (new FloatSchema())->lte($lte);

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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.lte',
                    'template' => 'Value should be lesser than or equal {{lte}}, {{given}} given',
                    'variables' => [
                        'lte' => $lte,
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.gt',
                    'template' => 'Value should be greater than {{gt}}, {{given}} given',
                    'variables' => [
                        'gt' => 0,
                        'given' => 0,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.gte',
                    'template' => 'Value should be greater than or equal {{gte}}, {{given}} given',
                    'variables' => [
                        'gte' => 0,
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.lt',
                    'template' => 'Value should be lesser than {{lt}}, {{given}} given',
                    'variables' => [
                        'lt' => 0,
                        'given' => 0,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.lte',
                    'template' => 'Value should be lesser than or equal {{lte}}, {{given}} given',
                    'variables' => [
                        'lte' => 0,
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidtoInt(): void
    {
        $input = 4.0;

        $schema = (new FloatSchema())->toInt();

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseWithInvalidtoInt(): void
    {
        $input = 4.2;

        $schema = (new FloatSchema())->toInt();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'float.int',
                    'template' => 'Invalid int "{{given}}"',
                    'variables' => [
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithToString(): void
    {
        $input = 4.2;

        $schema = (new FloatSchema())->toString();

        self::assertSame((string) $input, $schema->parse($input));
    }
}
