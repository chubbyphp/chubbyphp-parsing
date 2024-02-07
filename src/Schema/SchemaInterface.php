<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Result;

interface SchemaInterface
{
    public function nullable(): static;

    /**
     * @param \Closure(mixed $input): mixed $preMiddleware
     */
    public function preMiddleware(\Closure $preMiddleware): static;

    /**
     * @param \Closure(mixed $input): mixed $postMiddleware
     */
    public function postMiddleware(\Closure $postMiddleware): static;

    /**
     * @param \Closure(mixed $input, ParserErrorException $parserErrorException): mixed $catch
     */
    public function catch(\Closure $catch): static;

    public function parse(mixed $input): mixed;

    public function safeParse(mixed $input): Result;
}
