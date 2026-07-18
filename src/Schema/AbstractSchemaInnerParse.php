<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Result;

abstract class AbstractSchemaInnerParse implements SchemaInterface
{
    protected bool $nullable = false;

    /**
     * @var array<\Closure(mixed): mixed>
     */
    protected array $preParses = [];

    /**
     * @var array<\Closure>
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

    final public function default(mixed $default): static
    {
        return $this->preParse(static fn (mixed $input) => $input ?? $default);
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

    final public function postParse(\Closure $postParse): static
    {
        $clone = clone $this;
        $clone->postParses[] = $postParse;

        return $clone;
    }

    final public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            $output = $this->innerParse($input);

            return $this->dispatchPostParses($output);
        } catch (ErrorsException $e) {
            if ($this->catch) {
                return ($this->catch)($input, $e);
            }

            throw $e;
        }
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

    abstract protected function innerParse(mixed $input): mixed;

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

    /**
     * Normalizes a value so that json (schema spec) equal values share the same
     * representation: integral floats within the json safe integer range (2 ** 53, same as
     * Number.MAX_SAFE_INTEGER) are equal to their integer counterpart (1.0 equals 1),
     * objects (associative arrays) are sorted by their property names, as the property
     * order does not matter.
     */
    final protected static function normalizeJson(mixed $value): mixed
    {
        if (\is_float($value) && abs($value) <= 2 ** 53 && 0.0 === fmod($value, 1.0)) {
            return (int) $value;
        }

        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (\is_array($value)) {
            ksort($value);

            return array_map(self::normalizeJson(...), $value);
        }

        return $value;
    }

    final protected function getDataType(mixed $input): string
    {
        return \is_object($input) ? $input::class : \gettype($input);
    }
}
