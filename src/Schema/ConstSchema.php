<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class ConstSchema extends AbstractSchemaInnerParse
{
    public const string ERROR_TYPE_CODE = 'const.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "array|bool|float|int|\stdClass|string|null", {{given}} given';

    public const string ERROR_EQUALS_CODE = 'const.equals';
    public const string ERROR_EQUALS_TEMPLATE = 'Input should be {{expected}}, {{given}} given';

    /**
     * @param null|array<mixed>|bool|float|int|\stdClass|string $const any json value: null, boolean, number, string, array or object (associative array / \stdClass)
     */
    public function __construct(private array|bool|float|int|\stdClass|string|null $const) {}

    protected function innerParse(mixed $input): mixed
    {
        if (null !== $input
            && !\is_array($input)
            && !\is_bool($input)
            && !\is_float($input)
            && !\is_int($input)
            && !\is_string($input)
            && !$input instanceof \stdClass
        ) {
            throw new ErrorsException(
                new Error(
                    self::ERROR_TYPE_CODE,
                    self::ERROR_TYPE_TEMPLATE,
                    ['given' => $this->getDataType($input)]
                )
            );
        }

        /*
         * Equality is based on json (schema spec) equality: numbers with the same
         * mathematical value (1 and 1.0) are equal, while 1, "1" and true are not.
         */
        if (self::normalizeJson($input) === self::normalizeJson($this->const)) {
            return $input;
        }

        throw new ErrorsException(
            new Error(
                self::ERROR_EQUALS_CODE,
                self::ERROR_EQUALS_TEMPLATE,
                ['expected' => $this->const, 'given' => $input]
            )
        );
    }
}
