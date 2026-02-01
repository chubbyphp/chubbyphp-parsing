<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Enum;

enum Uuid: int
{
    case v1 = 1; // Gregorian Time
    case v2 = 2; // DCE Security
    case v3 = 3; // MD5 Hash
    case v4 = 4; // Random
    case v5 = 5; // SHA-1 Hash
    case v6 = 6; // Reordered Time (Sortable)
    case v7 = 7; // Unix Epoch Time (Sortable)
    case v8 = 8; // Custom
}
