<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;

final class ArraySchema extends AbstractSchema implements SchemaInterface
{
    public function __construct(private SchemaInterface $itemSchema) {}

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_array($input)) {
                throw new ParserErrorException(sprintf('Type should be "array" "%s" given', $this->getDataType($input)));
            }

            $output = [];

            $childrenParserErrorException = new ParserErrorException();

            foreach ($input as $i => $item) {
                try {
                    $output[$i] = $this->itemSchema->parse($item);
                } catch (ParserErrorException $childParserErrorException) {
                    $childrenParserErrorException->addParserErrorException($childParserErrorException, $i);
                }
            }

            if ($childrenParserErrorException->hasError()) {
                throw $childrenParserErrorException;
            }

            return $this->transformOutput($output);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }
}
