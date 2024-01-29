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

    public function testNestedGetErrors(): void
    {
        $exception = $this->getNestedParserErrorException();

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

    public function testNestedGetApiProblemErrorMessages(): void
    {
        $exception = $this->getNestedParserErrorException();

        self::assertSame([
            [
                'name' => 'field1',
                'reason' => 'value: 1',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 1,
                ],
            ],
            [
                'name' => 'field1',
                'reason' => 'value: 12',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 12,
                ],
            ],
            [
                'name' => 'field2',
                'reason' => 'value: 2',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 2,
                ],
            ],
            [
                'name' => 'field3',
                'reason' => 'value: 3',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 3,
                ],
            ],
            [
                'name' => 'field4[field1]',
                'reason' => 'value: 4',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 4,
                ],
            ],
            [
                'name' => 'field4[field1]',
                'reason' => 'value: 8',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 8,
                ],
            ],
            [
                'name' => 'field4[field2]',
                'reason' => 'value: 5',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 5,
                ],
            ],
            [
                'name' => 'field4[field2]',
                'reason' => 'value: 9',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 9,
                ],
            ],
            [
                'name' => 'field4[field3][field1]',
                'reason' => 'value: 6',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 6,
                ],
            ],
            [
                'name' => 'field4[field3][field1]',
                'reason' => 'value: 10',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 10,
                ],
            ],
            [
                'name' => 'field4[field3][field2]',
                'reason' => 'value: 7',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 7,
                ],
            ],
            [
                'name' => 'field4[field3][field2]',
                'reason' => 'value: 11',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 11,
                ],
            ],
            [
                'name' => 'field5',
                'reason' => 'value: 13',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 13,
                ],
            ],
            [
                'name' => 'field5',
                'reason' => 'value: 14',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 14,
                ],
            ],
            [
                'name' => 'field5',
                'reason' => 'value: 15',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 15,
                ],
            ],
            [
                'name' => 'field6[0][field1]',
                'reason' => 'value: 16',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 16,
                ],
            ],
            [
                'name' => 'field6[0][field1]',
                'reason' => 'value: 17',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 17,
                ],
            ],
            [
                'name' => 'field6[0][field2]',
                'reason' => 'value: 18',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 18,
                ],
            ],
            [
                'name' => 'field6[0][field3]',
                'reason' => 'value: 19',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 19,
                ],
            ],
            [
                'name' => 'field6[1][field1]',
                'reason' => 'value: 20',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 20,
                ],
            ],
            [
                'name' => 'field6[1][field1]',
                'reason' => 'value: 21',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 21,
                ],
            ],
            [
                'name' => 'field6[1][field2]',
                'reason' => 'value: 22',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 22,
                ],
            ],
            [
                'name' => 'field6[1][field3]',
                'reason' => 'value: 23',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 23,
                ],
            ],
            [
                'name' => 'field6[2][field1]',
                'reason' => 'value: 24',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 24,
                ],
            ],
            [
                'name' => 'field6[2][field1]',
                'reason' => 'value: 25',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 25,
                ],
            ],
            [
                'name' => 'field6[2][field2]',
                'reason' => 'value: 26',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 26,
                ],
            ],
            [
                'name' => 'field6[2][field3]',
                'reason' => 'value: 27',
                'details' => [
                    '_template' => 'value: {{value}}',
                    'value' => 27,
                ],
            ],
        ], $this->errorsToSimpleArray($exception->getApiProblemErrorMessages()));

        self::assertTrue($exception->hasError());
    }

    private function getNestedParserErrorException(): ParserErrorException
    {
        $i = 0;

        return (new ParserErrorException())
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
    }
}
