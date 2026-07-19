<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\RecordSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

final class RecordDemo implements \JsonSerializable
{
    public string $field1;
    public string $field2;

    public function jsonSerialize(): array
    {
        return [
            'field1' => $this->field1,
            'field2' => $this->field2,
        ];
    }
}

/**
 * @covers \Chubbyphp\Parsing\Schema\RecordSchema
 *
 * @internal
 */
final class RecordSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new RecordSchema(new StringSchema());

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default([]));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (array $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (array $output, ErrorsException $e) => $output));

        self::assertNotSame($schema, $schema->minProperties(1));
        self::assertNotSame($schema, $schema->maxProperties(1));

        self::assertNotSame($schema, $schema->propertyNames(new StringSchema()));
    }

    public function testParseSuccess(): void
    {
        $input = ['field1' => 'value1', 'field2' => 'value2'];

        $schema = new RecordSchema(new StringSchema());

        $output = $schema->parse($input);

        self::assertSame($input, $output);
    }

    public function testParseSuccessWithStdClassInput(): void
    {
        $input = new \stdClass();
        $input->field1 = 'value1';
        $input->field2 = 'value2';

        $schema = new RecordSchema(new StringSchema());

        $output = $schema->parse($input);

        self::assertSame((array) $input, $output);
    }

    public function testParseSuccessWithIteratorInput(): void
    {
        $input = new \ArrayIterator(['field1' => 'value1', 'field2' => 'value2']);

        $schema = new RecordSchema(new StringSchema());

        $output = $schema->parse($input);

        self::assertSame((array) $input, $output);
    }

    public function testParseSuccessWithJsonSerialzableObject(): void
    {
        $input = new RecordDemo();
        $input->field1 = 'value1';
        $input->field2 = 'value2';

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
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'record.type',
                        'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithFailedFields(): void
    {
        $input = ['field1' => 'value1', 'field2' => 42];

        $schema = new RecordSchema(new StringSchema());

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => 'field2',
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
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertNull($input);

                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'record.type',
                            'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
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
        $input = ['field1' => 'value1', 'field2' => 'value2'];

        $schema = new RecordSchema(new StringSchema());

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new RecordSchema(new StringSchema());

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'record.type',
                    'template' => 'Type should be "array|\stdClass|\Traversable", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
        ], $schema->safeParse(null)->exception->errors->jsonSerialize());
    }

    public function testParseSuccessWithMinAndMaxProperties(): void
    {
        $schema = (new RecordSchema(new StringSchema()))->minProperties(1)->maxProperties(2);

        self::assertSame(['field1' => 'test'], $schema->parse(['field1' => 'test']));
        self::assertSame(
            ['field1' => 'test', 'field2' => 'test'],
            $schema->parse(['field1' => 'test', 'field2' => 'test'])
        );
    }

    public function testParseFailedWithMinProperties(): void
    {
        $schema = (new RecordSchema(new StringSchema()))->minProperties(1);

        try {
            $schema->parse([]);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'record.minProperties',
                        'template' => 'Properties should be minimum {{minProperties}}, {{given}} given',
                        'variables' => [
                            'minProperties' => 1,
                            'given' => 0,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseFailedWithMaxProperties(): void
    {
        $schema = (new RecordSchema(new StringSchema()))->maxProperties(1);

        try {
            $schema->parse(['field1' => 'test', 'field2' => 'test']);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'record.maxProperties',
                        'template' => 'Properties should be maximum {{maxProperties}}, {{given}} given',
                        'variables' => [
                            'maxProperties' => 1,
                            'given' => 2,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPropertyNames(): void
    {
        $input = ['field1' => 'value1', 2 => 'value2'];

        $schema = (new RecordSchema(new StringSchema()))
            ->propertyNames((new StringSchema())->pattern('/^[a-z0-9]+$/'))
        ;

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseFailedWithPropertyNames(): void
    {
        $schema = (new RecordSchema(new StringSchema()))
            ->propertyNames((new StringSchema())->pattern('/^[a-z]+$/'))
        ;

        try {
            $schema->parse(['field' => 'value1', 'field2' => 1]);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => 'field2',
                    'error' => [
                        'code' => 'string.pattern',
                        'template' => '{{given}} does not pattern {{pattern}}',
                        'variables' => [
                            'pattern' => '/^[a-z]+$/',
                            'given' => 'field2',
                        ],
                    ],
                ],
                [
                    'path' => 'field2',
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
