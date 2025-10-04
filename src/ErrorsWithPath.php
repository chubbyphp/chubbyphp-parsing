<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

/**
 * @phpstan-type ErrorAsJson array{code: string, template: string, variables: array<string, mixed>}
 * @phpstan-type ErrorWithPathJson array{error: ErrorAsJson, path: string}
 * @phpstan-type ErrorsWithPathJson array<ErrorWithPathJson>
 * @phpstan-type ApiProblem array{name: string, reason: string, details: non-empty-array<string, mixed>}
 */
final class ErrorsWithPath implements \JsonSerializable
{
    /**
     * @var array<ErrorWithPath>
     */
    private array $errorsWithPath = [];

    public function __construct(private string $path = '') {}

    public function __toString()
    {
        return implode(PHP_EOL, array_map(static fn (ErrorWithPath $errorWithPath) => (string) $errorWithPath, $this->errorsWithPath));
    }

    public function addErrorsWithPath(self $errorsWithPath): self
    {
        foreach ($errorsWithPath->errorsWithPath as $errorWithPath) {
            $this->addErrorWithPath($errorWithPath);
        }

        return $this;
    }

    public function addError(Error $error): self
    {
        $this->errorsWithPath[] = new ErrorWithPath($this->path, $error);

        return $this;
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
                $pathParts = explode('.', $errorWithPath->path);

                $current = &$tree;
                $lastIndex = \count($pathParts) - 1;

                foreach ($pathParts as $i => $pathPart) {
                    if ($i < $lastIndex) {
                        $current[$pathPart] ??= [];
                        $current = &$current[$pathPart];

                        continue;
                    }

                    $current[$pathPart] = array_merge($current[$pathPart] ?? [], [(string) $errorWithPath->error]);
                }

                return $tree;
            },
            []
        );
    }

    /**
     * @return array<ApiProblem>
     */
    public function toApiProblems(): array
    {
        return array_map(
            fn (ErrorWithPath $errorWithPath) => [
                'name' => $this->pathToName($errorWithPath->path),
                'reason' => (string) $errorWithPath->error,
                'details' => [
                    '_template' => $errorWithPath->error->template,
                    ...$errorWithPath->error->variables,
                ],
            ],
            $this->errorsWithPath
        );
    }

    /**
     * @return ErrorsWithPathJson
     */
    public function jsonSerialize(): array
    {
        return array_map(static fn (ErrorWithPath $errorWithPath) => $errorWithPath->jsonSerialize(), $this->errorsWithPath);
    }

    private function addErrorWithPath(ErrorWithPath $errorWithPath): self
    {
        $this->errorsWithPath[] = new ErrorWithPath($this->mergePath($this->path, $errorWithPath->path), $errorWithPath->error);

        return $this;
    }

    private function mergePath(string $path, string $existingPath): string
    {
        return '' === $path ? $existingPath : $path.'.'.$existingPath;
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
