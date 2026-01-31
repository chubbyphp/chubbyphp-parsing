<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ErrorsException;
use Chubbyphp\Parsing\Result;

/**
 * @method static default(mixed $default)
 * @method static refine(\Closure(mixed $output): bool $refine, string $code, string $template, array<string, mixed> $variables = [])
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

    // /**
    //  * @param \Closure(mixed $output): bool $refine
    //  * @param array<string, mixed> $variables
    //  */
    // public function refine(\Closure $refine, string $code, string $template, array $variables = []): static;

    public function parse(mixed $input): mixed;

    public function safeParse(mixed $input): Result;

    /**
     * @param \Closure(mixed $input, ErrorsException $e): mixed $catch
     */
    public function catch(\Closure $catch): static;
}
