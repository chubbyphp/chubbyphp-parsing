<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\AbstractSchemaInnerParse;
use PHPUnit\Framework\TestCase;

enum AbstractSchemaInnerParseEnum: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchemaInnerParse
 *
 * @internal
 */
final class AbstractSchemaInnerParseTest extends TestCase
{
    public function testNullable(): void
    {
        $schema = new class extends AbstractSchemaInnerParse {
            protected function innerParse(mixed $input): mixed
            {
                if (null === $input) {
                    throw new ErrorsException(new Error('type', 'Type error', []));
                }

                return $input;
            }
        };

        self::assertNotSame($schema, $schema->nullable());
        self::assertNull($schema->nullable()->parse(null));
        self::assertNull($schema->nullable(true)->parse(null));

        try {
            $schema->nullable(false)->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException) {
        }
    }

    public function testDefault(): void
    {
        $schema = new class extends AbstractSchemaInnerParse {
            protected function innerParse(mixed $input): mixed
            {
                return $input;
            }
        };

        self::assertNotSame($schema, $schema->default('default'));
        self::assertSame('default', $schema->default('default')->parse(null));
        self::assertSame('value', $schema->default('default')->parse('value'));
    }

    public function testPreParse(): void
    {
        $schema = new class extends AbstractSchemaInnerParse {
            protected function innerParse(mixed $input): mixed
            {
                return $input;
            }
        };

        self::assertNotSame($schema, $schema->preParse(static fn ($i) => $i));
        self::assertSame('a12', $schema
            ->preParse(static fn ($i) => $i.'1')
            ->preParse(static fn ($i) => $i.'2')
            ->parse('a'));
    }

    public function testPostParse(): void
    {
        $schema = new class extends AbstractSchemaInnerParse {
            protected function innerParse(mixed $input): mixed
            {
                return $input;
            }
        };

        self::assertNotSame($schema, $schema->postParse(static fn ($o) => $o));
        self::assertSame('a12', $schema
            ->postParse(static fn ($o) => $o.'1')
            ->postParse(static fn ($o) => $o.'2')
            ->parse('a'));
    }

    public function testSafeParse(): void
    {
        $successSchema = new class extends AbstractSchemaInnerParse {
            protected function innerParse(mixed $input): mixed
            {
                return $input;
            }
        };

        $result = $successSchema->safeParse('value');
        self::assertTrue($result->success);
        self::assertSame('value', $result->data);

        $failSchema = new class extends AbstractSchemaInnerParse {
            protected function innerParse(mixed $input): mixed
            {
                throw new ErrorsException(new Error('error', 'Error', []));
            }
        };

        $result = $failSchema->safeParse('value');
        self::assertFalse($result->success);
        self::assertInstanceOf(ErrorsException::class, $result->exception);
    }

    public function testCatch(): void
    {
        $schema = new class extends AbstractSchemaInnerParse {
            protected function innerParse(mixed $input): mixed
            {
                throw new ErrorsException(new Error('error', 'Error', []));
            }
        };

        self::assertNotSame($schema, $schema->catch(static fn ($i, $e) => $i));
        self::assertSame('caught', $schema->catch(static fn ($i, $e) => 'caught')->parse('value'));
    }

    public function testGetDataType(): void
    {
        $schema = new class extends AbstractSchemaInnerParse {
            protected function innerParse(mixed $input): mixed
            {
                throw new ErrorsException(new Error('type', '{{given}}', ['given' => $this->getDataType($input)]));
            }
        };

        try {
            $schema->parse('string');
        } catch (ErrorsException $e) {
            self::assertSame('string', $e->errors->jsonSerialize()[0]['error']['variables']['given']);
        }

        try {
            $schema->parse(new \stdClass());
        } catch (ErrorsException $e) {
            self::assertSame(\stdClass::class, $e->errors->jsonSerialize()[0]['error']['variables']['given']);
        }
    }

    public function testVarExport(): void
    {
        $schema = new class extends AbstractSchemaInnerParse {
            protected function innerParse(mixed $input): string
            {
                return $this->varExport($input);
            }
        };

        self::assertSame('null', $schema->parse(null));
        self::assertSame('true', $schema->parse(true));
        self::assertSame('false', $schema->parse(false));
        self::assertSame('1', $schema->parse(1));
        self::assertSame('1.234', $schema->parse(1.234));
        self::assertSame('1200.0', $schema->parse(1.2e3));
        self::assertSame('7.0E-10', $schema->parse(7E-10));
        self::assertSame('1234.567', $schema->parse(1_234.567));
        self::assertSame("'test'", $schema->parse('test'));
        self::assertSame(
            "new \\DateTimeImmutable('2024-01-20T09:15:00+00:00')",
            $schema->parse(new \DateTimeImmutable('2024-01-20T09:15:00+00:00'))
        );
        self::assertSame(
            '\Chubbyphp\Tests\Parsing\Unit\Schema\AbstractSchemaInnerParseEnum::from(\'H\')',
            $schema->parse(AbstractSchemaInnerParseEnum::Hearts)
        );

        self::assertSame(
            "['null' => null, 'true' => true, 'false' => 'false', 1 => 1, '1.234' => 1.234, '1.2e3' => 1200.0, '7E-10' => 7.0E-10, '1_234.567' => 1234.567, 'test' => 'test', '2024-01-20T09:15:00+00:00' => new \\DateTimeImmutable('2024-01-20T09:15:00+00:00'), 'AbstractSchemaInnerParseEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\Schema\\AbstractSchemaInnerParseEnum::from('H')]",
            $schema->parse([
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
                'AbstractSchemaInnerParseEnum::Hearts' => AbstractSchemaInnerParseEnum::Hearts,
            ])
        );

        self::assertSame(
            "(object) ['null' => null, 'true' => true, 'false' => 'false', 1 => 1, '1.234' => 1.234, '1.2e3' => 1200.0, '7E-10' => 7.0E-10, '1_234.567' => 1234.567, 'test' => 'test', '2024-01-20T09:15:00+00:00' => new \\DateTimeImmutable('2024-01-20T09:15:00+00:00'), 'AbstractSchemaInnerParseEnum::Hearts' => \\Chubbyphp\\Tests\\Parsing\\Unit\\Schema\\AbstractSchemaInnerParseEnum::from('H')]",
            $schema->parse((object) [
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
                'AbstractSchemaInnerParseEnum::Hearts' => AbstractSchemaInnerParseEnum::Hearts,
            ])
        );
    }
}
