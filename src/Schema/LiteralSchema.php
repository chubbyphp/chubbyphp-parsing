<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;

final class LiteralSchema extends AbstractSchema implements LiteralSchemaInterface
{
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
                    sprintf('Type should be "bool|float|int|string" "%s" given', $this->getDataType($input))
                );
            }

            if ($input !== $this->literal) {
                throw new ParserErrorException(
                    sprintf(
                        'Input should be %s, %s given',
                        $this->formatValue($this->literal),
                        $this->formatValue($input),
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

    private function formatValue(bool|float|int|string $literal): string
    {
        if (\is_string($literal)) {
            return '"'.$literal.'"';
        }

        if (\is_float($literal) || \is_int($literal)) {
            return (string) $literal;
        }

        return true === $literal ? 'true' : 'false';
    }
}
