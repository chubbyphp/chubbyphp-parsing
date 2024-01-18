<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class ParserErrorException extends \RuntimeException
{
    private array $errors = [];

    public function __construct(null|string $error = null, null|int|string $path = null)
    {
        if (null === $error) {
            return;
        }

        $this->addError($error, $path);
    }

    public function addParserErrorException(self $parserErrorException, null|int|string $path = null): self
    {
        if (null !== $path) {
            if (!isset($this->errors[$path])) {
                $this->errors[$path] = [];
            }

            foreach ($parserErrorException->getErrors() as $subPath => $error) {
                if (!isset($this->errors[$path][$subPath])) {
                    $this->errors[$path][$subPath] = [];
                }

                $this->errors[$path][$subPath] = $error;
            }

            return $this;
        }

        foreach ($parserErrorException->getErrors() as $subPath => $error) {
            $this->errors[$subPath] = $error;
        }

        return $this;
    }

    public function addError(string $error, null|int|string $path = null): self
    {
        if (null !== $path) {
            if (!isset($this->errors[$path])) {
                $this->errors[$path] = [];
            }

            $this->errors[$path][] = $error;

            return $this;
        }

        $this->errors[] = $error;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasError(): bool
    {
        return 0 !== \count($this->errors);
    }
}
