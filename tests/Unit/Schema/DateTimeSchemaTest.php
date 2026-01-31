<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\DateTimeSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\DateTimeSchema
 *
 * @internal
 */
final class DateTimeSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new DateTimeSchema();

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(new \DateTimeImmutable('2024-01-20T09:15:00+00:00')));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (\DateTimeInterface $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (\DateTimeInterface $output, ErrorsException $e) => $output));
    }

    public function testParseSuccess(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');

        $schema = new DateTimeSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');
        $input2 = new \DateTimeImmutable('2024-01-21T09:15:00+00:00');

        $schema = (new DateTimeSchema())->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new DateTimeSchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new DateTimeSchema();

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'datetime.type',
                        'template' => 'Type should be "\DateTimeInterface", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');

        $schema = (new DateTimeSchema())->preParse(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');

        $schema = (new DateTimeSchema())->postParse(static fn (\DateTimeInterface $output) => $output->format('c'));

        self::assertSame($input->format('c'), $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new DateTimeSchema())
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'datetime.type',
                            'template' => 'Type should be "\DateTimeInterface", {{given}} given',
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
        $input = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');

        $schema = new DateTimeSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new DateTimeSchema();

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'datetime.type',
                    'template' => 'Type should be "\DateTimeInterface", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
        ], $schema->safeParse(null)->exception->errors->jsonSerialize());
    }

    public function testParseWithValidFrom(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');
        $min = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');

        $schema = (new DateTimeSchema())->from($min);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidFrom(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');
        $min = new \DateTimeImmutable('2024-01-20T09:15:01+00:00');

        $schema = (new DateTimeSchema())->from($min);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'datetime.from',
                        'template' => 'From datetime {{from}}, {{given}} given',
                        'variables' => [
                            'from' => $min->format('c'),
                            'given' => $input->format('c'),
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidTo(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');
        $max = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');

        $schema = (new DateTimeSchema())->to($max);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidTo(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:01+00:00');
        $max = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');

        $schema = (new DateTimeSchema())->to($max);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame(
                [
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'datetime.to',
                            'template' => 'To datetime {{to}}, {{given}} given',
                            'variables' => [
                                'to' => $max->format('c'),
                                'given' => $input->format('c'),
                            ],
                        ],
                    ],
                ],
                $errorsException->errors->jsonSerialize()
            );
        }
    }

    public function testParseWithToInt(): void
    {
        $input = new \DateTimeImmutable('2024-01-20T09:15:00+00:00');

        $schema = (new DateTimeSchema())->toInt()->positive();

        self::assertSame($input->getTimestamp(), $schema->parse($input));
    }

    public function testParseWithToIntNullable(): void
    {
        $schema = (new DateTimeSchema())->nullable()->toInt();

        self::assertNull($schema->parse(null));
    }

    public function testParseWithToString(): void
    {
        $inputString = '2024-01-20T09:15:00+00:00';

        $input = new \DateTimeImmutable($inputString);

        $schema = (new DateTimeSchema())->toString()->length(25);

        self::assertSame($inputString, $schema->parse($input));
    }

    public function testParseWithToStringNullable(): void
    {
        $schema = (new DateTimeSchema())->nullable()->toString();

        self::assertNull($schema->parse(null));
    }
}
