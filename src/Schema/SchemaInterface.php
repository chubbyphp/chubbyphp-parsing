<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Result;

interface SchemaInterface
{
    public function nullable(): static;

    /**
     * @param \Closure(mixed $input): mixed $preParse
     */
    public function preParse(\Closure $preParse): static;

    /**
     * @param \Closure(mixed $input): mixed $postParse
     */
    public function postParse(\Closure $postParse): static;

    public function parse(mixed $input): mixed;

    public function safeParse(mixed $input): Result;

    /**
     * @param \Closure(mixed $input, ParserErrorException $parserErrorException): mixed $catch
     */
    public function catch(\Closure $catch): static;
}
