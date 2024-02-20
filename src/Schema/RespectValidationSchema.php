<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validatable;

final class RespectValidationSchema extends AbstractSchema implements SchemaInterface
{
    public function __construct(private Validatable $validatable) {}

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            try {
                $this->validatable->assert($input);

                return $this->dispatchPostParses($input);
            } catch (ValidationException $e) {
                throw $this->convertException($e);
            }
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    private function convertException(NestedValidationException|ValidationException $validationException): ParserErrorException
    {
        if ($validationException instanceof NestedValidationException) {
            $parserErrorException = new ParserErrorException();
            foreach ($validationException->getChildren() as $childValidationException) {
                $parserErrorException->addParserErrorException($this->convertException($childValidationException));
            }

            return $parserErrorException;
        }

        return new ParserErrorException(
            new Error(
                $validationException->getId(),
                $validationException->getMessage(),
                $validationException->getParams(),
            )
        );
    }
}
