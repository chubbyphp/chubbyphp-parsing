<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

/**
 * @phpstan-type ErrorAsJson array{code: string, template: string, variables: array<string, mixed>}
 */
final class Error implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(public readonly string $code, public readonly string $template, public readonly array $variables) {}

    public function __toString()
    {
        $message = $this->template;
        foreach ($this->variables as $name => $value) {
            $encodedValue = json_encode($value);
            $message = str_replace(
                '{{'.$name.'}}',
                false !== $encodedValue ? $encodedValue : '<cannot_be_encoded>',
                $message
            );
        }

        return $message;
    }

    /**
     * @return ErrorAsJson
     */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'template' => $this->template,
            'variables' => json_decode(json_encode($this->variables, JSON_THROW_ON_ERROR), true),
        ];
    }
}
