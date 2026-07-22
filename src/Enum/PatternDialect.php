<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Enum;

enum PatternDialect
{
    case pcre; // delimited PCRE, passed to preg_match as is, example: '/^[a-z]+$/i'
    case ecma262; // delimiter-less ECMA-262 (json schema "pattern"), translated to PCRE, example: '^[a-z]+$'
}
