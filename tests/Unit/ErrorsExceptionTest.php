<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\ErrorsException
 *
 * @internal
 */
final class ErrorsExceptionTest extends TestCase
{
    public function testWithErrors(): void
    {
        $error = new Error('code', 'template', []);
        $errors = (new Errors())->add($error, 'path');
        $exception = new ErrorsException($errors);

        self::assertSame($errors, $exception->errors);
        self::assertSame('path: template', $exception->getMessage());
    }

    public function testWithError(): void
    {
        $error = new Error('code', 'template', []);
        $exception = new ErrorsException($error);

        self::assertSame('template', $exception->getMessage());
    }
}
