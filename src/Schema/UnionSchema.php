<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParseErrorInterface;
use Chubbyphp\Parsing\ParseErrors;

final class UnionSchema extends AbstractSchema
{
    /**
     * @var array<SchemaInterface>
     */
    private array $schemas;

    /**
     * @param array<SchemaInterface> $schemas
     */
    public function __construct(array $schemas)
    {
        foreach ($schemas as $i => $schema) {
            if (!$schema instanceof SchemaInterface) {
                $type = \is_object($schema) ? $schema::class : \gettype($schema);

                throw new \InvalidArgumentException(sprintf('Argument #1 value of #%s ($schemas) must be of type SchemaInterface, %s given', (string) $i, $type));
            }
        }

        $this->schemas = $schemas;
    }

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            $output = $this->parseSchemas($input);

            /** @var array<ParseErrorInterface> $parseErrors */
            $parseErrors = [];

            foreach ($this->transform as $transform) {
                $output = $transform($output, $parseErrors);
            }

            if (\count($parseErrors)) {
                throw new ParseErrors($parseErrors);
            }

            return $output;
        } catch (ParseErrorInterface $parseError) {
            if ($this->catch) {
                return ($this->catch)($input, $parseError);
            }

            throw $parseError;
        }
    }

    private function parseSchemas(mixed $input)
    {
        /** @var array<ParseErrorInterface> $parseErrors */
        $parseErrors = [];

        foreach ($this->schemas as $schema) {
            try {
                return $schema->parse($input);
            } catch (ParseErrorInterface $parseError) {
                $parseErrors = array_merge($parseErrors, $parseError->getParseErrors());
            }
        }

        throw new ParseErrors($parseErrors);
    }
}
