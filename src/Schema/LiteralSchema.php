<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class LiteralSchema extends AbstractSchema implements LiteralSchemaInterface
{
    public const ERROR_TYPE_CODE = 'literal.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "bool|float|int|string", "{{given}}" given';

    public const ERROR_EQUALS_CODE = 'literal.equals';
    public const ERROR_EQUALS_TEMPLATE = 'Input should be {{expected}}, {{given}} given';

    public function __construct(private bool|float|int|string $literal) {}

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_bool($input) && !\is_float($input) && !\is_int($input) && !\is_string($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            if ($input !== $this->literal) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_EQUALS_CODE,
                        self::ERROR_EQUALS_TEMPLATE,
                        ['expected' => $this->literal, 'given' => $input]
                    )
                );
            }

            return $this->transformOutput($input);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }
}
