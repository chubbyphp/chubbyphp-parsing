<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\ParserErrorException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\ParserErrorException
 *
 * @internal
 */
final class ParserErrorExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new ParserErrorException();

        self::assertEquals([], $exception->getErrors());
        self::assertFalse($exception->hasError());
    }

    public function testConstructWithError(): void
    {
        $exception = new ParserErrorException('This is a error message');

        self::assertEquals(['This is a error message'], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testConstructWithErrorAndKey(): void
    {
        $exception = new ParserErrorException('This is a error message', 'field');

        self::assertEquals(['field' => ['This is a error message']], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testAddParserErrorException(): void
    {
        $exception = new ParserErrorException();
        $exception->addParserErrorException(new ParserErrorException('This is a error message'));

        self::assertEquals(['This is a error message'], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testAddParserErrorExceptionAndKey(): void
    {
        $exception = new ParserErrorException();
        $exception->addParserErrorException(new ParserErrorException('This is a error message'), 'field');

        self::assertEquals(['field' => ['This is a error message']], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testAddError(): void
    {
        $exception = new ParserErrorException();
        $exception->addError('This is a error message');

        self::assertEquals(['This is a error message'], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testAddErrorAndKey(): void
    {
        $exception = new ParserErrorException();
        $exception->addError('This is a error message', 'field');

        self::assertEquals(['field' => ['This is a error message']], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testNested(): void
    {
        $exception = (new ParserErrorException())
            ->addError('This is a error message', 'field1')
            ->addError('This is a error message', 'field2')
            ->addError('This is a error message', 'field3')
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError('This is a error message', 'field1')
                    ->addError('This is a error message', 'field2')
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError('This is a error message', 'field1')
                            ->addError('This is a error message', 'field2'),
                        'field3'
                    ),
                'field4'
            )
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError('This is a error message', 'field1')
                    ->addError('This is a error message', 'field2')
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError('This is a error message', 'field1')
                            ->addError('This is a error message', 'field2'),
                        'field3'
                    ),
                'field4'
            )
        ;

        self::assertEquals([
            'field1' => ['This is a error message'],
            'field2' => ['This is a error message'],
            'field3' => ['This is a error message'],
            'field4' => [
                'field1' => ['This is a error message', 'This is a error message'],
                'field2' => ['This is a error message', 'This is a error message'],
                'field3' => [
                    'field1' => ['This is a error message', 'This is a error message'],
                    'field2' => ['This is a error message', 'This is a error message'],
                ],
            ],
        ], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }
}
