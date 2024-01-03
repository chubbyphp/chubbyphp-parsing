<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParseError;
use Chubbyphp\Parsing\ParseErrorInterface;
use Chubbyphp\Parsing\ParseErrors;

final class LiteralSchema extends AbstractSchema
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
                throw new ParseError(sprintf("Type should be 'string' '%s' given", \gettype($input)));
            }

            if ($input !== $this->literal) {
                throw new ParseError(sprintf("Input should be '%s' '%s' given", $this->literal, $input));
            }

            $output = $input;

            /** @var array<ParseErrorInterface> $parseErrors */
            $parseErrors = [];

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
