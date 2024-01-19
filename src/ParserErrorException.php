<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class ParserErrorException extends \RuntimeException
{
    private array $errors = [];

    public function __construct(null|string $error = null, null|int|string $key = null)
    {
        if (null === $error) {
            return;
        }

        $this->addError($error, $key);
    }

    public function addParserErrorException(self $parserErrorException, null|int|string $key = null): self
    {
        if (null !== $key) {
            if (!isset($this->errors[$key])) {
                $this->errors[$key] = [];
            }

            foreach ($parserErrorException->getErrors() as $subPath => $error) {
                if (!isset($this->errors[$key][$subPath])) {
                    $this->errors[$key][$subPath] = [];
                }

                $this->errors[$key][$subPath] = $error;
            }

            return $this;
        }

        foreach ($parserErrorException->getErrors() as $subPath => $error) {
            $this->errors[$subPath] = $error;
        }

        return $this;
    }

    public function addError(string $error, null|int|string $key = null): self
    {
        if (null !== $key) {
            if (!isset($this->errors[$key])) {
                $this->errors[$key] = [];
            }

            $this->errors[$key][] = $error;

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
