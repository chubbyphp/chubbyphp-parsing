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
        $i = 1;

        $exception = (new ParserErrorException())
            ->addError('error: '.$i++, 'field1')
            ->addError('error: '.$i++, 'field2')
            ->addError('error: '.$i++, 'field3')
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError('error: '.$i++, 'field1')
                    ->addError('error: '.$i++, 'field2')
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError('error: '.$i++, 'field1')
                            ->addError('error: '.$i++, 'field2'),
                        'field3'
                    ),
                'field4'
            )
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError('error: '.$i++, 'field1')
                    ->addError('error: '.$i++, 'field2')
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError('error: '.$i++, 'field1')
                            ->addError('error: '.$i++, 'field2'),
                        'field3'
                    ),
                'field4'
            )
            ->addError('error: '.$i++, 'field1')
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError('error: '.$i++)
                    ->addError('error: '.$i++)
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError('error: '.$i++)
                    ),
                'field5'
            )
        ;

        self::assertEquals([
            'field1' => ['error: 1', 'error: 12'],
            'field2' => ['error: 2'],
            'field3' => ['error: 3'],
            'field4' => [
                'field1' => ['error: 4', 'error: 8'],
                'field2' => ['error: 5', 'error: 9'],
                'field3' => [
                    'field1' => ['error: 6', 'error: 10'],
                    'field2' => ['error: 7', 'error: 11'],
                ],
            ],
            'field5' => ['error: 13', 'error: 14', 'error: 15'],
        ], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }
}
