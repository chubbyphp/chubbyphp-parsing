<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\Schema\LazySchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\LazySchema
 *
 * @internal
 */
final class LazySchemaTest extends TestCase
{
    public function testParseSuccess(): void
    {
        $input = [
            'name' => 'name1',
            'child' => [
                'name' => 'name2',
                'child' => [
                    'name' => 'name3',
                    'child' => null,
                ],
            ],
        ];

        $schema = new LazySchema(static function () use (&$schema) {
            return (new ObjectSchema([
                'name' => new StringSchema(),
                'child' => $schema,
            ]))->nullable();
        });

        $output = $schema->parse($input);

        self::assertInstanceOf(\stdClass::class, $output);

        self::assertSame($input, json_decode(json_encode($output), true));
    }

    public function testSafeParseSuccess(): void
    {
        $input = [
            'name' => 'name1',
            'child' => [
                'name' => 'name2',
                'child' => [
                    'name' => 'name3',
                    'child' => null,
                ],
            ],
        ];

        $schema = new LazySchema(static function () use (&$schema) {
            return (new ObjectSchema([
                'name' => new StringSchema(),
                'child' => $schema,
            ]))->nullable();
        });

        $result = $schema->safeParse($input);

        self::assertTrue($result->success);

        self::assertInstanceOf(\stdClass::class, $result->data);

        self::assertSame($input, json_decode(json_encode($result->data), true));
    }

    public function testNullableTrue(): void
    {
        $schema = new LazySchema(static function () use (&$schema) {
            return (new ObjectSchema([
                'name' => new StringSchema(),
                'child' => $schema,
            ]))->nullable();
        });

        try {
            $schema->nullable();

            throw new \Exception('code should not be reached');
        } catch (\BadMethodCallException $e) {
            self::assertSame('LazySchema does not support any modification, "nullable" called with true.', $e->getMessage());
        }
    }

    public function testNullableFalse(): void
    {
        $schema = new LazySchema(static function () use (&$schema) {
            return (new ObjectSchema([
                'name' => new StringSchema(),
                'child' => $schema,
            ]))->nullable();
        });

        try {
            $schema->nullable(false);

            throw new \Exception('code should not be reached');
        } catch (\BadMethodCallException $e) {
            self::assertSame('LazySchema does not support any modification, "nullable" called with false.', $e->getMessage());
        }
    }

    public function testPreParse(): void
    {
        $schema = new LazySchema(static function () use (&$schema) {
            return (new ObjectSchema([
                'name' => new StringSchema(),
                'child' => $schema,
            ]))->nullable();
        });

        try {
            $schema->preParse(static function (): void {});

            throw new \Exception('code should not be reached');
        } catch (\BadMethodCallException $e) {
            self::assertSame('LazySchema does not support any modification, "preParse" called.', $e->getMessage());
        }
    }

    public function testPostParse(): void
    {
        $schema = new LazySchema(static function () use (&$schema) {
            return (new ObjectSchema([
                'name' => new StringSchema(),
                'child' => $schema,
            ]))->nullable();
        });

        try {
            $schema->postParse(static function (): void {});

            throw new \Exception('code should not be reached');
        } catch (\BadMethodCallException $e) {
            self::assertSame('LazySchema does not support any modification, "postParse" called.', $e->getMessage());
        }
    }

    public function testCatch(): void
    {
        $schema = new LazySchema(static function () use (&$schema) {
            return (new ObjectSchema([
                'name' => new StringSchema(),
                'child' => $schema,
            ]))->nullable();
        });

        try {
            $schema->catch(static function (): void {});

            throw new \Exception('code should not be reached');
        } catch (\BadMethodCallException $e) {
            self::assertSame('LazySchema does not support any modification, "catch" called.', $e->getMessage());
        }
    }
}
