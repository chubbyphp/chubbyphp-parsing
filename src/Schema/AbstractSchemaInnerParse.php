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

    final protected function getDataType(mixed $input): string
    {
        return \is_object($input) ? $input::class : \gettype($input);
    }

    protected function varExport(mixed $input): string
    {
        if ($input instanceof \DateTimeInterface) {
            return 'new \DateTimeImmutable(\''.$input->format('c').'\')';
        }

        if ($input instanceof \BackedEnum) {
            return '\\'.$input::class.'::from('.$this->varExport($input->value).')';
        }

        if ($input instanceof \stdClass) {
            return '(object) '.$this->varExport((array) $input);
        }

        if (\is_array($input)) {
            return '['.implode(', ', array_map(
                fn (int|string $key, mixed $value) => $this->varExport($key).' => '.$this->varExport($value),
                array_keys($input),
                $input
            )).']';
        }

        if (null === $input) {
            return 'null';
        }

        return var_export($input, true);
    }
}
