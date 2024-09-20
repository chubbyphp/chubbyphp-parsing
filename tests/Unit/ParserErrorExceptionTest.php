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
            'offset' => [
                [
                    'code' => 'int.type',
                    'template' => 'Type should be "int", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
                [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            'limit' => [
                [
                    'code' => 'int.type',
                    'template' => 'Type should be "int", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
                [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            'filters' => [
                'name' => [
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", {{given}} given',
                        'variables' => [
                            'given' => 'float',
                        ],
                    ],
                ],
            ],
            'sort' => [
                'name' => [
                    [
                        'code' => 'literal.type',
                        'template' => 'Type should be "bool|float|int|string", {{given}} given',
                        'variables' => [
                            'given' => 'float',
                        ],
                    ],
                    [
                        'code' => 'literal.type',
                        'template' => 'Type should be "bool|float|int|string", {{given}} given',
                        'variables' => [
                            'given' => 'float',
                        ],
                    ],
                ],
            ],
            'items' => [
                [
                    'id' => [
                        [
                            'code' => 'string.type',
                            'template' => 'Type should be "string", {{given}} given',
                            'variables' => [
                                'given' => 'float',
                            ],
                        ],
                    ],
                    'createdAt' => [
                        [
                            'code' => 'datetime.type',
                            'template' => 'Type should be "\DateTimeInterface", {{given}} given',
                            'variables' => [
                                'given' => 'float',
                            ],
                        ],
                    ],
                    'updatedAt' => [
                        [
                            'code' => 'datetime.type',
                            'template' => 'Type should be "\DateTimeInterface", {{given}} given',
                            'variables' => [
                                'given' => 'float',
                            ],
                        ],
                    ],
                    'name' => [
                        [
                            'code' => 'string.type',
                            'template' => 'Type should be "string", {{given}} given',
                            'variables' => [
                                'given' => 'float',
                            ],
                        ],
                    ],
                    'tag' => [
                        [
                            'code' => 'string.type',
                            'template' => 'Type should be "string", {{given}} given',
                            'variables' => [
                                'given' => 'float',
                            ],
                        ],
                    ],
                    'vaccinations' => [
                        0 => [
                            'name' => [
                                [
                                    'code' => 'string.type',
                                    'template' => 'Type should be "string", {{given}} given',
                                    'variables' => [
                                        'given' => 'float',
                                    ],
                                ],
                                [
                                    'code' => 'string.type',
                                    'template' => 'Type should be "string", {{given}} given',
                                    'variables' => [
                                        'given' => 'float',
                                    ],
                                ],
                            ],
                        ],
                        3 => [
                            'name' => [
                                [
                                    'code' => 'string.type',
                                    'template' => 'Type should be "string", {{given}} given',
                                    'variables' => [
                                        'given' => 'float',
                                    ],
                                ],
                                [
                                    'code' => 'string.type',
                                    'template' => 'Type should be "string", {{given}} given',
                                    'variables' => [
                                        'given' => 'float',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '_type' => [
                        [
                            'code' => 'literal.type',
                            'template' => 'Type should be "bool|float|int|string", {{given}} given',
                            'variables' => [
                                'given' => 'float',
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
                'name' => 'offset',
                'reason' => 'Type should be "int", "float" given',
                'details' => [
                    '_template' => 'Type should be "int", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'offset',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'limit',
                'reason' => 'Type should be "int", "float" given',
                'details' => [
                    '_template' => 'Type should be "int", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'limit',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'filters[name]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'sort[name]',
                'reason' => 'Type should be "bool|float|int|string", "float" given',
                'details' => [
                    '_template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'sort[name]',
                'reason' => 'Type should be "bool|float|int|string", "float" given',
                'details' => [
                    '_template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][id]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][createdAt]',
                'reason' => 'Type should be "\DateTimeInterface", "float" given',
                'details' => [
                    '_template' => 'Type should be "\DateTimeInterface", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][updatedAt]',
                'reason' => 'Type should be "\DateTimeInterface", "float" given',
                'details' => [
                    '_template' => 'Type should be "\DateTimeInterface", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][name]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][tag]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][vaccinations][0][name]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][vaccinations][0][name]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][vaccinations][3][name]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][vaccinations][3][name]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            [
                'name' => 'items[0][_type]',
                'reason' => 'Type should be "bool|float|int|string", "float" given',
                'details' => [
                    '_template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'given' => 'float',
                ],
            ],
        ], $this->errorsToSimpleArray($exception->getApiProblemErrorMessages()));

        self::assertTrue($exception->hasError());
    }

    public function testGetMessage(): void
    {
        $exception = $this->getNestedParserErrorException();

        $message = <<<'EOD'
            offset: Type should be "int", "float" given
            offset: Type should be "string", "float" given
            limit: Type should be "int", "float" given
            limit: Type should be "string", "float" given
            filters[name]: Type should be "string", "float" given
            sort[name]: Type should be "bool|float|int|string", "float" given
            sort[name]: Type should be "bool|float|int|string", "float" given
            items[0][id]: Type should be "string", "float" given
            items[0][createdAt]: Type should be "\DateTimeInterface", "float" given
            items[0][updatedAt]: Type should be "\DateTimeInterface", "float" given
            items[0][name]: Type should be "string", "float" given
            items[0][tag]: Type should be "string", "float" given
            items[0][vaccinations][0][name]: Type should be "string", "float" given
            items[0][vaccinations][0][name]: Type should be "string", "float" given
            items[0][vaccinations][3][name]: Type should be "string", "float" given
            items[0][vaccinations][3][name]: Type should be "string", "float" given
            items[0][_type]: Type should be "bool|float|int|string", "float" given
            EOD;

        self::assertSame($message, $exception->getMessage());

        $exception->addError(new Error('random', 'Make sure this error gets added as well', []), 'anotherField');

        $messageWithOneErrorMore = $message .= PHP_EOL.'anotherField: Make sure this error gets added as well';

        self::assertSame($messageWithOneErrorMore, $exception->getMessage());
    }

    private function getNestedParserErrorException(): ParserErrorException
    {
        return (new ParserErrorException())
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('int.type', 'Type should be "int", {{given}} given', ['given' => 'float']))
                    )
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']))
                    ),
                'offset'
            )
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('int.type', 'Type should be "int", {{given}} given', ['given' => 'float']))
                    )
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']))
                    ),
                'limit'
            )
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                        'name'
                    ),
                'filters'
            )
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addError(new Error('literal.type', 'Type should be "bool|float|int|string", {{given}} given', ['given' => 'float']))
                            )
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addError(new Error('literal.type', 'Type should be "bool|float|int|string", {{given}} given', ['given' => 'float']))
                            ),
                        'name'
                    ),
                'sort'
            )
            ->addParserErrorException(
                (new ParserErrorException())
                    ->addParserErrorException(
                        (new ParserErrorException())
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                                'id'
                            )
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addError(new Error('datetime.type', 'Type should be "\DateTimeInterface", {{given}} given', ['given' => 'float'])),
                                'createdAt'
                            )
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addError(new Error('datetime.type', 'Type should be "\DateTimeInterface", {{given}} given', ['given' => 'float'])),
                                'updatedAt'
                            )
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                                'name'
                            )
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                                'tag'
                            )
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addParserErrorException(
                                        (new ParserErrorException())
                                            ->addParserErrorException(
                                                (new ParserErrorException())
                                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                                                'name'
                                            ),
                                        0
                                    )
                                    // does make sense, but to make sure the nesting works as expected
                                    ->addParserErrorException(
                                        (new ParserErrorException())
                                            ->addParserErrorException(
                                                (new ParserErrorException())
                                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                                                'name'
                                            ),
                                        0
                                    )
                                    ->addParserErrorException(
                                        (new ParserErrorException())
                                            ->addParserErrorException(
                                                (new ParserErrorException())
                                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                                                'name'
                                            ),
                                        3
                                    )
                                    // does make sense, but to make sure the nesting works as expected
                                    ->addParserErrorException(
                                        (new ParserErrorException())
                                            ->addParserErrorException(
                                                (new ParserErrorException())
                                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                                                'name'
                                            ),
                                        3
                                    ),
                                'vaccinations'
                            )
                            ->addParserErrorException(
                                (new ParserErrorException())
                                    ->addError(new Error('literal.type', 'Type should be "bool|float|int|string", {{given}} given', ['given' => 'float'])),
                                '_type'
                            ),
                        0
                    ),
                'items'
            )
        ;
    }
}
