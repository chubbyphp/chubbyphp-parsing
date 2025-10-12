<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

/**
 * @phpstan-type ErrorWithPath array{path: string, error: Error}
 * @phpstan-type ErrorsWithPath array<ErrorWithPath>
 * @phpstan-type ErrorAsJson array{code: string, template: string, variables: array<string, mixed>}
 * @phpstan-type ErrorWithPathJson array{path: string, error: ErrorAsJson}
 * @phpstan-type ErrorsWithPathJson array<ErrorWithPathJson>
 * @phpstan-type ApiProblemInvalidParameter array{name: string, reason: string, details: non-empty-array<string, mixed>}
 */
final class Errors implements \JsonSerializable
{
    /**
     * @var ErrorsWithPath
     */
    private array $errorsWithPath = [];

    public function __toString()
    {
        return implode(PHP_EOL, array_map(static fn ($error) => ('' !== $error['path'] ? $error['path'].': ' : '').$error['error'], $this->errorsWithPath));
    }

    public function add(Error|self $errors, string $path = ''): self
    {
        if ($errors instanceof self) {
            foreach ($errors->errorsWithPath as $errorWithPath) {
                $this->errorsWithPath[] = ['path' => $this->mergePath($path, $errorWithPath['path']), 'error' => $errorWithPath['error']];
            }

            return $this;
        }

        $this->errorsWithPath[] = ['path' => $path, 'error' => $errors];

        return $this;
    }

    public function has(): bool
    {
        return 0 !== \count($this->errorsWithPath);
    }

    /**
     * @return ErrorsWithPathJson
     */
    public function jsonSerialize(): array
    {
        return array_map(static fn ($errorWithPath) => [
            'path' => $errorWithPath['path'],
            'error' => $errorWithPath['error']->jsonSerialize(),
        ], $this->errorsWithPath);
    }

    /**
     * @return array<ApiProblemInvalidParameter>
     */
    public function toApiProblemInvalidParameters(): array
    {
        return array_map(
            fn ($errorWithPath) => [
                'name' => $this->pathToName($errorWithPath['path']),
                'reason' => (string) $errorWithPath['error'],
                'details' => [
                    '_template' => $errorWithPath['error']->template,
                    ...$errorWithPath['error']->variables,
                ],
            ],
            $this->errorsWithPath
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toTree(): array
    {
        // @var array<string, mixed> $tree
        return array_reduce(
            $this->errorsWithPath,
            static function (array $tree, $errorWithPath): array {
                $pathParts = explode('.', $errorWithPath['path']);

                $current = &$tree;
                $lastIndex = \count($pathParts) - 1;

                foreach ($pathParts as $i => $pathPart) {
                    if ($i < $lastIndex) {
                        $current[$pathPart] ??= [];
                        $current = &$current[$pathPart];

                        continue;
                    }

                    $current[$pathPart] = array_merge($current[$pathPart] ?? [], [(string) $errorWithPath['error']]);
                }

                return $tree;
            },
            []
        );
    }

    private function mergePath(string $path, string $existingPath): string
    {
        return implode('.', array_filter([$path, $existingPath], static fn ($part) => '' !== $part));
    }

    private function pathToName(string $path): string
    {
        $pathParts = explode('.', $path);

        return implode('', array_map(
            static fn (string $pathPart, $i) => 0 === $i ? $pathPart : '['.$pathPart.']',
            $pathParts,
            array_keys($pathParts)
        ));
    }
}
