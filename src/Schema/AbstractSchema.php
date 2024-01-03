<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParseErrorInterface;
use Chubbyphp\Parsing\Result;

abstract class AbstractSchema implements SchemaInterface
{
    /**
     * @var array<\Closure(mixed, array<ParseErrorInterface> &$parseError): mixed>
     */
    protected array $transform = [];

    protected mixed $default = null;

    /**
     * @var \Closure(mixed, ParseErrorInterface): mixed
     */
    protected mixed $catch = null;

    protected bool $nullable = false;

    final public function safeParse(mixed $input): Result
    {
        try {
            return new Result($this->parse($input), null);
        } catch (ParseErrorInterface $parseError) {
            return new Result(null, $parseError);
        }
    }

    /**
     * @param \Closure(mixed $input, array<ParseErrorInterface> &$parseError): mixed $transform
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
     * @param \Closure(mixed $input, ParseErrorInterface $parseError): mixed $catch
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
}
