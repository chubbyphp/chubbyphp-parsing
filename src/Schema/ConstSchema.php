<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class ConstSchema extends AbstractSchemaInnerParse
{
    public const string ERROR_TYPE_CODE = 'const.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "bool|float|int|string", {{given}} given';

    public const string ERROR_EQUALS_CODE = 'const.equals';
    public const string ERROR_EQUALS_TEMPLATE = 'Input should be {{expected}}, {{given}} given';

    public function __construct(private bool|float|int|string $const) {}

    protected function innerParse(mixed $input): mixed
    {
        if (!\is_bool($input) && !\is_float($input) && !\is_int($input) && !\is_string($input)) {
            throw new ErrorsException(
                new Error(
                    self::ERROR_TYPE_CODE,
                    self::ERROR_TYPE_TEMPLATE,
                    ['given' => $this->getDataType($input)]
                )
            );
        }

        if ($input === $this->const) {
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
