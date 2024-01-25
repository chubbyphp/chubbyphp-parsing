<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class BoolSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'bool.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "bool", "{{given}}" given';

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_bool($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            return $this->dispatchMiddlewares($input);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function toInt(): static
    {
        return $this->middleware(static fn (bool $bool) => true === $bool ? 1 : 0);
    }

    public function toString(): static
    {
        return $this->middleware(static fn (bool $bool) => true === $bool ? 'true' : 'false');
    }
}
