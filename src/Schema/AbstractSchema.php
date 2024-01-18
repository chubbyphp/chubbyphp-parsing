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
    protected mixed $catch = null;

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
        $this->transform[] = $transform;

        return $this;
    }

    final public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param \Closure(mixed $input, ParserErrorException $\parserErrorException): mixed $catch
     */
    final public function catch(\Closure $catch): static
    {
        $this->catch = $catch;

        return $this;
    }

    final public function nullable(): static
    {
        $this->nullable = true;

        return $this;
    }

    protected function transformOutput(mixed $output): mixed
    {
        foreach ($this->transform as $transform) {
            $output = $transform($output);
        }

        return $output;
    }
}
