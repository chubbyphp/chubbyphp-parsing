<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

interface ObjectSchemaInterface extends SchemaInterface
{
    public function getFieldSchema(string $fieldName): ?SchemaInterface;

    /**
     * @param array<string> $strict
     */
    public function strict(array $strict = []): static;

    /**
     * @param array<string> $required
     */
    public function required(array $required): static;

    public function additionalProperties(SchemaInterface $additionalFieldSchema): static;
}
