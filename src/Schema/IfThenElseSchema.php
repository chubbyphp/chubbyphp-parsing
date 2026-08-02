<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

final class IfThenElseSchema extends AbstractSchemaInnerParse
{
    private ?SchemaInterface $thenSchema = null;

    private ?SchemaInterface $elseSchema = null;

    public function __construct(private SchemaInterface $ifSchema) {}

    public function then(SchemaInterface $thenSchema): static
    {
        $clone = clone $this;
        $clone->thenSchema = $thenSchema;

        return $clone;
    }

    public function else(SchemaInterface $elseSchema): static
    {
        $clone = clone $this;
        $clone->elseSchema = $elseSchema;

        return $clone;
    }

    /*
     * The if schema is a pure condition (json schema spec: if/then/else): it only decides
     * which branch applies, its coercions/transformations never leak - the branch schema
     * receives the original input. The branch schema's output is the result; an absent
     * branch means no constraint, the input is returned unchanged (json schema `true`).
     * There are no own errors: a failing branch propagates its errors as they are, so the
     * instance paths stay correct.
     */
    protected function innerParse(mixed $input): mixed
    {
        $branchSchema = $this->ifSchema->safeParse($input)->success ? $this->thenSchema : $this->elseSchema;

        if (null === $branchSchema) {
            return $input;
        }

        return $branchSchema->parse($input);
    }
}
