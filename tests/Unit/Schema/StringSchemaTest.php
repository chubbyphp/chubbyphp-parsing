<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\StringSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\StringSchema
 *
 * @internal
 */
final class StringSchemaTest extends TestCase
{
    public function testParseSuccess(): void
    {
        $input = 'test';

        $schema = new StringSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->default($input);

        self::assertSame($input, $schema->parse(null));
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['Type should be "string" "NULL" given'], $parserErrorException->getErrors());
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = '1';

        $schema = (new StringSchema())->transform(static fn (string $output) => (int) $output);

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new StringSchema())
            ->catch(static function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame(['Type should be "string" "NULL" given'], $parserErrorException->getErrors());

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

        self::assertSame(['Type should be "string" "NULL" given'], $schema->safeParse(null)->exception->getErrors());
    }

    public function testParseWithValidMin(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->min(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMin(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->min(5);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['Length should at min 5, 4 given'], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidMax(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->max(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMax(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->max(3);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['Length should at max 3, 4 given'], $parserErrorException->getErrors());
        }
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['Length should be 5, 4 given'], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidEmail(): void
    {
        $input = 'john.doe@example.com';

        $schema = (new StringSchema())->email();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMail(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->email();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"test" is not a valid email'], $parserErrorException->getErrors());
        }
    }

    public function testParseWithUrlWithInvalidOption(): void
    {
        try {
            (new StringSchema())->url(99);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Invalid option "99" given', $e->getMessage());
        }
    }

    public function testParseWithValidUrl(): void
    {
        $input = 'https://localhost';

        $schema = (new StringSchema())->url();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUrl(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->url();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"test" is not a valid url'], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidUrlPathReqired(): void
    {
        $input = 'https://localhost/';

        $schema = (new StringSchema())->url(FILTER_FLAG_PATH_REQUIRED);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUrlPathReqired(): void
    {
        $input = 'https://localhost';

        $schema = (new StringSchema())->url(FILTER_FLAG_PATH_REQUIRED);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"https://localhost" is not a valid url'], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidUrlQueryReqired(): void
    {
        $input = 'https://localhost?key=value';

        $schema = (new StringSchema())->url(FILTER_FLAG_QUERY_REQUIRED);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUrlQueryReqired(): void
    {
        $input = 'https://localhost';

        $schema = (new StringSchema())->url(FILTER_FLAG_QUERY_REQUIRED);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"https://localhost" is not a valid url'], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidUrlPathAndQueryReqired(): void
    {
        $input = 'https://localhost/?key=value';

        $schema = (new StringSchema())->url(FILTER_FLAG_PATH_REQUIRED | FILTER_FLAG_QUERY_REQUIRED);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUrlPathAndQueryReqired(): void
    {
        $input = 'https://localhost';

        $schema = (new StringSchema())->url(FILTER_FLAG_PATH_REQUIRED | FILTER_FLAG_QUERY_REQUIRED);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"https://localhost" is not a valid url'], $parserErrorException->getErrors());
        }
    }

    public function testParseWithUuidWithInvalidOption(): void
    {
        try {
            (new StringSchema())->uuid(99);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Invalid option "99" given', $e->getMessage());
        }
    }

    public function testParseWithValidUuid(): void
    {
        $input1 = '960b0533-da17-42d8-a0a4-dd2ab7213caf';
        $input2 = '960b0533-da17-52d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuid();

        self::assertSame($input1, $schema->parse($input1));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseWithInvalidUuid(): void
    {
        $input = '960b0533-da17-72d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuid();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"960b0533-da17-72d8-a0a4-dd2ab7213caf" is not a valid uuid',
            ], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidUuidV4(): void
    {
        $input = '960b0533-da17-42d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuid(StringSchema::UUID_V4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUuidV4(): void
    {
        $input = '960b0533-da17-52d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuid(StringSchema::UUID_V4);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"960b0533-da17-52d8-a0a4-dd2ab7213caf" is not a valid uuid v4',
            ], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidUuidV5(): void
    {
        $input = '960b0533-da17-52d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuid(StringSchema::UUID_V5);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUuidV5(): void
    {
        $input = '960b0533-da17-42d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuid(StringSchema::UUID_V5);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                '"960b0533-da17-42d8-a0a4-dd2ab7213caf" is not a valid uuid v5',
            ], $parserErrorException->getErrors());
        }
    }

    public function testParseWithRegexWithInvalidPattern(): void
    {
        try {
            (new StringSchema())->regex('test');

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Invalid pattern "test" given', $e->getMessage());
        }
    }

    public function testParseWithValidRegex(): void
    {
        $input = 'aBcDeFg';

        $schema = (new StringSchema())->regex('/^[a-z]+$/i');

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidRegex(): void
    {
        $input = 'a1B2C3d4';

        $schema = (new StringSchema())->regex('/^[a-z]+$/i');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"a1B2C3d4" does not match pattern "/^[a-z]+$/i"'], $parserErrorException->getErrors());
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"example" does not contains with "lee"'], $parserErrorException->getErrors());
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"example" does not starts with "xam"'], $parserErrorException->getErrors());
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
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"example" does not ends with "mpl"'], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidDateTime(): void
    {
        $input = '2024-01-20T09:15:00Z';

        $schema = (new StringSchema())->dateTime();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidDateTime(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->dateTime();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"test" is not a valid datetime'], $parserErrorException->getErrors());
        }
    }

    public function testParseWithIpWithInvalidOption(): void
    {
        try {
            (new StringSchema())->ip(99);

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Invalid option "99" given', $e->getMessage());
        }
    }

    public function testParseWithValidIpV4WithoutOption(): void
    {
        $input1 = '192.168.1.1';
        $input2 = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $schema = (new StringSchema())->ip();

        self::assertSame($input1, $schema->parse($input1));
        self::assertSame($input2, $schema->parse($input2));
    }

    public function testParseWithInvalidIpV4WithoutOption(): void
    {
        $input1 = '256.202.56.89';
        $input2 = '2001:0db8:85a3:0000:0000:8a2e:0370:733g';

        $schema = (new StringSchema())->ip();

        try {
            $schema->parse($input1);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                '"256.202.56.89" is not a valid ip',
            ], $parserErrorException->getErrors());
        }

        try {
            $schema->parse($input2);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                '"2001:0db8:85a3:0000:0000:8a2e:0370:733g" is not a valid ip',
            ], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidIpV4(): void
    {
        $input = '192.168.1.1';

        $schema = (new StringSchema())->ip(FILTER_FLAG_IPV4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidIpV4(): void
    {
        $input = '256.202.56.89';

        $schema = (new StringSchema())->ip(FILTER_FLAG_IPV4);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                '"256.202.56.89" is not a valid ip',
            ], $parserErrorException->getErrors());
        }
    }

    public function testParseWithValidIpV6(): void
    {
        $input = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $schema = (new StringSchema())->ip(FILTER_FLAG_IPV6);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidIpV6(): void
    {
        $input = '2001:0db8:85a3:0000:0000:8a2e:0370:733g';

        $schema = (new StringSchema())->ip(FILTER_FLAG_IPV6);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame(['"2001:0db8:85a3:0000:0000:8a2e:0370:733g" is not a valid ip',
            ], $parserErrorException->getErrors());
        }
    }

    public function testParseWithTrim(): void
    {
        $input = '   test ';

        $schema = (new StringSchema())->trim();

        self::assertSame(trim($input), $schema->parse($input));
    }

    public function testParseWithtoLower(): void
    {
        $input = 'TEST';

        $schema = (new StringSchema())->toLower();

        self::assertSame(strtolower($input), $schema->parse($input));
    }

    public function testParseWithtoUpper(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->toUpper();

        self::assertSame(strtoupper($input), $schema->parse($input));
    }
}
