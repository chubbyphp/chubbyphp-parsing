<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

/**
 * @method static optional(array<string> $optional)
 * @method static required(array<string> $required)
 * @method static additionalProperties(SchemaInterface $additionalFieldSchema)
 */
interface ObjectSchemaInterface extends SchemaInterface
{
    public function getFieldSchema(string $fieldName): ?SchemaInterface;

    /**
     * @param array<string> $strict
     */
    public function strict(array $strict = []): static;

    // /**
    //  * @deprecated use required() instead, fields not listed there are optional
    //  *
    //  * @param array<string> $optional
    //  */
    // public function optional(array $optional): static;

    // /**
    //  * @param array<string> $required
    //  */
    // public function required(array $required): static;

    // public function additionalProperties(SchemaInterface $additionalFieldSchema): static;
}
