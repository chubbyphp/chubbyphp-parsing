<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\RecordSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\RecordSchema
 *
 * @internal
 */
final class RecordSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new RecordSchema(new StringSchema());

        self::assertNotSame($schema, $schema->transform(static fn (\stdClass $output) => $output));
        self::assertNotSame($schema, $schema->default([]));
        self::assertNotSame($schema, $schema->catch(static fn (\stdClass $output, ParserErrorException $e) => $output));
        self::assertNotSame($schema, $schema->nullable());
    }

    public function testParseSuccess(): void
    {
        $input = ['field1' => 'value1', 'field2' => 'value2'];

        $schema = new RecordSchema(new StringSchema());

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame($input, (array) $output);
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = ['field1' => 'value1', 'field2' => 'value2'];

        $schema = (new RecordSchema(new StringSchema()))->default($input);

        $output = $schema->parse(null);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame($input, (array) $output);
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new RecordSchema(new StringSchema()))->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new RecordSchema(new StringSchema());

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'record.type',
                    'template' => 'Type should be "array", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseFailedWithFailedFields(): void
    {
        $input = ['field1' => 'value1', 'field2' => 42];

        $schema = new RecordSchema(new StringSchema());

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                'field2' => [
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", "{{given}}" given',
                        'variables' => [
                            'given' => 'integer',
                        ],
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = ['field1' => 'value1'];

        $schema = (new RecordSchema(new StringSchema()))->transform(static function (\stdClass $output) {
            $output->field2 = 'value2';

            return $output;
        });

        self::assertSame([...$input, 'field2' => 'value2'], (array) $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new RecordSchema(new StringSchema()))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'record.type',
                        'template' => 'Type should be "array", "{{given}}" given',
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
        $input = ['field1' => 'value1', 'field2' => 'value2'];

        $schema = new RecordSchema(new StringSchema());

        self::assertSame($input, (array) $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new RecordSchema(new StringSchema());

        self::assertSame([
            [
                'code' => 'record.type',
                'template' => 'Type should be "array", "{{given}}" given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }
}
