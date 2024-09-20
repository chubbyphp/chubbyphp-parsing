<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

/**
 * @internal
 */
final class ParserErrorExceptionToString implements \Stringable
{
    public function __construct(private ParserErrorException $e) {}

    public function __toString(): string
    {
        /** @var array<string> */
        $lines = [];

        foreach ($this->e->getApiProblemErrorMessages() as $apiProblemErrorMessage) {
            $lines[] = "{$apiProblemErrorMessage['name']}: {$apiProblemErrorMessage['reason']}";
        }

        return implode(PHP_EOL, $lines);
    }
}
