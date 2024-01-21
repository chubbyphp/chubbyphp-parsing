<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Result;

interface SchemaInterface
{
    public function parse(mixed $input): mixed;

    public function safeParse(mixed $input): Result;

    /**
     * @param \Closure(mixed $input): mixed $transform
     */
    public function transform(\Closure $transform): static;

    public function default(mixed $default): static;

    /**
     * @param \Closure(mixed $input, ParserErrorException $parserErrorException): mixed $catch
     */
    public function catch(\Closure $catch): static;

    public function nullable(): static;
}
