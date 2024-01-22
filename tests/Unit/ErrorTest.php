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
    public function testToString(): void
    {
        $code = 'some.error';
        $template = '{{bool}},{{int}},{{float}},{{string}},{{stringable}}';
        $variables = ['bool' => true, 'int' => 1337, 'float' => 4.2, 'string' => 'test', 'stringable' => new class() implements \Stringable {
            public function __toString(): string
            {
                return 'stringable';
            }
        }];

        $error = new Error($code, $template, $variables);

        self::assertSame($code, $error->code);
        self::assertSame($template, $error->template);
        self::assertSame($variables, $error->variables);

        self::assertSame('true,1337,4.2,"test","stringable"', (string) $error);
    }
}
