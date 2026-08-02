<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

final class AnySchema extends AbstractSchemaInnerParse
{
    /*
     * Accepts every input, including null, and returns it unchanged (json schema spec:
     * the boolean schema `true` / the empty schema `{}`). The reject-everything
     * counterpart (the boolean schema `false`) is `not(any())`.
     */
    protected function innerParse(mixed $input): mixed
    {
        return $input;
    }
}
