<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Result;

interface SchemaInterface
{
    public function nullable(): static;

    public function default(mixed $default): static;

    /**
     * @param \Closure(mixed $input): mixed $middleware
     */
    public function middleware(\Closure $middleware): static;

    /**
     * @param \Closure(mixed $input, ParserErrorException $parserErrorException): mixed $catch
     */
    public function catch(\Closure $catch): static;

    public function parse(mixed $input): mixed;

    public function safeParse(mixed $input): Result;
}
