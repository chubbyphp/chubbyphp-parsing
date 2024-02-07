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
    private null|SchemaInterface $schema = null;

    /**
     * @param \Closure(): SchemaInterface $lazy
     */
    public function __construct(private \Closure $lazy) {}

    public function nullable(): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "nullable" called.');
    }

    /**
     * @param \Closure(mixed $input): mixed $preMiddleware
     */
    public function preMiddleware(\Closure $preMiddleware): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "preMiddleware" called.');
    }

    /**
     * @param \Closure(mixed $input): mixed $postMiddleware
     */
    public function postMiddleware(\Closure $postMiddleware): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "postMiddleware" called.');
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
