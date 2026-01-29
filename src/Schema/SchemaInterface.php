<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Result;

/**
 * @method static default(mixed $default)
 */
interface SchemaInterface
{
    public function nullable(bool $nullable = true): static;

    // public function default(mixed $default): static;

    /**
     * @param \Closure(mixed $input): mixed $preParse
     */
    public function preParse(\Closure $preParse): static;

    public function postParse(\Closure $postParse): static;

    public function parse(mixed $input): mixed;

    public function safeParse(mixed $input): Result;

    /**
     * @param \Closure(mixed $input, ErrorsException $e): mixed $catch
     */
    public function catch(\Closure $catch): static;
}
