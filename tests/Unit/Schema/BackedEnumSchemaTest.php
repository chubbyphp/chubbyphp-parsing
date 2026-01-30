<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\BackedEnumSchema;
use PHPUnit\Framework\TestCase;

enum Suit
{
    case Hearts;
    case Diamonds;
    case Clubs;
    case Spades;
}

enum BackedSuit: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

enum BackedSuitInt: int
{
    case Hearts = 1;
    case Diamonds = 2;
    case Clubs = 3;
    case Spades = 4;
}

enum BackedEmpty: string {}

/**
 * @covers \Chubbyphp\Parsing\Schema\BackedEnumSchema
 *
 * @internal
 */
final class BackedEnumSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new BackedEnumSchema(BackedSuit::class);

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default(true));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (BackedSuit $output) => $output->value));
        self::assertNotSame($schema, $schema->catch(static fn (BackedSuit $output, ErrorsException $e) => $output->value));
    }

    public function testConstructWithoutEnumClass(): void
    {
        try {
            new BackedEnumSchema(\stdClass::class);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 ($backedEnum) must be of type \BackedEnum::class, string given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testConstructWithoutBackedEnumClass(): void
    {
        try {
            new BackedEnumSchema(Suit::class);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 ($backedEnum) must be of type \BackedEnum::class, string given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testConstructWithEmptyBackedEnumClass(): void
    {
        try {
            new BackedEnumSchema(BackedEmpty::class);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Argument #1 ($backedEnum) must be of type \BackedEnum::class, string given',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testParseSuccess(): void
    {
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = new BackedEnumSchema(BackedSuit::class);

        self::assertSame($enum, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $enum1 = BackedSuit::Diamonds;
        $input1 = $enum1->value;

        $enum2 = BackedSuit::Hearts;
        $input2 = $enum2->value;

        $schema = (new BackedEnumSchema(BackedSuit::class))->default($input1);

        self::assertSame($enum1, $schema->parse(null));
        self::assertSame($enum2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new BackedEnumSchema(BackedSuit::class))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new BackedEnumSchema(BackedSuit::class);

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'backedEnum.type',
                        'template' => 'Type should be "int|string", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithUnknownValue(): void
    {
        $schema = new BackedEnumSchema(BackedSuit::class);

        try {
            $schema->parse('Z');

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'backedEnum.value',
                        'template' => 'Value should be one of {{cases}}, {{given}} given',
                        'variables' => [
                            'cases' => ['H', 'D', 'C', 'S'],
                            'given' => 'Z',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuit::class))->preParse(static fn () => $input);

        self::assertSame($enum, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuit::class))->postParse(static fn (BackedSuit $output) => $output->value);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new BackedEnumSchema(BackedSuit::class))
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'backedEnum.type',
                            'template' => 'Type should be "int|string", {{given}} given',
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
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = new BackedEnumSchema(BackedSuit::class);

        self::assertSame($enum, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new BackedEnumSchema(BackedSuit::class);

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'backedEnum.type',
                    'template' => 'Type should be "int|string", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
        ], $schema->safeParse(null)->exception->errors->jsonSerialize());
    }

    public function testParseWithValidtoInt(): void
    {
        $enum = BackedSuitInt::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuitInt::class))->toInt();

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseWithValidtoIntNullable(): void
    {
        $schema = (new BackedEnumSchema(BackedSuitInt::class))->nullable()->toInt();

        self::assertNull($schema->parse(null));
    }

    public function testParseWithInvalidToInt(): void
    {
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuit::class))->toInt();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'int.type',
                        'template' => 'Type should be "int", {{given}} given',
                        'variables' => [
                            'given' => 'string',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidtoString(): void
    {
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuit::class))->toString();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithValidtoStringNullable(): void
    {
        $schema = (new BackedEnumSchema(BackedSuit::class))->nullable()->toString();

        self::assertNull($schema->parse(null));
    }

    public function testParseWithInvalidToString(): void
    {
        $enum = BackedSuitInt::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuitInt::class))->toString();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", {{given}} given',
                        'variables' => [
                            'given' => 'integer',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }
}
