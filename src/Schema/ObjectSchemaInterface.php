<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

interface ObjectSchemaInterface extends SchemaInterface
{
    public function getFieldSchema(string $fieldName): null|SchemaInterface;
}
