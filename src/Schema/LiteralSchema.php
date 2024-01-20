<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;

final class LiteralSchema extends AbstractSchema implements LiteralSchemaInterface
{
    public function __construct(private string $literal) {}

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_string($input)) {
                throw new ParserErrorException(sprintf("Type should be 'string' '%s' given", $this->getDataType($input)));
            }

            if ($input !== $this->literal) {
                throw new ParserErrorException(sprintf("Input should be '%s' '%s' given", $this->literal, $input));
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
