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
        $i = 0;

        $exception = (new ParserErrorException())
            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field2')
            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field3')
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                    ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field2')
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field2'),
                        'field3'
                    ),
                'field4'
            )
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                    ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field2')
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field2'),
                        'field3'
                    ),
                'field4'
            )
            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]))
                    ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]))
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]))
                    ),
                'field5'
            )
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addParserErrorException(
                                        (new ParserErrorException())
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field2')
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field3'),
                                    ),
                                0
                            )
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addParserErrorException(
                                        (new ParserErrorException())
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field2')
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field3'),
                                    ),
                                1
                            )
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addParserErrorException(
                                        (new ParserErrorException())
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field1')
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field2')
                                            ->addError(new Error('error: '.++$i, 'value: {{value}}', ['value' => $i]), 'field3'),
                                    ),
                                2
                            )
                    ),
                'field6'
            )
        ;

        self::assertSame([
            'field1' => [
                [
                    'code' => 'error: 1',
                    'template' => 'value: {{value}}',
                    'variables' => [
                        'value' => 1,
                    ],
                ],
                [
                    'code' => 'error: 12',
                    'template' => 'value: {{value}}',
                    'variables' => [
                        'value' => 12,
                    ],
                ],
            ],
            'field2' => [
                [
                    'code' => 'error: 2',
                    'template' => 'value: {{value}}',
                    'variables' => [
                        'value' => 2,
                    ],
                ],
            ],
            'field3' => [
                [
                    'code' => 'error: 3',
                    'template' => 'value: {{value}}',
                    'variables' => [
                        'value' => 3,
                    ],
                ],
            ],
            'field4' => [
                'field1' => [
                    [
                        'code' => 'error: 4',
                        'template' => 'value: {{value}}',
                        'variables' => [
                            'value' => 4,
                        ],
                    ],
                    [
                        'code' => 'error: 8',
                        'template' => 'value: {{value}}',
                        'variables' => [
                            'value' => 8,
                        ],
                    ],
                ],
                'field2' => [
                    [
                        'code' => 'error: 5',
                        'template' => 'value: {{value}}',
                        'variables' => [
                            'value' => 5,
                        ],
                    ],
                    [
                        'code' => 'error: 9',
                        'template' => 'value: {{value}}',
                        'variables' => [
                            'value' => 9,
                        ],
                    ],
                ],
                'field3' => [
                    'field1' => [
                        [
                            'code' => 'error: 6',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 6,
                            ],
                        ],
                        [
                            'code' => 'error: 10',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 10,
                            ],
                        ],
                    ],
                    'field2' => [
                        [
                            'code' => 'error: 7',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 7,
                            ],
                        ],
                        [
                            'code' => 'error: 11',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 11,
                            ],
                        ],
                    ],
                ],
            ],
            'field5' => [
                [
                    'code' => 'error: 13',
                    'template' => 'value: {{value}}',
                    'variables' => [
                        'value' => 13,
                    ],
                ],
                [
                    'code' => 'error: 14',
                    'template' => 'value: {{value}}',
                    'variables' => [
                        'value' => 14,
                    ],
                ],
                [
                    'code' => 'error: 15',
                    'template' => 'value: {{value}}',
                    'variables' => [
                        'value' => 15,
                    ],
                ],
            ],
            'field6' => [
                [
                    'field1' => [
                        [
                            'code' => 'error: 16',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 16,
                            ],
                        ],
                        [
                            'code' => 'error: 17',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 17,
                            ],
                        ],
                    ],
                    'field2' => [
                        [
                            'code' => 'error: 18',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 18,
                            ],
                        ],
                    ],
                    'field3' => [
                        [
                            'code' => 'error: 19',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 19,
                            ],
                        ],
                    ],
                ],
                [
                    'field1' => [
                        [
                            'code' => 'error: 20',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 20,
                            ],
                        ],
                        [
                            'code' => 'error: 21',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 21,
                            ],
                        ],
                    ],
                    'field2' => [
                        [
                            'code' => 'error: 22',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 22,
                            ],
                        ],
                    ],
                    'field3' => [
                        [
                            'code' => 'error: 23',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 23,
                            ],
                        ],
                    ],
                ],
                [
                    'field1' => [
                        [
                            'code' => 'error: 24',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 24,
                            ],
                        ],
                        [
                            'code' => 'error: 25',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 25,
                            ],
                        ],
                    ],
                    'field2' => [
                        [
                            'code' => 'error: 26',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 26,
                            ],
                        ],
                    ],
                    'field3' => [
                        [
                            'code' => 'error: 27',
                            'template' => 'value: {{value}}',
                            'variables' => [
                                'value' => 27,
                            ],
                        ],
                    ],
                ],
            ],
        ], $this->errorsToSimpleArray($exception->getErrors()));

        self::assertTrue($exception->hasError());
    }
}
