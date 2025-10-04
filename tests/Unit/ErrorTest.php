<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\Error;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Error
 *
 * @internal
 */
final class ErrorTest extends TestCase
{
    public function testToStringAndJsonSerialize(): void
    {
        $code = 'some.error';
        $template = '{{bool}},{{int}},{{float}},{{string}},{{array}},{{date}}';
        $variables = [
            'bool' => true,
            'int' => 1337,
            'float' => 4.2,
            'string' => 'test',
            'array' => ['bool' => true, 'int' => 1337, 'float' => 4.2, 'string' => 'test'],
            'date' => new \DateTimeImmutable('2025-10-07T00:00:00Z'),
        ];

        $error = new Error($code, $template, $variables);

        self::assertSame($code, $error->code);
        self::assertSame($template, $error->template);
        self::assertSame($variables, $error->variables);

        self::assertSame(
            'true,1337,4.2,"test",{"bool":true,"int":1337,"float":4.2,"string":"test"},{"date":"2025-10-07 00:00:00.000000","timezone_type":2,"timezone":"Z"}',
            (string) $error
        );

        self::assertSame([
            'code' => 'some.error',
            'template' => '{{bool}},{{int}},{{float}},{{string}},{{array}},{{date}}',
            'variables' => json_decode(json_encode($variables), true),
        ], $error->jsonSerialize());
    }

    public function testToStringWithError(): void
    {
        $resource = fopen('php://memory', 'r');

        $code = 'some.error';
        $template = '{{resource}}';
        $variables = [
            'resource' => $resource,
        ];

        $error = new Error($code, $template, $variables);

        self::assertSame($code, $error->code);
        self::assertSame($template, $error->template);
        self::assertSame($variables, $error->variables);

        self::assertSame('<cannot_be_encoded>', (string) $error);
    }
}
