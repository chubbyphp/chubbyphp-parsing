<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class ErrorsException extends \RuntimeException
{
    public readonly Errors $errors;

    public function __construct(Error|Errors $errorsOrError)
    {
        $errors = $errorsOrError instanceof Errors ? $errorsOrError : (new Errors())->add($errorsOrError);

        $this->errors = $errors;
        parent::__construct((string) $errors);
    }
}
