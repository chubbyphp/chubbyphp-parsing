<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;

final class BoolSchema extends AbstractSchema implements SchemaInterface
{
    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_bool($input)) {
                throw new ParserErrorException(sprintf('Type should be "bool" "%s" given', $this->getDataType($input)));
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