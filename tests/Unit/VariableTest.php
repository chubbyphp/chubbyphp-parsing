<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\Variable;
use PHPUnit\Framework\TestCase;

enum DummyBackedEnum: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

enum DummyUnitEnum
{
    case Hearts;
    case Diamonds;
    case Clubs;
    case Spades;
}

final class Dummy {}

/**
 * @covers \Chubbyphp\Parsing\Variable
 *
 * @internal
 */
final class VariableTest extends TestCase
{
    public function testToCode(): void
    {
        $resource = fopen('php://memory', 'r');

        self::assertSame('null', Variable::toCode(null));
        self::assertSame('true', Variable::toCode(true));
        self::assertSame('false', Variable::toCode(false));
        self::assertSame('1', Variable::toCode(1));
        self::assertSame('1.234', Variable::toCode(1.234));
        self::assertSame('1200.0', Variable::toCode(1.2e3));
        self::assertSame('7.0E-10', Variable::toCode(7E-10));
        self::assertSame('1234.567', Variable::toCode(1_234.567));
        self::assertSame('NAN', Variable::toCode(NAN));
        self::assertSame('INF', Variable::toCode(INF));
        self::assertSame('-INF', Variable::toCode(-INF));
        self::assertSame("'test'", Variable::toCode('test'));
        self::assertSame(
            "new \\DateTimeImmutable('2024-01-20T09:15:00+00:00')",
            Variable::toCode(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'))
        );
        self::assertSame(
            '\\'.DummyBackedEnum::class.'::from(\'H\')',
            Variable::toCode(DummyBackedEnum::Hearts)
        );
        self::assertSame(
            '\\'.DummyUnitEnum::class.'::Hearts',
            Variable::toCode(DummyUnitEnum::Hearts)
        );

        self::assertSame(
            '<\\'.Dummy::class.'>',
            Variable::toCode(new Dummy())
        );

        self::assertSame('<resource>', Variable::toCode($resource));

        self::assertSame(
            "[
    'null' => null,
    'true' => true,
    'false' => 'false',
    1 => 1,
    '1.234' => 1.234,
    '1.2e3' => 1200.0,
    '7E-10' => 7.0E-10,
    '1_234.567' => 1234.567,
    'test' => 'test',
    '2024-01-20T09:15:00+00:00' => new \\DateTimeImmutable('2024-01-20T09:15:00+00:00'),
    'DummyBackedEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\DummyBackedEnum::from('H'),
    'DummyUnitEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\DummyUnitEnum::Hearts,
    'Dummy' => <\\Chubbyphp\\Tests\\Parsing\\Unit\\Dummy>,
    'resource' => <resource>,
    'array' => [
        'null' => null,
        'true' => true,
        'false' => 'false',
        1 => 1,
        '1.234' => 1.234,
        '1.2e3' => 1200.0,
        '7E-10' => 7.0E-10,
        '1_234.567' => 1234.567,
        'test' => 'test',
        '2024-01-20T09:15:00+00:00' => new \\DateTimeImmutable('2024-01-20T09:15:00+00:00'),
        'DummyBackedEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\DummyBackedEnum::from('H'),
        'DummyUnitEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\DummyUnitEnum::Hearts,
        'Dummy' => <\\Chubbyphp\\Tests\\Parsing\\Unit\\Dummy>,
        'resource' => <resource>,
    ],
]",
            Variable::toCode([
                'null' => null,
                'true' => true,
                'false' => 'false',
                '1' => 1,
                '1.234' => 1.234,
                '1.2e3' => 1.2e3,
                '7E-10' => 7E-10,
                '1_234.567' => 1_234.567,
                'test' => 'test',
                '2024-01-20T09:15:00+00:00' => new \DateTimeImmutable('2024-01-20T09:15:00+00:00'),
                'DummyBackedEnum::Hearts' => DummyBackedEnum::Hearts,
                'DummyUnitEnum::Hearts' => DummyUnitEnum::Hearts,
                'Dummy' => new Dummy(),
                'resource' => $resource,
                'array' => [
                    'null' => null,
                    'true' => true,
                    'false' => 'false',
                    '1' => 1,
                    '1.234' => 1.234,
                    '1.2e3' => 1.2e3,
                    '7E-10' => 7E-10,
                    '1_234.567' => 1_234.567,
                    'test' => 'test',
                    '2024-01-20T09:15:00+00:00' => new \DateTimeImmutable('2024-01-20T09:15:00+00:00'),
                    'DummyBackedEnum::Hearts' => DummyBackedEnum::Hearts,
                    'DummyUnitEnum::Hearts' => DummyUnitEnum::Hearts,
                    'Dummy' => new Dummy(),
                    'resource' => $resource,
                ],
            ])
        );

        self::assertSame(
            "(object) [
    'null' => null,
    'true' => true,
    'false' => 'false',
    1 => 1,
    '1.234' => 1.234,
    '1.2e3' => 1200.0,
    '7E-10' => 7.0E-10,
    '1_234.567' => 1234.567,
    'test' => 'test',
    '2024-01-20T09:15:00+00:00' => new \\DateTimeImmutable('2024-01-20T09:15:00+00:00'),
    'DummyBackedEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\DummyBackedEnum::from('H'),
    'DummyUnitEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\DummyUnitEnum::Hearts,
    'Dummy' => <\\Chubbyphp\\Tests\\Parsing\\Unit\\Dummy>,
    'resource' => <resource>,
    'stdClass' => (object) [
        'null' => null,
        'true' => true,
        'false' => 'false',
        1 => 1,
        '1.234' => 1.234,
        '1.2e3' => 1200.0,
        '7E-10' => 7.0E-10,
        '1_234.567' => 1234.567,
        'test' => 'test',
        '2024-01-20T09:15:00+00:00' => new \\DateTimeImmutable('2024-01-20T09:15:00+00:00'),
        'DummyBackedEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\DummyBackedEnum::from('H'),
        'DummyUnitEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\DummyUnitEnum::Hearts,
        'Dummy' => <\\Chubbyphp\\Tests\\Parsing\\Unit\\Dummy>,
        'resource' => <resource>,
    ],
]",
            Variable::toCode((object) [
                'null' => null,
                'true' => true,
                'false' => 'false',
                '1' => 1,
                '1.234' => 1.234,
                '1.2e3' => 1.2e3,
                '7E-10' => 7E-10,
                '1_234.567' => 1_234.567,
                'test' => 'test',
                '2024-01-20T09:15:00+00:00' => new \DateTimeImmutable('2024-01-20T09:15:00+00:00'),
                'DummyBackedEnum::Hearts' => DummyBackedEnum::Hearts,
                'DummyUnitEnum::Hearts' => DummyUnitEnum::Hearts,
                'Dummy' => new Dummy(),
                'resource' => $resource,
                'stdClass' => (object) [
                    'null' => null,
                    'true' => true,
                    'false' => 'false',
                    '1' => 1,
                    '1.234' => 1.234,
                    '1.2e3' => 1.2e3,
                    '7E-10' => 7E-10,
                    '1_234.567' => 1_234.567,
                    'test' => 'test',
                    '2024-01-20T09:15:00+00:00' => new \DateTimeImmutable('2024-01-20T09:15:00+00:00'),
                    'DummyBackedEnum::Hearts' => DummyBackedEnum::Hearts,
                    'DummyUnitEnum::Hearts' => DummyUnitEnum::Hearts,
                    'Dummy' => new Dummy(),
                    'resource' => $resource,
                ],
            ])
        );

        fclose($resource);
    }
}
