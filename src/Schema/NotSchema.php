<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class NotSchema extends AbstractSchemaInnerParse
{
    public const string ERROR_MATCH_CODE = 'not.match';
    public const string ERROR_MATCH_TEMPLATE = 'Input should not match the given schema, {{given}} given';

    public function __construct(private SchemaInterface $schema) {}

    protected function innerParse(mixed $input): mixed
    {
        try {
            $this->schema->parse($input);
        } catch (ErrorsException) {
            /*
             * `not` is pure validation: the input is returned unchanged, the wrapped
             * schema's coercions/transformations never leak into the output.
             */
            return $input;
        }

        throw new ErrorsException(
            new Error(
                self::ERROR_MATCH_CODE,
                self::ERROR_MATCH_TEMPLATE,
                ['given' => $input]
            )
        );
    }
}
