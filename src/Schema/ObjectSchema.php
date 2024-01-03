<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParseError;
use Chubbyphp\Parsing\ParseErrorInterface;
use Chubbyphp\Parsing\ParseErrors;

final class ObjectSchema extends AbstractSchema
{
    /**
     * @var array<string, SchemaInterface>
     */
    public readonly array $fieldSchemas;

    /**
     * @param array<string, SchemaInterface> $fieldSchemas
     * @param class-string                   $classname
     */
    public function __construct(array $fieldSchemas, private string $classname)
    {
        foreach ($fieldSchemas as $name => $fieldSchema) {
            if (!\is_string($name)) {
                $type = \is_object($name) ? $name::class : \gettype($name);

                throw new \InvalidArgumentException(sprintf('Argument #1 name #%s ($objectSchema) must be of type string, %s given', (string) $name, $type));
            }

            if (!$fieldSchema instanceof SchemaInterface) {
                $type = \is_object($fieldSchema) ? $fieldSchema::class : \gettype($fieldSchema);

                throw new \InvalidArgumentException(sprintf('Argument #1 value of #%s ($objectSchema) must be of type SchemaInterface, %s given', (string) $name, $type));
            }
        }

        $this->fieldSchemas = $fieldSchemas;
    }

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_array($input)) {
                throw new ParseError(sprintf("Input needs to be array, '%s'", \gettype($input)));
            }

            $output = new $this->classname();

            /** @var array<string,ParseErrorInterface> $parseErrors */
            $parseErrors = [];

            foreach (array_keys($input) as $property) {
                if (!isset($this->fieldSchemas[$property])) {
                    $parseErrors[$property] = new ParseErrors([new ParseError(sprintf("Additional property '%s'", $property))]);
                }
            }

            foreach ($this->fieldSchemas as $property => $fieldSchema) {
                try {
                    $output->{$property} = $fieldSchema->parse($input[$property] ?? null);
                } catch (ParseErrorInterface $parseError) {
                    $parseErrors[$property] = $parseError;
                }
            }

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
}
