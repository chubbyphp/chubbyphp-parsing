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
    protected array $preParses = [];

    /**
     * @var array<\Closure(mixed): mixed>
     */
    protected array $postParses = [];

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
     * @param \Closure(mixed $input): mixed $preParse
     */
    final public function preParse(\Closure $preParse): static
    {
        $clone = clone $this;

        $clone->preParses[] = $preParse;

        return $clone;
    }

    /**
     * @param \Closure(mixed $input): mixed $postParse
     */
    final public function postParse(\Closure $postParse): static
    {
        $clone = clone $this;

        $clone->postParses[] = $postParse;

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

    /**
     * @param \Closure(mixed $input, ParserErrorException $parserErrorException): mixed $catch
     */
    final public function catch(\Closure $catch): static
    {
        $clone = clone $this;

        $clone->catch = $catch;

        return $clone;
    }

    final public function default(mixed $default): static
    {
        return $this->preParse(static fn (mixed $input) => $input ?? $default);
    }

    final protected function dispatchPreParses(mixed $data): mixed
    {
        foreach ($this->preParses as $preParse) {
            $data = $preParse($data);
        }

        return $data;
    }

    final protected function dispatchPostParses(mixed $data): mixed
    {
        foreach ($this->postParses as $postParse) {
            $data = $postParse($data);
        }

        return $data;
    }

    final protected function getDataType(mixed $input): string
    {
        return \is_object($input) ? $input::class : \gettype($input);
    }
}
