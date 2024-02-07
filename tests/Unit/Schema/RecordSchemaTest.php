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

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default([]));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (array $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (array $output, ParserErrorException $e) => $output));
    }

    public function testParseSuccess(): void
    {
        $input = ['field1' => 'value1', 'field2' => 'value2'];

        $schema = new RecordSchema(new StringSchema());

        $output = $schema->parse($input);

        self::assertSame($input, $output);
    }

    public function testParseSuccessWithStdClass(): void
    {
        $input = new \stdClass();
        $input->field1 = 'value1';
        $input->field2 = 'value2';

        $schema = new RecordSchema(new StringSchema());

        $output = $schema->parse($input);

        self::assertSame((array) $input, $output);
    }

    public function testParseSuccessWithIterator(): void
    {
        $input = new \ArrayIterator(['field1' => 'value1', 'field2' => 'value2']);

        $schema = new RecordSchema(new StringSchema());

        $output = $schema->parse($input);

        self::assertSame((array) $input, $output);
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = ['field1' => 'value1', 'field2' => 'value1'];
        $input2 = ['field1' => 'value1', 'field2' => 'value2'];

        $schema = (new RecordSchema(new StringSchema()))->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
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
                    'template' => 'Type should be "array|\stdClass|\Traversable", "{{given}}" given',
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

    public function testParseSuccessWithPreParse(): void
    {
        $input = ['field1' => 'value1'];

        $schema = (new RecordSchema(new StringSchema()))->preParse(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = ['field1' => 'value1'];

        $schema = (new RecordSchema(new StringSchema()))->postParse(static function (array $output) {
            $output['field2'] = 'value2';

            return $output;
        });

        self::assertSame([...$input, 'field2' => 'value2'], $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new RecordSchema(new StringSchema()))
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'record.type',
                        'template' => 'Type should be "array|\stdClass|\Traversable", "{{given}}" given',
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

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new RecordSchema(new StringSchema());

        self::assertSame([
            [
                'code' => 'record.type',
                'template' => 'Type should be "array|\stdClass|\Traversable", "{{given}}" given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }
}
