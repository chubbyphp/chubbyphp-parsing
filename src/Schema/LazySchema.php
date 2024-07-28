<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Result;

/**
 * @phpstan-type Call array{name: string, arguments: array<mixed>}
 */
final class LazySchema implements SchemaInterface
{
    private ?SchemaInterface $schema = null;

    /**
     * @param \Closure(): SchemaInterface $lazy
     */
    public function __construct(private \Closure $lazy) {}

    public function nullable(bool $nullable = true): static
    {
        throw new \BadMethodCallException(
            \sprintf(
                'LazySchema does not support any modification, "nullable" called with %s.',
                $nullable ? 'true' : 'false'
            )
        );
    }

    /**
     * @param \Closure(mixed $input): mixed $preParse
     */
    public function preParse(\Closure $preParse): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "preParse" called.');
    }

    /**
     * @param \Closure(mixed $input): mixed $postParse
     */
    public function postParse(\Closure $postParse): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "postParse" called.');
    }

    /**
     * @param \Closure(mixed $input, ParserErrorException $parserErrorException): mixed $catch
     */
    public function catch(\Closure $catch): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "catch" called.');
    }

    public function parse(mixed $input): mixed
    {
        $schema = $this->resolveSchema();

        return $schema->parse($input);
    }

    public function safeParse(mixed $input): Result
    {
        $schema = $this->resolveSchema();

        return $schema->safeParse($input);
    }

    private function resolveSchema(): SchemaInterface
    {
        if (!$this->schema) {
            $this->schema = ($this->lazy)();
        }

        return $this->schema;
    }
}
