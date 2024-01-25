<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Result;

abstract class AbstractSchema implements SchemaInterface
{
    protected bool $nullable = false;

    protected mixed $default = null;

    /**
     * @var array<\Closure(mixed): mixed>
     */
    protected array $middlewares = [];

    /**
     * @var \Closure(mixed, ParserErrorException): mixed
     */
    protected null|\Closure $catch = null;

    final public function nullable(): static
    {
        $clone = clone $this;

        $clone->nullable = true;

        return $clone;
    }

    final public function default(mixed $default): static
    {
        $clone = clone $this;

        $clone->default = $default;

        return $clone;
    }

    /**
     * @param \Closure(mixed $input): mixed $middleware
     */
    final public function middleware(\Closure $middleware): static
    {
        $clone = clone $this;

        $clone->middlewares[] = $middleware;

        return $clone;
    }

    /**
     * @param \Closure(mixed $input, ParserErrorException $parserErrorException): mixed $catch
     */
    final public function catch(\Closure $catch): static
    {
        $clone = clone $this;

        $clone->catch = $catch;

        return $clone;
    }

    final public function safeParse(mixed $input): Result
    {
        try {
            return new Result($this->parse($input), null);
        } catch (ParserErrorException $parserErrorException) {
            return new Result(null, $parserErrorException);
        }
    }

    final protected function dispatchMiddlewares(mixed $data): mixed
    {
        foreach ($this->middlewares as $middleware) {
            $data = $middleware($data);
        }

        return $data;
    }

    final protected function getDataType(mixed $input): string
    {
        return \is_object($input) ? $input::class : \gettype($input);
    }
}
