<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Integration;

use Chubbyphp\Parsing\ParseError;
use Chubbyphp\Parsing\Parser;
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
            public null|string $firstname;
            public string $lastname;
            public null|int $age;
        };

        $p = new Parser();

        $schema = $p->array(
            $p->object([
                '_type' => $p->literal('person'),
                'firstname' => $p->string()->default('John'),
                'lastname' => $p->string()->default('Doe'),
                'age' => $p->union([
                    $p->integer(),
                    $p->string()->transform(static function (string $age, array &$parseErrors) {
                        $ageAsInteger = (int) $age;

                        if ((string) $ageAsInteger !== $age) {
                            $parseErrors[] = new ParseError(sprintf("Age '%s' is not parseable to inteter", $age));

                            return $age;
                        }

                        return $ageAsInteger;
                    })->nullable(),
                ]),
            ], $person::class)
        );

        $result = $schema->safeParse([
            ['_type' => 'person', 'firstname' => 'James', 'lastname' => 'Smith', 'age' => 32],
            ['_type' => 'person', 'firstname' => 'Jane', 'lastname' => 'Smith', 'age' => '28'],
            ['_type' => 'person'],
        ]);

        var_dump($result->data);
        var_dump($result->error?->getData());

        self::assertTrue(true);
    }
}
