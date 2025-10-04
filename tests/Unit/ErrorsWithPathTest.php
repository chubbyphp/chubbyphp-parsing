<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsWithPath;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\ErrorsWithPath
 * @covers \Chubbyphp\Parsing\ErrorWithPath
 *
 * @internal
 */
final class ErrorsWithPathTest extends TestCase
{
    public function testToString(): void
    {
        self::assertSame(<<<'EOD'
            offset: Type should be "int", "float" given
            offset: Type should be "string", "float" given
            limit: Type should be "int", "float" given
            limit: Type should be "string", "float" given
            sort.name: Type should be "bool|float|int|string", "null" given
            items.0.id: Type should be "string", "float" given
            items.0.createdAt: Type should be "\DateTimeInterface", "float" given
            items.0.updatedAt: Type should be "\DateTimeInterface", "float" given
            items.0.name: Type should be "string", "float" given
            items.0.tag: Type should be "string", "float" given
            items.0.vaccinations.0.name: Type should be "string", "float" given
            items.0.vaccinations.3.name: Type should be "string", "float" given
            _type: Type should be "bool|float|int|string", "null" given
            EOD, (string) $this->provideErrorsWithPath());
    }

    public function testJsonSerialize(): void
    {
        self::assertSame([
            0 => [
                'path' => 'offset',
                'error' => [
                    'code' => 'int.type',
                    'template' => 'Type should be "int", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            1 => [
                'path' => 'offset',
                'error' => [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            2 => [
                'path' => 'limit',
                'error' => [
                    'code' => 'int.type',
                    'template' => 'Type should be "int", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            3 => [
                'path' => 'limit',
                'error' => [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            4 => [
                'path' => 'sort.name',
                'error' => [
                    'code' => 'literal.type',
                    'template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'variables' => [
                        'given' => 'null',
                    ],
                ],
            ],
            5 => [
                'path' => 'items.0.id',
                'error' => [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            6 => [
                'path' => 'items.0.createdAt',
                'error' => [
                    'code' => 'datetime.type',
                    'template' => 'Type should be "\DateTimeInterface", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            7 => [
                'path' => 'items.0.updatedAt',
                'error' => [
                    'code' => 'datetime.type',
                    'template' => 'Type should be "\DateTimeInterface", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            8 => [
                'path' => 'items.0.name',
                'error' => [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            9 => [
                'path' => 'items.0.tag',
                'error' => [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            10 => [
                'path' => 'items.0.vaccinations.0.name',
                'error' => [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            11 => [
                'path' => 'items.0.vaccinations.3.name',
                'error' => [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'float',
                    ],
                ],
            ],
            12 => [
                'path' => '_type',
                'error' => [
                    'code' => 'literal.type',
                    'template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'variables' => [
                        'given' => 'null',
                    ],
                ],
            ],
        ], json_decode(json_encode($this->provideErrorsWithPath()), true));
    }

    public function testToTree(): void
    {
        self::assertSame([
            'offset' => [
                0 => 'Type should be "int", "float" given',
                1 => 'Type should be "string", "float" given',
            ],
            'limit' => [
                0 => 'Type should be "int", "float" given',
                1 => 'Type should be "string", "float" given',
            ],
            'sort' => [
                'name' => [
                    0 => 'Type should be "bool|float|int|string", "null" given',
                ],
            ],
            'items' => [
                0 => [
                    'id' => [
                        0 => 'Type should be "string", "float" given',
                    ],
                    'createdAt' => [
                        0 => 'Type should be "\DateTimeInterface", "float" given',
                    ],
                    'updatedAt' => [
                        0 => 'Type should be "\DateTimeInterface", "float" given',
                    ],
                    'name' => [
                        0 => 'Type should be "string", "float" given',
                    ],
                    'tag' => [
                        0 => 'Type should be "string", "float" given',
                    ],
                    'vaccinations' => [
                        0 => [
                            'name' => [
                                0 => 'Type should be "string", "float" given',
                            ],
                        ],
                        3 => [
                            'name' => [
                                0 => 'Type should be "string", "float" given',
                            ],
                        ],
                    ],
                ],
            ],
            '_type' => [
                0 => 'Type should be "bool|float|int|string", "null" given',
            ],
        ], $this->provideErrorsWithPath()->toTree());
    }

    public function testToApiProblems(): void
    {
        self::assertSame([
            0 => [
                'name' => 'offset',
                'reason' => 'Type should be "int", "float" given',
                'details' => [
                    '_template' => 'Type should be "int", {{given}} given',
                    'given' => 'float',
                ],
            ],
            1 => [
                'name' => 'offset',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            2 => [
                'name' => 'limit',
                'reason' => 'Type should be "int", "float" given',
                'details' => [
                    '_template' => 'Type should be "int", {{given}} given',
                    'given' => 'float',
                ],
            ],
            3 => [
                'name' => 'limit',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            4 => [
                'name' => 'sort[name]',
                'reason' => 'Type should be "bool|float|int|string", "null" given',
                'details' => [
                    '_template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'given' => 'null',
                ],
            ],
            5 => [
                'name' => 'items[0][id]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            6 => [
                'name' => 'items[0][createdAt]',
                'reason' => 'Type should be "\DateTimeInterface", "float" given',
                'details' => [
                    '_template' => 'Type should be "\DateTimeInterface", {{given}} given',
                    'given' => 'float',
                ],
            ],
            7 => [
                'name' => 'items[0][updatedAt]',
                'reason' => 'Type should be "\DateTimeInterface", "float" given',
                'details' => [
                    '_template' => 'Type should be "\DateTimeInterface", {{given}} given',
                    'given' => 'float',
                ],
            ],
            8 => [
                'name' => 'items[0][name]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            9 => [
                'name' => 'items[0][tag]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            10 => [
                'name' => 'items[0][vaccinations][0][name]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            11 => [
                'name' => 'items[0][vaccinations][3][name]',
                'reason' => 'Type should be "string", "float" given',
                'details' => [
                    '_template' => 'Type should be "string", {{given}} given',
                    'given' => 'float',
                ],
            ],
            12 => [
                'name' => '_type',
                'reason' => 'Type should be "bool|float|int|string", "null" given',
                'details' => [
                    '_template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'given' => 'null',
                ],
            ],
        ], $this->provideErrorsWithPath()->toApiProblems());
    }

    private function provideErrorsWithPath(): ErrorsWithPath
    {
        return new ErrorsWithPath()
            ->addErrorsWithPath(
                new ErrorsWithPath('offset')
                    ->addError(new Error('int.type', 'Type should be "int", {{given}} given', ['given' => 'float']))
                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
            )
            ->addErrorsWithPath(
                new ErrorsWithPath('limit')
                    ->addError(new Error('int.type', 'Type should be "int", {{given}} given', ['given' => 'float']))
                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
            )
            ->addErrorsWithPath(
                new ErrorsWithPath('sort')
                    ->addErrorsWithPath(
                        new ErrorsWithPath('name')
                            ->addError(new Error('literal.type', 'Type should be "bool|float|int|string", {{given}} given', ['given' => 'null']))
                    )
            )
            ->addErrorsWithPath(
                new ErrorsWithPath('items')
                    ->addErrorsWithPath(
                        new ErrorsWithPath('0')
                            ->addErrorsWithPath(
                                new ErrorsWithPath('id')
                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']))
                            )
                            ->addErrorsWithPath(
                                new ErrorsWithPath('createdAt')
                                    ->addError(new Error('datetime.type', 'Type should be "\DateTimeInterface", {{given}} given', ['given' => 'float']))
                            )
                            ->addErrorsWithPath(
                                new ErrorsWithPath('updatedAt')
                                    ->addError(new Error('datetime.type', 'Type should be "\DateTimeInterface", {{given}} given', ['given' => 'float']))
                            )
                            ->addErrorsWithPath(
                                new ErrorsWithPath('name')
                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']))
                            )
                            ->addErrorsWithPath(
                                new ErrorsWithPath('tag')
                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']))
                            )
                            ->addErrorsWithPath(
                                new ErrorsWithPath('vaccinations')
                                    ->addErrorsWithPath(
                                        new ErrorsWithPath('0')
                                            ->addErrorsWithPath(
                                                new ErrorsWithPath('name')
                                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']))
                                            )
                                    )
                                    ->addErrorsWithPath(
                                        new ErrorsWithPath('3')
                                            ->addErrorsWithPath(
                                                new ErrorsWithPath('name')
                                                    ->addError(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']))
                                            )
                                    )
                            )
                    )
            )
            ->addErrorsWithPath(
                new ErrorsWithPath('_type')
                    ->addError(new Error('literal.type', 'Type should be "bool|float|int|string", {{given}} given', ['given' => 'null']))
            )
        ;
    }
}
