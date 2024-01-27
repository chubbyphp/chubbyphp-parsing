<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\BackedEnumSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

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

enum BackedSuitIntString: string
{
    case Hearts = '1';
    case Diamonds = '2';
    case Clubs = '3';
    case Spades = '4';
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
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\BackedEnumSchema
 *
 * @internal
 */
final class BackedEnumSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new BackedEnumSchema(BackedSuit::class);

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->default(true));
        self::assertNotSame($schema, $schema->middleware(static fn (BackedSuit $output) => $output->value));
        self::assertNotSame($schema, $schema->catch(static fn (BackedSuit $output, ParserErrorException $e) => $output->value));
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
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuit::class))->default($input);

        self::assertSame($enum, $schema->parse(null));
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'backedEnum.type',
                    'template' => 'Type should be "int|string", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithUnknownValue(): void
    {
        $schema = new BackedEnumSchema(BackedSuit::class);

        try {
            $schema->parse('Z');

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'backedEnum.value',
                    'template' => 'Value should be one of {{cases}}, {{given}} given',
                    'variables' => [
                        'cases' => ['H', 'D', 'C', 'S'],
                        'given' => 'Z',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithmiddleware(): void
    {
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuit::class))->middleware(static fn (BackedSuit $output) => $output->value);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new BackedEnumSchema(BackedSuit::class))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'backedEnum.type',
                        'template' => 'Type should be "int|string", "{{given}}" given',
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
                'code' => 'backedEnum.type',
                'template' => 'Type should be "int|string", "{{given}}" given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }

    public function testParseWithValidtoInt(): void
    {
        $enum = BackedSuitIntString::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuitIntString::class))->toInt();

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseWithInvalidtoInt(): void
    {
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuit::class))->toInt();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'backedEnum.int',
                    'template' => 'Cannot convert {{given}} to int',
                    'variables' => [
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidtoStringWithStringEnum(): void
    {
        $enum = BackedSuit::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuit::class))->toString();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithValidtoStringWithIntEnum(): void
    {
        $enum = BackedSuitInt::Diamonds;
        $input = $enum->value;

        $schema = (new BackedEnumSchema(BackedSuitInt::class))->toString();

        self::assertSame((string) $input, $schema->parse($input));
    }
}
