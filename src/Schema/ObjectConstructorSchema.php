<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\Errors;
use Chubbyphp\Parsing\ErrorsException;

final class ObjectConstructorSchema extends AbstractObjectSchema implements ObjectSchemaInterface
{
    public const string ERROR_TYPE_CODE = 'object.type';
    public const string ERROR_UNKNOWN_FIELD_CODE = 'object.unknownField';

    public const string ERROR_PARAMETER_TYPE_CODE = 'object.parameterType';
    public const string ERROR_PARAMETER_TYPE_TEMPLATE = 'Parameter {{index}} {{name}} should be of {{type}}, {{given}} given';

    private readonly string $typeErrorPattern;

    /**
     * @param array<mixed, mixed> $fieldToSchema
     * @param class-string        $classname
     */
    public function __construct(array $fieldToSchema, private string $classname)
    {
        try {
            $reflectionClass = new \ReflectionClass($this->classname);
        } catch (\ReflectionException) {
            throw new \InvalidArgumentException('Class "'.$classname.'" does not exist or cannot be used for reflection');
        }

        try {
            $constructorReflectionMethod = $reflectionClass->getMethod('__construct');
        } catch (\ReflectionException) {
            throw new \InvalidArgumentException('Class "'.$classname.'" does not have a __construct method');
        }

        $parameterFieldToSchema = [];

        /** @var list<string> $missingFieldToSchema */
        $missingFieldToSchema = [];
        foreach ($constructorReflectionMethod->getParameters() as $parameterReflection) {
            $name = $parameterReflection->getName();

            if (isset($fieldToSchema[$name])) {
                $parameterFieldToSchema[$name] = $fieldToSchema[$name];

                unset($fieldToSchema[$name]);
            } elseif (!$parameterReflection->isOptional()) {
                $missingFieldToSchema[] = $name;
            }
        }

        if ([] !== $missingFieldToSchema) {
            throw new \InvalidArgumentException('Missing fieldToSchema for "'.$classname.'" __construct parameters: "'.implode('", "', $missingFieldToSchema).'"');
        }

        if ([] !== $fieldToSchema) {
            throw new \InvalidArgumentException('Additional fieldToSchema for "'.$classname.'" __construct parameters: "'.implode('", "', array_keys($fieldToSchema)).'"');
        }

        $this->typeErrorPattern = \sprintf('/%s::__construct\(\): Argument #(\d+) \(([^)]+)\) must be of type ([^ ]+), ([^ ]+) given/', preg_quote($this->classname, '/'));

        parent::__construct($parameterFieldToSchema);
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function parseFields(array $input, Errors $childrenErrors): ?object
    {
        $parameters = [];

        foreach ($this->getFieldToSchema() as $fieldName => $fieldSchema) {
            try {
                if ($this->skip($input, $fieldName)) {
                    continue;
                }

                $parameters[$fieldName] = $fieldSchema->parse($input[$fieldName] ?? null);
            } catch (ErrorsException $e) {
                $childrenErrors->add($e->errors, $fieldName);
            }
        }

        try {
            return new ($this->classname)(...$parameters);
        } catch (\TypeError $e) {
            $matches = [];

            if (1 === preg_match($this->typeErrorPattern, $e->getMessage(), $matches)) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_PARAMETER_TYPE_CODE,
                        self::ERROR_PARAMETER_TYPE_TEMPLATE,
                        ['index' => $matches[1], 'name' => $matches[2], 'type' => $matches[3], 'given' => $matches[4]]
                    )
                );
            }

            throw $e;
        }
    }
}
