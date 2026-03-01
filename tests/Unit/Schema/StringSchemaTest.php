<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\Enum\Uuid;
use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\StringSchema
 *
 * @internal
 */
final class StringSchemaTest extends TestCase
{
    public function testImmutability(): void
    {
        $schema = new StringSchema();

        self::assertNotSame($schema, $schema->nullable());
        self::assertNotSame($schema, $schema->nullable(false));
        self::assertNotSame($schema, $schema->default('test'));
        self::assertNotSame($schema, $schema->preParse(static fn (mixed $input) => $input));
        self::assertNotSame($schema, $schema->postParse(static fn (string $output) => $output));
        self::assertNotSame($schema, $schema->catch(static fn (string $output, ErrorsException $e) => $output));
        self::assertNotSame($schema, $schema->length(1));
        self::assertNotSame($schema, $schema->minLength(1));
        self::assertNotSame($schema, $schema->maxLength(1));
        self::assertNotSame($schema, $schema->contains('test'));
        self::assertNotSame($schema, $schema->startsWith('test'));
        self::assertNotSame($schema, $schema->endsWith('test'));
        self::assertNotSame($schema, $schema->hostname());
        self::assertNotSame($schema, $schema->email());
        self::assertNotSame($schema, $schema->ipV4());
        self::assertNotSame($schema, $schema->ipV6());
        self::assertNotSame($schema, $schema->mac());
        self::assertNotSame($schema, $schema->pattern('/.*/'));
        self::assertNotSame($schema, $schema->uri());
        self::assertNotSame($schema, $schema->uuid());
        self::assertNotSame($schema, $schema->trim());
        self::assertNotSame($schema, $schema->trimStart());
        self::assertNotSame($schema, $schema->trimEnd());
        self::assertNotSame($schema, $schema->toLowerCase());
        self::assertNotSame($schema, $schema->toUpperCase());
    }

    public function testParseSuccess(): void
    {
        $input = 'test';

        $schema = new StringSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input1 = 'test1';
        $input2 = 'test2';

        $schema = (new StringSchema())->default($input1);

        self::assertSame($input1, $schema->parse(null));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new StringSchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new StringSchema();

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", {{given}} given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseSuccessWithPreParse(): void
    {
        $input = '1';

        $schema = (new StringSchema())->preParse(static fn () => $input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithPostParse(): void
    {
        $input = '1';

        $schema = (new StringSchema())->postParse(static fn (string $output) => (int) $output);

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new StringSchema())
            ->catch(static function (mixed $input, ErrorsException $errorsException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'path' => '',
                        'error' => [
                            'code' => 'string.type',
                            'template' => 'Type should be "string", {{given}} given',
                            'variables' => [
                                'given' => 'NULL',
                            ],
                        ],
                    ],
                ], $errorsException->errors->jsonSerialize());

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = 'test';

        $schema = new StringSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new StringSchema();

        self::assertSame([
            [
                'path' => '',
                'error' => [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", {{given}} given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ],
        ], $schema->safeParse(null)->exception->errors->jsonSerialize());
    }

    public function testParseWithValidLength(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->length(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidLength(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->length(5);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.length',
                        'template' => 'Length {{length}}, {{given}} given',
                        'variables' => [
                            'length' => 5,
                            'given' => \strlen($input),
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidMinLength(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->minLength(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMinLength(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->minLength(5);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.minLength',
                        'template' => 'Min length {{min}}, {{given}} given',
                        'variables' => [
                            'minLength' => 5,
                            'given' => \strlen($input),
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidMaxLength(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->maxLength(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMaxLength(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->maxLength(3);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.maxLength',
                        'template' => 'Max length {{max}}, {{given}} given',
                        'variables' => [
                            'maxLength' => 3,
                            'given' => \strlen($input),
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidContains(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->contains('amp');

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidContains(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->contains('lee');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.contains',
                        'template' => '{{given}} does not contain {{contains}}',
                        'variables' => [
                            'contains' => 'lee',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidIncludes(): void
    {
        $input = 'example';

        error_clear_last();

        $schema = (new StringSchema())->includes('amp');

        $lastError = error_get_last();

        self::assertNotNull($lastError);
        self::assertArrayHasKey('type', $lastError);
        self::assertSame(E_USER_DEPRECATED, $lastError['type']);
        self::assertArrayHasKey('message', $lastError);
        self::assertSame('Use contains($contains) instead', $lastError['message']);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidIncludes(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->includes('lee');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.includes',
                        'template' => '{{given}} does not include {{includes}}',
                        'variables' => [
                            'includes' => 'lee',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidStartsWith(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->startsWith('exa');

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidStartsWith(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->startsWith('xam');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.startsWith',
                        'template' => '{{given}} does not starts with {{startsWith}}',
                        'variables' => [
                            'startsWith' => 'xam',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidEndsWith(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->endsWith('ple');

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidEndsWith(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->endsWith('mpl');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.endsWith',
                        'template' => '{{given}} does not ends with {{endsWith}}',
                        'variables' => [
                            'endsWith' => 'mpl',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidHostname(): void
    {
        $input = 'example.com';

        $schema = (new StringSchema())->hostname();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidHostname(): void
    {
        $input = 'example..com';

        $schema = (new StringSchema())->hostname();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.hostname',
                        'template' => 'Invalid hostname {{given}}',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidDomain(): void
    {
        $input = 'example.com';

        error_clear_last();

        $schema = (new StringSchema())->domain();

        $lastError = error_get_last();

        self::assertNotNull($lastError);
        self::assertArrayHasKey('type', $lastError);
        self::assertSame(E_USER_DEPRECATED, $lastError['type']);
        self::assertArrayHasKey('message', $lastError);
        self::assertSame('Use hostname() instead', $lastError['message']);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidDomain(): void
    {
        $input = 'example..com';

        $schema = (new StringSchema())->domain();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.domain',
                        'template' => 'Invalid domain {{given}}',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidEmail(): void
    {
        $input = 'john.doe@example.com';

        $schema = (new StringSchema())->email();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidEmail(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->email();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.email',
                        'template' => 'Invalid email {{given}}',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidIpV4(): void
    {
        $input = '192.168.1.1';

        $schema = (new StringSchema())->ipV4();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidIpV4(): void
    {
        $input = '256.202.56.89';

        $schema = (new StringSchema())->ipV4();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.ip',
                        'template' => 'Invalid ip {{version}} {{given}}',
                        'variables' => [
                            'version' => 'v4',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidIpV6(): void
    {
        $input = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $schema = (new StringSchema())->ipV6();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidIpV6(): void
    {
        $input = '2001:0db8:85a3:0000:0000:8a2e:0370:733g';

        $schema = (new StringSchema())->ipV6();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.ip',
                        'template' => 'Invalid ip {{version}} {{given}}',
                        'variables' => [
                            'version' => 'v6',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidMac(): void
    {
        $input = 'ff:ff:ff:ff:ff:ff';

        $schema = (new StringSchema())->mac();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMac(): void
    {
        $input = 'ff:ff:ff:ff:ff:fg';

        $schema = (new StringSchema())->mac();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.mac',
                        'template' => 'Invalid mac {{given}}',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithMatchWithInvalidPattern(): void
    {
        try {
            (new StringSchema())->match('test');

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Invalid match "test" given', $e->getMessage());
        }
    }

    public function testParseWithValidMatch(): void
    {
        $input = 'aBcDeFg';

        error_clear_last();

        $schema = (new StringSchema())->match('/^[a-z]+$/i');

        $lastError = error_get_last();

        self::assertNotNull($lastError);
        self::assertArrayHasKey('type', $lastError);
        self::assertSame(E_USER_DEPRECATED, $lastError['type']);
        self::assertArrayHasKey('message', $lastError);
        self::assertSame('Use pattern($pattern) instead', $lastError['message']);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMatch(): void
    {
        $input = 'a1B2C3d4';

        $schema = (new StringSchema())->match('/^[a-z]+$/i');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.match',
                        'template' => '{{given}} does not match {{match}}',
                        'variables' => [
                            'match' => '/^[a-z]+$/i',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithPatternWithInvalidPattern(): void
    {
        try {
            (new StringSchema())->pattern('test');

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Invalid pattern "test" given', $e->getMessage());
        }
    }

    public function testParseWithValidPattern(): void
    {
        $input = 'aBcDeFg';

        $schema = (new StringSchema())->pattern('/^[a-z]+$/i');

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidPattern(): void
    {
        $input = 'a1B2C3d4';

        $schema = (new StringSchema())->pattern('/^[a-z]+$/i');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.pattern',
                        'template' => '{{given}} does not pattern {{pattern}}',
                        'variables' => [
                            'pattern' => '/^[a-z]+$/i',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithRegexpWithInvalidPattern(): void
    {
        try {
            (new StringSchema())->regexp('test');

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Invalid regexp "test" given', $e->getMessage());
        }
    }

    public function testParseWithValidRegexp(): void
    {
        $input = 'aBcDeFg';

        error_clear_last();

        $schema = (new StringSchema())->regexp('/^[a-z]+$/i');

        $lastError = error_get_last();

        self::assertNotNull($lastError);
        self::assertArrayHasKey('type', $lastError);
        self::assertSame(E_USER_DEPRECATED, $lastError['type']);
        self::assertArrayHasKey('message', $lastError);
        self::assertSame('Use pattern($pattern) instead', $lastError['message']);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidRegexp(): void
    {
        $input = 'a1B2C3d4';

        $schema = (new StringSchema())->regexp('/^[a-z]+$/i');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.regexp',
                        'template' => '{{given}} does not regexp {{regexp}}',
                        'variables' => [
                            'regexp' => '/^[a-z]+$/i',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidUri(): void
    {
        $input = 'https://localhost';

        $schema = (new StringSchema())->uri();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUri(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->uri();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.uri',
                        'template' => 'Invalid uri {{given}}',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidUrl(): void
    {
        $input = 'https://localhost';

        error_clear_last();

        $schema = (new StringSchema())->url();

        $lastError = error_get_last();

        self::assertNotNull($lastError);
        self::assertArrayHasKey('type', $lastError);
        self::assertSame(E_USER_DEPRECATED, $lastError['type']);
        self::assertArrayHasKey('message', $lastError);
        self::assertSame('Use uri() instead', $lastError['message']);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUrl(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->url();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.url',
                        'template' => 'Invalid url {{given}}',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidUuid(): void
    {
        $input = '960b0533-da17-42d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuid();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUuid(): void
    {
        $input = '960b0533-da17-52d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuid();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.uuid',
                        'template' => 'Invalid uuid {{version}} {{given}}',
                        'variables' => [
                            'version' => 'v4',
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    #[DataProvider('provideParseWithValidUuidsCases')]
    public function testParseWithValidUuids(Uuid $version, string $uuid): void
    {
        self::assertSame($uuid, (new StringSchema())->uuid($version)->parse($uuid));
    }

    /**
     * @return array<string, array{Uuid, string}>
     */
    public static function provideParseWithValidUuidsCases(): iterable
    {
        return [
            'v1 timestamp + MAC' => [
                Uuid::v1,
                '6fa459ea-ee8a-1d13-a3ac-0800200c9a66',
            ],
            'v2 DCE security' => [
                Uuid::v2,
                '000003e8-ee8a-2d13-8500-0800200c9a66',
            ],
            'v3 MD5 hash' => [
                Uuid::v3,
                '5df41881-3aed-3515-88a7-2f4a814cf09e',
            ],
            'v4 random' => [
                Uuid::v4,
                'f47ac10b-58cc-4372-a567-0e02b2c3d479',
            ],
            'v5 SHA-1 hash' => [
                Uuid::v5,
                'a6e4eb18-bba0-5a2d-b0aa-b85e4718e89f',
            ],
            'v6 reordered timestamp' => [
                Uuid::v6,
                '1d13ee8a-6fa4-659e-a3ac-0800200c9a66',
            ],
            'v7 unix timestamp' => [
                Uuid::v7,
                '019490a9-5e00-7d34-b5f6-4a1b2c3d4e5f',
            ],
            'v8 custom' => [
                Uuid::v8,
                'c0ffee00-cafe-8bad-beef-deaddeadbeef',
            ],
        ];
    }

    #[DataProvider('provideParseWithInvalidUuidsCases')]
    public function testParseWithInvalidUuids(Uuid $version, string $uuid): void
    {
        try {
            (new StringSchema())->uuid($version)->parse($uuid);

            self::fail('Expected ErrorsException was not thrown');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.uuid',
                        'template' => 'Invalid uuid {{version}} {{given}}',
                        'variables' => [
                            'version' => 'v'.$version->value,
                            'given' => $uuid,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    /**
     * @return array<string, array{Uuid, string}>
     */
    public static function provideParseWithInvalidUuidsCases(): iterable
    {
        return [
            'v1 with invalid variant c' => [
                Uuid::v1,
                '6fa459ea-ee8a-1d13-c3ac-0800200c9a66',
            ],
            'v2 with invalid variant 0' => [
                Uuid::v2,
                '000003e8-ee8a-2d13-0500-0800200c9a66',
            ],
            'v3 with invalid hex character g' => [
                Uuid::v3,
                '5df41881-3aed-3515-88a7-2f4a814cg09e',
            ],
            'v4 with wrong version 5' => [
                Uuid::v4,
                'f47ac10b-58cc-5372-a567-0e02b2c3d479',
            ],
            'v5 with invalid variant 7' => [
                Uuid::v5,
                'a6e4eb18-bba0-5a2d-70aa-b85e4718e89f',
            ],
            'v6 with wrong version 1' => [
                Uuid::v6,
                '1d13ee8a-6fa4-159e-a3ac-0800200c9a66',
            ],
            'v7 with invalid variant f' => [
                Uuid::v7,
                '019490a9-5e00-7d34-f5f6-4a1b2c3d4e5f',
            ],
            'v8 with wrong version 0' => [
                Uuid::v8,
                'c0ffee00-cafe-0bad-beef-deaddeadbeef',
            ],
        ];
    }

    public function testParseWithValidUuidV4(): void
    {
        $input = '960b0533-da17-42d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuidV4();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithValidUuidV5(): void
    {
        $input = '960b0533-da17-52d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuidV5();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithTrim(): void
    {
        $input = '   test ';

        $schema = (new StringSchema())->trim();

        self::assertSame(trim($input), $schema->parse($input));
    }

    public function testParseWithTrimStart(): void
    {
        $input = '   test ';

        $schema = (new StringSchema())->trimStart();

        self::assertSame(ltrim($input), $schema->parse($input));
    }

    public function testParseWithTrimEnd(): void
    {
        $input = '   test ';

        $schema = (new StringSchema())->trimEnd();

        self::assertSame(rtrim($input), $schema->parse($input));
    }

    public function testParseWithToLowerCase(): void
    {
        $input = 'TEST';

        $schema = (new StringSchema())->toLowerCase();

        self::assertSame(strtolower($input), $schema->parse($input));
    }

    public function testParseWithToUpperCase(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->toUpperCase();

        self::assertSame(strtoupper($input), $schema->parse($input));
    }

    public function testParseWithValidToDateTime(): void
    {
        $input = '2024-01-20T09:15:00+00:00';

        $schema = (new StringSchema())->toDateTime()->from(new \DateTimeImmutable('2024-01-20T09:00:00+00:00'));

        self::assertEquals(new \DateTimeImmutable($input), $schema->parse($input));
    }

    public function testParseWithInvalidToDateTimeWithInvalidMonth(): void
    {
        $input = '2017-13-01';

        $schema = (new StringSchema())->toDateTime();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.datetime',
                        'template' => 'Cannot convert {{given}} to datetime',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithInvalidToDateTimeWithInvalidDay(): void
    {
        $input = '2017-02-31';

        $schema = (new StringSchema())->toDateTime();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.datetime',
                        'template' => 'Cannot convert {{given}} to datetime',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithInvalidToDateTimeWithAllZero(): void
    {
        $input = '0000-00-00';

        $schema = (new StringSchema())->toDateTime();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.datetime',
                        'template' => 'Cannot convert {{given}} to datetime',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithInvalidToDateTimeWithText(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->toDateTime();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.datetime',
                        'template' => 'Cannot convert {{given}} to datetime',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidtoDateTimeNullable(): void
    {
        $schema = (new StringSchema())->nullable()->toDateTime();

        self::assertNull($schema->parse(null));
    }

    public function testParseWithValidtoBool(): void
    {
        $schema = (new StringSchema())->toBool();

        self::assertTrue($schema->parse('true'));
        self::assertTrue($schema->parse('yes'));
        self::assertTrue($schema->parse('on'));
        self::assertTrue($schema->parse('1'));
        self::assertFalse($schema->parse('false'));
        self::assertFalse($schema->parse('no'));
        self::assertFalse($schema->parse('off'));
        self::assertFalse($schema->parse('0'));
    }

    public function testParseWithInvalidToBool(): void
    {
        $input = 'truee';

        $schema = (new StringSchema())->toBool();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.bool',
                        'template' => 'Cannot convert {{given}} to bool',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidtoBoolNullable(): void
    {
        $schema = (new StringSchema())->nullable()->toBool();

        self::assertNull($schema->parse(null));
    }

    public function testParseWithValidtoFloat(): void
    {
        $input = '4.2';

        $schema = (new StringSchema())->toFloat()->exclusiveMinimum(4.0);

        self::assertSame((float) $input, $schema->parse($input));
    }

    public function testParseWithInvalidToFloat(): void
    {
        $input = '4.2cars';

        $schema = (new StringSchema())->toFloat();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.float',
                        'template' => 'Cannot convert {{given}} to float',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidtoFloatNullable(): void
    {
        $schema = (new StringSchema())->nullable()->toFloat();

        self::assertNull($schema->parse(null));
    }

    public function testParseWithValidtoInt(): void
    {
        $input = '42';

        $schema = (new StringSchema())->toInt()->exclusiveMinimum(40);

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseWithInvalidToInt(): void
    {
        $input = '42cars';

        $schema = (new StringSchema())->toInt();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ErrorsException $errorsException) {
            self::assertSame([
                [
                    'path' => '',
                    'error' => [
                        'code' => 'string.int',
                        'template' => 'Cannot convert {{given}} to int',
                        'variables' => [
                            'given' => $input,
                        ],
                    ],
                ],
            ], $errorsException->errors->jsonSerialize());
        }
    }

    public function testParseWithValidtoIntNullable(): void
    {
        $schema = (new StringSchema())->nullable()->toInt();

        self::assertNull($schema->parse(null));
    }
}
