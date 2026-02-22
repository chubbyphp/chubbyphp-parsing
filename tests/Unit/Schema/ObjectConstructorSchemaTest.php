<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\FloatSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\ObjectConstructorSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

final class ObjectConstructorDemo implements \JsonSerializable
{
    public function __construct(
        public readonly string $field1,
        public readonly int $field2,
        public readonly ?float $field3 = null,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'field1' => $this->field1,
            'field2' => $this->field2,
            'field3' => $this->field3,
        ];
    }
}

final class ObjectConstructorThrowingTypeErrorDemo
{
    public function __construct(
        public readonly string $field1,
    ) {
        throw new \TypeError('some unrelated type error');
    }
}

/**
 * @covers \Chubbyphp\Parsing\Schema\ObjectConstructorSchema
 *
 * @internal
 */
final class ObjectConstructorSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new ObjectConstructorSchema(['field1' => new StringSchema(), 'field2' => new IntSchema(), 'field3' => new FloatSchema()], ObjectConstructorDemo::class);

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default([]));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (\stdClass $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (\stdClass $output, ErrorsException $e) => $output));

        self::assertNotSame($schema, $schema->strict());
        self::assertNotSame($schema, $schema->optional([]));
    }

    public function testConstructWithClassname(): void
    {
        try {
            new ObjectConstructorSchema([], 'UnknownClass');

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Class "UnknownClass" does not exist or cannot be used for reflection',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testConstructWithClassNotHavingAConstructor(): void
    {
        try {
            new ObjectConstructorSchema([], \stdClass::class);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Class "'.\stdClass::class.'" does not have a __construct method',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testConstructWithMissingFieldSchema(): void
    {
        try {
            new ObjectConstructorSchema([], ObjectConstructorDemo::class);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Missing fieldToSchema for "'.ObjectConstructorDemo::class.'" __construct parameters: "field1", "field2"',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testConstructWithAdditionalFieldSchema(): void
    {
        try {
            new ObjectConstructorSchema([
                'field1' => new StringSchema(),
                'field2' => new IntSchema(),
                'field3' => new FloatSchema(),
                'field4' => new FloatSchema(),
            ], ObjectConstructorDemo::class)->optional(['field3']);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(
                'Additional fieldToSchema for "'.ObjectConstructorDemo::class.'" __construct parameters: "field4"',
                $invalidArgumentException->getMessage()
            );
        }
    }

    public function testSuccessWithAllParameters(): void
    {
        $input = ['field1' => 'test', 'field2' => 5, 'field3' => 3.14159];

        $schema = new ObjectConstructorSchema([
            'field1' => new StringSchema(),
            'field2' => new IntSchema(),
            'field3' => new FloatSchema(),
        ], ObjectConstructorDemo::class)->optional(['field3']);

        /** @var ObjectConstructorDemo $object */
        $object = $schema->parse($input);

        self::assertInstanceOf(ObjectConstructorDemo::class, $object);

        self::assertSame($input, $object->jsonSerialize());
    }

    public function testSuccessWithAllParametersOptionalConsidered(): void
    {
        $input = ['field1' => 'test', 'field2' => 5];

        $schema = new ObjectConstructorSchema([
            'field1' => new StringSchema(),
            'field2' => new IntSchema(),
            'field3' => new FloatSchema(),
        ], ObjectConstructorDemo::class)->optional(['field3']);

        /** @var ObjectConstructorDemo $object */
        $object = $schema->parse($input);

        self::assertInstanceOf(ObjectConstructorDemo::class, $object);

        self::assertSame([...$input, 'field3' => null], $object->jsonSerialize());
    }

    public function testSuccessWithRequiredParameters(): void
    {
        $input = ['field1' => 'test', 'field2' => 5];

        $schema = new ObjectConstructorSchema([
            'field1' => new StringSchema(),
            'field2' => new IntSchema(),
        ], ObjectConstructorDemo::class)->optional(['field3']);

        /** @var ObjectConstructorDemo $object */
        $object = $schema->parse($input);

        self::assertInstanceOf(ObjectConstructorDemo::class, $object);

        self::assertSame([...$input, 'field3' => null], $object->jsonSerialize());
    }

    public function testFailedWithInvalidValue(): void
    {
        $input = ['field1' => 'test', 'field2' => 5, 'field3' => 'test'];

        $schema = new ObjectConstructorSchema([
            'field1' => new StringSchema(),
            'field2' => new IntSchema(),
            'field3' => new FloatSchema(),
        ], ObjectConstructorDemo::class)->optional(['field3']);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => 'field3',
                    'error' => [
                        'code' => 'float.type',
                        'template' => 'Type should be "float", {{given}} given',
                        'variables' => [
                            'given' => 'string',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testFailedWithInvalidValueNotCatchedByFieldSchema(): void
    {
        $input = ['field1' => 'test', 'field2' => 5, 'field3' => 'test'];

        $schema = new ObjectConstructorSchema([
            'field1' => new StringSchema(),
            'field2' => new IntSchema(),
            'field3' => new StringSchema(),
        ], ObjectConstructorDemo::class)->optional(['field3']);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'object.parameterType',
                        'template' => 'Parameter {{index}} {{name}} should be of {{type}}, {{given}} given',
                        'variables' => [
                            'index' => '3',
                            'name' => '$field3',
                            'type' => '?float',
                            'given' => 'string',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testFailedWithUnknownException(): void
    {
        $exception = new \Exception('unknown');

        $input = ['field1' => 'test', 'field2' => 5, 'field3' => 3.14159];

        $schema = new ObjectConstructorSchema([
            'field1' => new StringSchema(),
            'field2' => new IntSchema(),
            'field3' => new FloatSchema()->postParse(static fn () => throw $exception),
        ], ObjectConstructorDemo::class)->optional(['field3']);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (\Exception $e) {
            self::assertSame($exception, $e);
        }
    }

    public function testFailedWithTypeErrorNotMatchingPattern(): void
    {
        $input = ['field1' => 'test'];

        $schema = new ObjectConstructorSchema([
            'field1' => new StringSchema(),
        ], ObjectConstructorThrowingTypeErrorDemo::class);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (\TypeError $e) {
            self::assertSame('some unrelated type error', $e->getMessage());
        }
    }
}
