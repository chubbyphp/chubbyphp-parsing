<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\AbstractSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 *
 * @internal
 */
final class AbstractSchemaTest extends TestCase
{
    public function testNullable(): void
    {
        $schema = new class extends AbstractSchema {
            public function parse(mixed $input): mixed
            {
                if (null === $input && $this->nullable) {
                    return null;
                }

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
        $schema = new class extends AbstractSchema {
            public function parse(mixed $input): mixed
            {
                return $this->dispatchPreParses($input);
            }
        };

        self::assertNotSame($schema, $schema->default('default'));
        self::assertSame('default', $schema->default('default')->parse(null));
        self::assertSame('value', $schema->default('default')->parse('value'));
    }

    public function testPreParse(): void
    {
        $schema = new class extends AbstractSchema {
            public function parse(mixed $input): mixed
            {
                return $this->dispatchPreParses($input);
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
        $schema = new class extends AbstractSchema {
            public function parse(mixed $input): mixed
            {
                return $this->dispatchPostParses($input);
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
        $successSchema = new class extends AbstractSchema {
            public function parse(mixed $input): mixed
            {
                return $input;
            }
        };

        $result = $successSchema->safeParse('value');
        self::assertTrue($result->success);
        self::assertSame('value', $result->data);

        $failSchema = new class extends AbstractSchema {
            public function parse(mixed $input): mixed
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
        $schema = new class extends AbstractSchema {
            public function parse(mixed $input): mixed
            {
                $e = new ErrorsException(new Error('error', 'Error', []));
                if (null !== $this->catch) {
                    return ($this->catch)($input, $e);
                }

                throw $e;
            }
        };

        self::assertNotSame($schema, $schema->catch(static fn ($i, $e) => $i));
        self::assertSame('caught', $schema->catch(static fn ($i, $e) => 'caught')->parse('value'));
    }

    public function testGetDataType(): void
    {
        $schema = new class extends AbstractSchema {
            public function parse(mixed $input): mixed
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
}
