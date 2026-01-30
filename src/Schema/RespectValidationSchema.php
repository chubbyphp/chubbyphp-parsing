<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validatable;

final class RespectValidationSchema extends AbstractSchemaV2 implements SchemaInterface
{
    public function __construct(private Validatable $validatable) {}

    protected function innerParse(mixed $input): mixed
    {
        try {
            $this->validatable->assert($input);

            return $input;
        } catch (ValidationException $e) {
            throw $this->convertException($e);
        }
    }

    private function convertException(NestedValidationException|ValidationException $e): ErrorsException
    {
        if ($e instanceof NestedValidationException) {
            $errors = new Errors();
            foreach ($e->getChildren() as $child) {
                $errors->add($this->convertException($child)->errors);
            }

            return new ErrorsException($errors);
        }

        return new ErrorsException(
            new Error(
                $e->getId(),
                $e->getMessage(),
                $e->getParams(),
            )
        );
    }
}
