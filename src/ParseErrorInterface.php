<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

interface ParseErrorInterface extends \Throwable
{
    public function __toString(): string;

    /**
     * @return array<ParseErrorInterface>
     */
    public function getParseErrors(): array;

    public function getData(): array|string;
}
