<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParseError;
use Chubbyphp\Parsing\ParseErrorInterface;
use Chubbyphp\Parsing\ParseErrors;

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
                throw new ParseError(sprintf("Input needs to be array, '%s'", \gettype($input)));
            }

            $output = [];

            /** @var array<ParseErrorInterface> $parseErrors */
            $parseErrors = [];

            foreach ($input as $i => $item) {
                try {
                    $output[$i] = $this->itemSchema->parse($item);
                } catch (ParseErrorInterface $parseError) {
                    $parseErrors[$i] = $parseError;
                }
            }

            foreach ($this->transform as $transform) {
                $output = $transform($output, $parseErrors);
            }

            if (\count($parseErrors)) {
                throw new ParseErrors($parseErrors);
            }

            return $output;
        } catch (ParseErrorInterface $parseError) {
            if ($this->catch) {
                return ($this->catch)($input, $parseError);
            }

            throw $parseError;
        }
    }
}
