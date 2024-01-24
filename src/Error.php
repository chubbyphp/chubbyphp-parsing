<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class Error
{
    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(public string $code, public string $template, public array $variables) {}

    public function __toString()
    {
        $message = $this->template;
        foreach ($this->variables as $name => $value) {
            $encodedValue = json_encode($value);
            $message = str_replace('{{'.$name.'}}', false !== $encodedValue ? $encodedValue : '<cannot_be_encoded>', $message);
        }

        return $message;
    }
}
