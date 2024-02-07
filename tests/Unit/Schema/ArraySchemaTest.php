<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\ArraySchema;
use Chubbyphp\Parsing\Schema\DateTimeSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
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
    public function testImmutability(): void
    {
        $schema = new ArraySchema(new StringSchema());

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(['test']));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (array $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (array $output, ParserErrorException $e) => $output));
    }

    public function testParseSuccess(): void
    {
        $input = ['test'];

        $schema = new ArraySchema(new StringSchema());

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = ['test1'];
        $input2 = ['test2'];

        $schema = (new ArraySchema(new StringSchema()))->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
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
                        'template' => 'Type should be "array", {{given}} given',
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
                        'template' => 'Type should be "string", {{given}} given',
                        'variables' => [
                            'given' => 'integer',
                        ],
                    ],
                ],
                2 => [
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", {{given}} given',
                        'variables' => [
                            'given' => 'boolean',
                        ],
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = ['test1'];

        $schema = (new ArraySchema(new StringSchema()))->preParse(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = ['test1'];

        $schema = (new ArraySchema(new StringSchema()))
            ->postParse(static fn (array $output) => array_merge($output, ['test2']))
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
                            'template' => 'Type should be "array", {{given}} given',
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
                    'template' => 'Type should be "array", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
            $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors())
        );
    }

    public function testParseWithValidLength(): void
    {
        $input = ['test', 'test', 'test', 'test'];

        $schema = (new ArraySchema(new StringSchema()))->length(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidLength(): void
    {
        $input = ['test', 'test', 'test', 'test'];

        $schema = (new ArraySchema(new StringSchema()))->length(5);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'array.length',
                    'template' => 'Length {{length}}, {{given}} given',
                    'variables' => [
                        'length' => 5,
                        'given' => \count($input),
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidMinLength(): void
    {
        $input = ['test', 'test', 'test', 'test'];

        $schema = (new ArraySchema(new StringSchema()))->minLength(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMinLength(): void
    {
        $input = ['test', 'test', 'test', 'test'];

        $schema = (new ArraySchema(new StringSchema()))->minLength(5);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'array.minLength',
                    'template' => 'Min length {{min}}, {{given}} given',
                    'variables' => [
                        'minLength' => 5,
                        'given' => \count($input),
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidMaxLength(): void
    {
        $input = ['test', 'test', 'test', 'test'];

        $schema = (new ArraySchema(new StringSchema()))->maxLength(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMaxLength(): void
    {
        $input = ['test', 'test', 'test', 'test'];

        $schema = (new ArraySchema(new StringSchema()))->maxLength(3);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'array.maxLength',
                    'template' => 'Max length {{max}}, {{given}} given',
                    'variables' => [
                        'maxLength' => 3,
                        'given' => \count($input),
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidIncludes(): void
    {
        $dateTime1 = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');
        $dateTime2 = new \DateTimeImmutable('2024-01-21T09:15:00+00:00');

        $input = [$dateTime1, $dateTime2];

        $schema = (new ArraySchema(new DateTimeSchema()))->includes($dateTime2);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithValidIncludesWithEqualButNotSame(): void
    {
        $dateTime1 = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');
        $dateTime2 = new \DateTimeImmutable('2024-01-21T09:15:00+00:00');

        $dateTime2Equal = new \DateTimeImmutable('2024-01-21T09:15:00+00:00');

        $input = [$dateTime1, $dateTime2];

        $schema = (new ArraySchema(new DateTimeSchema()))->includes($dateTime2Equal, false);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidIncludesWithEqualButNotSame(): void
    {
        $dateTime1 = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');
        $dateTime2 = new \DateTimeImmutable('2024-01-21T09:15:00+00:00');

        $dateTime2Equal = new \DateTimeImmutable('2024-01-21T09:15:00+00:00');

        $input = [$dateTime1, $dateTime2];

        $schema = (new ArraySchema(new DateTimeSchema()))->includes($dateTime2Equal);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'array.includes',
                    'template' => '{{given}} does not include {{includes}}',
                    'variables' => [
                        'includes' => json_decode(json_encode($dateTime2Equal), true),
                        'given' => json_decode(json_encode($input), true),
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithInvalidIncludes(): void
    {
        $dateTime1 = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');
        $dateTime2 = new \DateTimeImmutable('2024-01-21T09:15:00+00:00');
        $dateTime3 = new \DateTimeImmutable('2024-01-22T09:15:00+00:00');

        $input = [$dateTime1, $dateTime2];

        $schema = (new ArraySchema(new DateTimeSchema()))->includes($dateTime3);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'array.includes',
                    'template' => '{{given}} does not include {{includes}}',
                    'variables' => [
                        'includes' => json_decode(json_encode($dateTime3), true),
                        'given' => json_decode(json_encode($input), true),
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithFilter(): void
    {
        $input = [1, 2, 3, 4, 5];

        $schema = (new ArraySchema(new IntSchema()))->filter(static fn (int $value) => 0 === $value % 2);

        self::assertSame([2, 4], $schema->parse($input));
    }

    public function testParseWithMap(): void
    {
        $input = [1, 2, 3, 4, 5];

        $schema = (new ArraySchema(new IntSchema()))->map(static fn (int $value) => $value * 2);

        self::assertSame([2, 4, 6, 8, 10], $schema->parse($input));
    }

    public function testParseWithSort(): void
    {
        $input = [5, 4, 3, 2, 1];

        $schema = (new ArraySchema(new IntSchema()))->sort();

        self::assertSame([5, 4, 3, 2, 1], $input); // make sure its not by reference
        self::assertSame([1, 2, 3, 4, 5], $schema->parse($input));
    }

    public function testParseWithSortWithFunction(): void
    {
        $input = [1, 2, 3, 4, 5];

        $schema = (new ArraySchema(new IntSchema()))->sort(static fn (int $a, int $b) => $b - $a);

        self::assertSame([1, 2, 3, 4, 5], $input); // make sure its not by reference
        self::assertSame([5, 4, 3, 2, 1], $schema->parse($input));
    }

    public function testParseWithReduce(): void
    {
        $input = [1, 2, 3, 4, 5];

        $schema = (new ArraySchema(new IntSchema()))->reduce(static fn (int $sum, int $current) => $sum + $current, 0);

        self::assertSame(array_sum($input), $schema->parse($input));
    }
}
