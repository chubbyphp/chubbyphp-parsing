<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

/**
 * @method static optional(array<string> $optional)
 */
interface ObjectSchemaInterface extends SchemaInterface
{
    public function getFieldSchema(string $fieldName): ?SchemaInterface;

    /**
     * @param array<string> $strict
     */
    public function strict(array $strict = []): static;

    // /**
    //  * @param array<string> $optional
    //  */
    // public function optional(array $optional): static;
}
