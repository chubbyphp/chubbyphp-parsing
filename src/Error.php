<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class Error
{
    /**
     * @param array<string, bool|float|int|string|\Stringable> $variables
     */
    public function __construct(public string $code, public string $template, public array $variables) {}

    public function __toString()
    {
        $message = $this->template;
        foreach ($this->variables as $name => $value) {
            $message = str_replace('{{'.$name.'}}', $this->formatValue($value), $message);
        }

        return $message;
    }

    private function formatValue(bool|float|int|string|\Stringable $value): string
    {
        if (\is_string($value) || $value instanceof \Stringable) {
            return '"'.$value.'"';
        }

        if (\is_float($value) || \is_int($value)) {
            return (string) $value;
        }

        return true === $value ? 'true' : 'false';
    }
}
