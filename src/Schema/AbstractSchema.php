<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ErrorsException;
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
     * @var \Closure(mixed, ErrorsException): mixed
     */
    protected ?\Closure $catch = null;

    final public function nullable(bool $nullable = true): static
    {
        $clone = clone $this;

        $clone->nullable = $nullable;

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
        } catch (ErrorsException $e) {
            return new Result(null, $e);
        }
    }

    /**
     * @param \Closure(mixed $input, ErrorsException $e): mixed $catch
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
        return array_reduce(
            $this->preParses,
            static fn (mixed $currentData, \Closure $preParse) => $preParse($currentData),
            $data
        );
    }

    final protected function dispatchPostParses(mixed $data): mixed
    {
        return array_reduce(
            $this->postParses,
            static fn (mixed $currentData, \Closure $postParse) => $postParse($currentData),
            $data
        );
    }

    final protected function getDataType(mixed $input): string
    {
        return \is_object($input) ? $input::class : \gettype($input);
    }
}
