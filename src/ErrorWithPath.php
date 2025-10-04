<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

/**
 * @phpstan-type ErrorAsJson array{code: string, template: string, variables: array<string, mixed>}
 * @phpstan-type ErrorWithPathJson array{error: ErrorAsJson, path: string}
 */
final class ErrorWithPath implements \JsonSerializable
{
    public function __construct(public readonly string $path, public readonly Error $error) {}

    public function __toString()
    {
        return $this->path.': '.(string) $this->error;
    }

    /**
     * @return ErrorWithPathJson
     */
    public function jsonSerialize(): array
    {
        return ['path' => $this->path, 'error' => $this->error->jsonSerialize()];
    }
}
