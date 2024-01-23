<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Result;

abstract class AbstractSchema implements SchemaInterface
{
    /**
     * @var array<\Closure(mixed): mixed>
     */
    protected array $transform = [];

    protected mixed $default = null;

    /**
     * @var \Closure(mixed, ParserErrorException): mixed
     */
    protected null|\Closure $catch = null;

    protected bool $nullable = false;

    final public function safeParse(mixed $input): Result
    {
        try {
            return new Result($this->parse($input), null);
        } catch (ParserErrorException $parserErrorException) {
            return new Result(null, $parserErrorException);
        }
    }

    /**
     * @param \Closure(mixed $input): mixed $transform
     */
    final public function transform(\Closure $transform): static
    {
        $clone = clone $this;

        $clone->transform[] = $transform;

        return $clone;
    }

    final public function default(mixed $default): static
    {
        $clone = clone $this;

        $clone->default = $default;

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

    final public function nullable(): static
    {
        $clone = clone $this;

        $clone->nullable = true;

        return $clone;
    }

    protected function transformOutput(mixed $output): mixed
    {
        foreach ($this->transform as $transform) {
            $output = $transform($output);
        }

        return $output;
    }

    protected function getDataType(mixed $input): string
    {
        return \is_object($input) ? $input::class : \gettype($input);
    }
}
