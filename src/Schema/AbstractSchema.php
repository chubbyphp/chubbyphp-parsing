<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Result;

abstract class AbstractSchema implements SchemaInterface
{
    protected bool $nullable = false;

    /**
     * @var array<\Closure(mixed): mixed>
     */
    protected array $preMiddlewares = [];

    /**
     * @var array<\Closure(mixed): mixed>
     */
    protected array $postMiddlewares = [];

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

    /**
     * @param \Closure(mixed $input): mixed $preMiddleware
     */
    final public function preMiddleware(\Closure $preMiddleware): static
    {
        $clone = clone $this;

        $clone->preMiddlewares[] = $preMiddleware;

        return $clone;
    }

    /**
     * @param \Closure(mixed $input): mixed $postMiddleware
     */
    final public function postMiddleware(\Closure $postMiddleware): static
    {
        $clone = clone $this;

        $clone->postMiddlewares[] = $postMiddleware;

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

    final public function default(mixed $default): static
    {
        return $this->preMiddleware(static fn (mixed $input) => $input ?? $default);
    }

    final protected function dispatchPreMiddlewares(mixed $data): mixed
    {
        foreach ($this->preMiddlewares as $preMiddleware) {
            $data = $preMiddleware($data);
        }

        return $data;
    }

    final protected function dispatchPostMiddlewares(mixed $data): mixed
    {
        foreach ($this->postMiddlewares as $postMiddleware) {
            $data = $postMiddleware($data);
        }

        return $data;
    }

    final protected function getDataType(mixed $input): string
    {
        return \is_object($input) ? $input::class : \gettype($input);
    }
}
