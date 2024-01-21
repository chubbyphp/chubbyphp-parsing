<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Result;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Result
 *
 * @internal
 */
final class ResultTest extends TestCase
{
    public function testData(): void
    {
        $data = ['key' => 'value'];

        $result = new Result($data, null);

        self::assertSame($data, $result->data);
        self::assertNull($result->exception);
        self::assertTrue($result->success);
    }

    public function testException(): void
    {
        $exception = new ParserErrorException('test');

        $result = new Result(null, $exception);

        self::assertNull($result->data);
        self::assertSame($exception, $result->exception);
        self::assertFalse($result->success);
    }
}
