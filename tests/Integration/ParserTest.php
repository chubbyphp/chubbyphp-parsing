<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Integration;

use Chubbyphp\Parsing\Parser;
use Chubbyphp\Parsing\ParserErrorException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ParserTest extends TestCase
{
    public function testParse(): void
    {
        $person = new class() {
            public string $firstname;
            public string $lastname;
            public null|int $age;
            public array $contactDetails;
        };

        $email = new class() {
            public string $_type;
            public string $value;
        };

        $phone = new class() {
            public string $_type;
            public string $value;
        };

        $p = new Parser();

        $schema = $p->array(
            $p->object(['firstname' => $p->string(),
                'lastname' => $p->string(),
                'age' => $p->union([
                    $p->integer(),
                    $p->string()->transform(static function (string $age) {
                        $ageAsInteger = (int) $age;

                        if ((string) $ageAsInteger !== $age) {
                            throw new ParserErrorException(sprintf("Age '%s' is not parseable to integer", $age));
                        }

                        return $ageAsInteger;
                    })->nullable(),
                ]),
                'contactDetails' => $p->array($p->discriminatedUnion(
                    [
                        $p->object([
                            '_type' => $p->literal('email'),
                            'value' => $p->string(),
                        ], $email::class),
                        $p->object([
                            '_type' => $p->literal('phone'),
                            'value' => $p->string(),
                        ], $phone::class),
                    ],
                    '_type',
                ))->default([]),
            ], $person::class)
        );

        try {
            var_dump($schema->parse([
                [
                    'firstname' => 'James',
                    'lastname' => 'Smith',
                    'age' => 32,
                    'contactDetails' => [
                        ['_type' => 'email', 'value' => 'james.smith@example.com'],
                        ['_type' => 'phone', 'value' => '+41790000000'],
                    ],
                ],
                [
                    'firstname' => 'Jane',
                    'lastname' => 'Smith',
                    'age' => '28',
                ],
            ]));
        } catch (ParserErrorException $e) {
            var_dump($e->getErrors());
        }

        self::assertTrue(true);
    }
}
