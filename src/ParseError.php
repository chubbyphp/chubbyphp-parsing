<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class ParseError extends \RuntimeException implements ParseErrorInterface
{
    public function __construct(private string $data) {}

    public function __toString(): string
    {
        return $this->getData();
    }

    /**
     * @return array<ParseErrorInterface>
     */
    public function getParseErrors(): array
    {
        return [$this];
    }

    public function getData(): array|string
    {
        return $this->data;
    }
}
