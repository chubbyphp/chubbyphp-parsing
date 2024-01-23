<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

/**
 * @covers \Chubbyphp\Parsing\ParserErrorException
 *
 * @internal
 */
final class ParserErrorExceptionTest extends AbstractTestCase
{
    public function testConstruct(): void
    {
        $exception = new ParserErrorException();

        self::assertSame([], $exception->getErrors());
        self::assertFalse($exception->hasError());
    }

    public function testConstructWithError(): void
    {
        $error = new Error('code', 'template', ['key' => 'value']);

        $exception = new ParserErrorException($error);

        self::assertSame([$error], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testConstructWithErrorAndKey(): void
    {
        $error = new Error('code', 'template', ['key' => 'value']);

        $exception = new ParserErrorException($error, 'field');

        self::assertSame(['field' => [$error]], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testToString(): void
    {
        self::assertSame(ParserErrorException::class, (string) new ParserErrorException());
    }

    public function testAddParserErrorException(): void
    {
        $error = new Error('code', 'template', ['key' => 'value']);

        $exception = new ParserErrorException();
        $exception->addParserErrorException(new ParserErrorException($error));

        self::assertSame([$error], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testAddParserErrorExceptionAndKey(): void
    {
        $error = new Error('code', 'template', ['key' => 'value']);

        $exception = new ParserErrorException();
        $exception->addParserErrorException(new ParserErrorException($error), 'field');

        self::assertSame(['field' => [$error]], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testAddError(): void
    {
        $error = new Error('code', 'template', ['key' => 'value']);

        $exception = new ParserErrorException();
        $exception->addError($error);

        self::assertSame([$error], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testAddErrorAndKey(): void
    {
        $error = new Error('code', 'template', ['key' => 'value']);

        $exception = new ParserErrorException();
        $exception->addError($error, 'field');

        self::assertSame(['field' => [$error]], $exception->getErrors());
        self::assertTrue($exception->hasError());
    }

    public function testNested(): void
    {
        $i = 1;

        $exception = (new ParserErrorException())
            ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field1')
            ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field2')
            ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field3')
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field1')
                    ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field2')
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field1')
                            ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field2'),
                        'field3'
                    ),
                'field4'
            )
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field1')
                    ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field2')
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field1')
                            ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field2'),
                        'field3'
                    ),
                'field4'
            )
            ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']), 'field1')
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']))
                    ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']))
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('error: '.$i++, 'template', ['key' => 'value']))
                    ),
                'field5'
            )
        ;

        self::assertSame([
            'field1' => [
                [
                    'code' => 'error: 1',
                    'template' => 'template',
                    'variables' => [
                        'key' => 'value',
                    ],
                ],
                [
                    'code' => 'error: 12',
                    'template' => 'template',
                    'variables' => [
                        'key' => 'value',
                    ],
                ],
            ],
            'field2' => [
                [
                    'code' => 'error: 2',
                    'template' => 'template',
                    'variables' => [
                        'key' => 'value',
                    ],
                ],
            ],
            'field3' => [
                [
                    'code' => 'error: 3',
                    'template' => 'template',
                    'variables' => [
                        'key' => 'value',
                    ],
                ],
            ],
            'field4' => [
                'field1' => [
                    [
                        'code' => 'error: 4',
                        'template' => 'template',
                        'variables' => [
                            'key' => 'value',
                        ],
                    ],
                    [
                        'code' => 'error: 8',
                        'template' => 'template',
                        'variables' => [
                            'key' => 'value',
                        ],
                    ],
                ],
                'field2' => [
                    [
                        'code' => 'error: 5',
                        'template' => 'template',
                        'variables' => [
                            'key' => 'value',
                        ],
                    ],
                    [
                        'code' => 'error: 9',
                        'template' => 'template',
                        'variables' => [
                            'key' => 'value',
                        ],
                    ],
                ],
                'field3' => [
                    'field1' => [
                        [
                            'code' => 'error: 6',
                            'template' => 'template',
                            'variables' => [
                                'key' => 'value',
                            ],
                        ],
                        [
                            'code' => 'error: 10',
                            'template' => 'template',
                            'variables' => [
                                'key' => 'value',
                            ],
                        ],
                    ],
                    'field2' => [
                        [
                            'code' => 'error: 7',
                            'template' => 'template',
                            'variables' => [
                                'key' => 'value',
                            ],
                        ],
                        [
                            'code' => 'error: 11',
                            'template' => 'template',
                            'variables' => [
                                'key' => 'value',
                            ],
                        ],
                    ],
                ],
            ],
            'field5' => [
                [
                    'code' => 'error: 13',
                    'template' => 'template',
                    'variables' => [
                        'key' => 'value',
                    ],
                ],
                [
                    'code' => 'error: 14',
                    'template' => 'template',
                    'variables' => [
                        'key' => 'value',
                    ],
                ],
                [
                    'code' => 'error: 15',
                    'template' => 'template',
                    'variables' => [
                        'key' => 'value',
                    ],
                ],
            ],
        ], $this->errorsToSimpleArray($exception->getErrors()));

        self::assertTrue($exception->hasError());
    }
}
