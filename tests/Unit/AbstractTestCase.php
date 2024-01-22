<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected function errorsToSimpleArray(array $errors): array
    {
        return json_decode(json_encode($errors), true);
    }
}
