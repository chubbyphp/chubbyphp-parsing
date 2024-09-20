<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

/**
 * @phpstan-type ApiProblem array{name: string, reason: string, details: non-empty-array<string, mixed>}
 */
final class ParserErrorException extends \RuntimeException
{
    private array $errors = [];

    public function __construct(?Error $error = null, null|int|string $key = null)
    {
        $this->message = $this->messageStringable();

        if ($error) {
            $this->addError($error, $key);
        }
    }

    public function __toString(): string
    {
        return self::class;
    }

    public function addParserErrorException(self $parserErrorException, null|int|string $key = null): self
    {
        if (null !== $key) {
            $this->errors = $this->mergeErrors([$key => $parserErrorException->getErrors()], $this->errors);

            return $this;
        }

        $this->errors = $this->mergeErrors($parserErrorException->getErrors(), $this->errors);

        return $this;
    }

    public function addError(Error $error, null|int|string $key = null): self
    {
        if (null !== $key) {
            $this->errors = $this->mergeErrors([$key => [$error]], $this->errors);

            return $this;
        }

        $this->errors = $this->mergeErrors([$error], $this->errors);

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

    /**
     * @return array<ApiProblem>
     */
    public function getApiProblemErrorMessages(): array
    {
        return $this->flatErrorsToApiProblemMessages($this->errors);
    }

    private function messageStringable(): \Stringable
    {
        return new class($this) implements \Stringable {
            public function __construct(private ParserErrorException $e) {}

            public function __toString(): string
            {
                $lines = [];
                foreach ($this->e->getApiProblemErrorMessages() as $apiProblemErrorMessage) {
                    $lines[] = "{$apiProblemErrorMessage['name']}: {$apiProblemErrorMessage['reason']}";
                }

                return implode(PHP_EOL, $lines);
            }
        };
    }

    private function mergeErrors(array $errors, array $mergedErrors): array
    {
        foreach ($errors as $key => $error) {
            if ($error instanceof Error) {
                $mergedErrors[] = $error;
            } else {
                $mergedErrors[$key] = $this->mergeErrors($error, $mergedErrors[$key] ?? []);
            }
        }

        return $mergedErrors;
    }

    /**
     * @return array<ApiProblem>
     */
    private function flatErrorsToApiProblemMessages(array $errors, string $path = ''): array
    {
        /** @var array<ApiProblem> */
        $errorsToApiProblemMessages = [];

        foreach ($errors as $key => $error) {
            if ($error instanceof Error) {
                $errorsToApiProblemMessages[] = [
                    'name' => $path,
                    'reason' => (string) $error,
                    'details' => [
                        '_template' => $error->template,
                        ...$error->variables,
                    ],
                ];
            } else {
                $errorsToApiProblemMessages = array_merge(
                    $errorsToApiProblemMessages,
                    $this->flatErrorsToApiProblemMessages(
                        $error,
                        '' === $path ? $key : $path.'['.$key.']'
                    )
                );
            }
        }

        return $errorsToApiProblemMessages;
    }
}
