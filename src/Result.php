<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class Result
{
    public bool $success;

    public function __construct(public mixed $data, public null|ParserErrorException $exception)
    {
        $this->success = null === $exception;
    }
}
