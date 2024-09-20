<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorExceptionToString;

/**
 * @covers \Chubbyphp\Parsing\ParserErrorExceptionToString
 *
 * @internal
 */
final class ParserErrorExceptionToStringTest extends AbstractTestCase
{
    public function testGetMessage(): void
    {
        $exception = ParserErrorExceptionTest::getNestedParserErrorException();

        $stringableException = new ParserErrorExceptionToString($exception);

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

        self::assertSame($message, (string) $stringableException);

        $exception->addError(new Error('random', 'Make sure this error gets added as well', []), 'anotherField');

        $messageWithOneErrorMore = $message .= PHP_EOL.'anotherField: Make sure this error gets added as well';

        self::assertSame($messageWithOneErrorMore, (string) $stringableException);
    }
}
