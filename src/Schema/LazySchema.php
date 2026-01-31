<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Result;

final class LazySchema implements SchemaInterface
{
    private ?SchemaInterface $schema = null;

    /**
     * @param \Closure(): SchemaInterface $schemaFactory
     */
    public function __construct(private \Closure $schemaFactory) {}

    /**
     * @internal
     *
     * @infection-ignore-all
     */
    public function nullable(bool $nullable = true): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "nullable" called.');
    }

    /**
     * @internal
     *
     * @infection-ignore-all
     */
    public function default(mixed $default): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "default" called.');
    }

    /**
     * @internal
     *
     * @infection-ignore-all
     */
    public function preParse(\Closure $preParse): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "preParse" called.');
    }

    /**
     * @internal
     *
     * @infection-ignore-all
     */
    public function postParse(\Closure $postParse): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "postParse" called.');
    }

    /**
     * internal.
     *
     * @param \Closure(mixed $output): bool $refine
     * @param array<string, mixed>          $variables
     */
    public function refine(\Closure $refine, string $code, string $template, array $variables = []): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "refine" called.');
    }

    public function parse(mixed $input): mixed
    {
        $schema = $this->resolveSchema();

        return $schema->parse($input);
    }

    public function safeParse(mixed $input): Result
    {
        $schema = $this->resolveSchema();

        return $schema->safeParse($input);
    }

    /**
     * @internal
     */
    public function catch(\Closure $catch): static
    {
        throw new \BadMethodCallException('LazySchema does not support any modification, "catch" called.');
    }

    private function resolveSchema(): SchemaInterface
    {
        if (!$this->schema) {
            $this->schema = ($this->schemaFactory)();
        }

        return $this->schema;
    }
}
