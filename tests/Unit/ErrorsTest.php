<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Errors
 *
 * @internal
 */
final class ErrorsTest extends TestCase
{
    public function testToString(): void
    {
        self::assertSame('template1', (string) (new Errors())->add(new Error('code1', 'template1', [])));
        self::assertSame('path: template2', (string) (new Errors())->add(new Error('code2', 'template2', []), 'path'));

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
            EOD, (string) $this->provideErrors());
    }

    public function testHas(): void
    {
        self::assertTrue($this->provideErrors()->has());
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
                    'code' => 'const.type',
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
                    'code' => 'const.type',
                    'template' => 'Type should be "bool|float|int|string", {{given}} given',
                    'variables' => [
                        'given' => 'null',
                    ],
                ],
            ],
        ], $this->provideErrors()->jsonSerialize());
    }

    public function testToApiProblemInvalidParameters(): void
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
        ], $this->provideErrors()->toApiProblemInvalidParameters());
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
        ], $this->provideErrors()->toTree());
    }

    private function provideErrors(): Errors
    {
        return (new Errors())
            ->add(
                (new Errors())
                    ->add(new Error('int.type', 'Type should be "int", {{given}} given', ['given' => 'float']))
                    ->add(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                'offset'
            )
            ->add(
                (new Errors())
                    ->add(new Error('int.type', 'Type should be "int", {{given}} given', ['given' => 'float']))
                    ->add(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float'])),
                'limit'
            )
            ->add(
                (new Errors())
                    ->add(new Error('const.type', 'Type should be "bool|float|int|string", {{given}} given', ['given' => 'null']), 'name'),
                'sort'
            )
            ->add(
                (new Errors())
                    ->add(
                        (new Errors())
                            ->add(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']), 'id')
                            ->add(new Error('datetime.type', 'Type should be "\DateTimeInterface", {{given}} given', ['given' => 'float']), 'createdAt')
                            ->add(new Error('datetime.type', 'Type should be "\DateTimeInterface", {{given}} given', ['given' => 'float']), 'updatedAt')
                            ->add(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']), 'name')
                            ->add(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']), 'tag')
                            ->add(
                                (new Errors())
                                    ->add(
                                        (new Errors())
                                            ->add(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']), 'name'),
                                        '0'
                                    )
                                    ->add(
                                        (new Errors())
                                            ->add(new Error('string.type', 'Type should be "string", {{given}} given', ['given' => 'float']), 'name'),
                                        '3'
                                    ),
                                'vaccinations'
                            ),
                        '0'
                    ),
                'items'
            )
            ->add(new Error('const.type', 'Type should be "bool|float|int|string", {{given}} given', ['given' => 'null']), '_type')
        ;
    }
}
