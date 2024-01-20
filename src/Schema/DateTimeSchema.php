<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;

final class DateTimeSchema extends AbstractSchema implements SchemaInterface
{
    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!$input instanceof \DateTimeInterface) {
                throw new ParserErrorException(
                    sprintf('Type should be "%s" "%s" given', \DateTimeInterface::class, $this->getDataType($input))
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
