<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * @internal
 *
 * @coversNothing
 */
final class OpenapiTest extends TestCase
{
    public function testSuccess(): void
    {
        $schemaYaml =
        <<<'EOD'
            components:
              schemas:
                Error:
                  type: object
                  required:
                    - code
                    - message
                  properties:
                    code:
                      type: integer
                      description: Error code
                    message:
                      type: string
                      description: Error message
                    details:
                      type: array
                      items:
                        type: string
                      description: Additional error details
            EOD;

        $yamlParser = new YamlParser();

        $openApi = $yamlParser->parse($schemaYaml);

        foreach ($openApi['components']['schemas'] as $name => $schema) {
            var_dump($name, $this->parseSchema($schema));
        }
    }

    private function parseSchema(array $definition)
    {
        switch ($definition['type']) {
            case 'string':
                return $this->parseStringSchema($definition);

            case 'number':
                return $this->parseNumberSchema($definition);

            case 'integer':
                return $this->parseIntegerSchema($definition);

            case 'boolean':
                return $this->parseBooleanSchema($definition);

            case 'object':
                return $this->parseObjectSchema($definition);

            case 'array':
                return $this->parseArraySchema($definition);

            case 'integer':
                return $this->parseIntegerSchema($definition);
        }
    }

    private function parseStringSchema(array $definition): string
    {
        $string = '$parser->string()';

        // nullable

        if ($definition['format']) {
            switch ($definition['format']) {
                case 'date':
                    $string .= "->match('^\\d{4}-\\d{2}-\\d{2}$')"; // do better

                    // no break
                case 'date-time':
                    $string .= "->match('^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}Z$')"; // do better

                    // no break
                case 'email':
                    $string .= '->email()';

                    // no break
                case 'hostname':
                    throw new \Exception('Not implemented yet');

                case 'ipv4':
                    $string .= '->ipV4()';

                    // no break
                case 'ipv6':
                    $string .= '->ipV6()';

                    // no break
                case 'uri':
                    $string .= '->url()';

                    // no break
                case 'uuid':
                    throw new \Exception('Not implemented yet');
            }
        }

        return $string;
    }

    private function parseNumberSchema(array $definition): string
    {
        return '$parser->float()';
    }

    private function parseIntegerSchema(array $definition): string
    {
        return '$parser->int()';
    }

    private function parseBooleanSchema(array $definition): string
    {
        return '$parser->bool()';
    }

    private function parseArraySchema(array $definition): string
    {
        return '$parser->array('.PHP_EOL.$this->parseSchema($definition['items']).PHP_EOL.')';
    }

    private function parseObjectSchema(array $definition): string
    {
        $required = array_fill_keys(array_keys($definition['properties'] ?? []), false);
        foreach ($definition['required'] as $childName) {
            $required[$childName] = true;
        }

        $properties = [];
        foreach ($definition['properties'] as $childName => $childDefinition) {
            $property = "'".$childName."' => ".$this->parseSchema($childDefinition);

            if (!$required[$childName]) {
                $property .= '->nullable()';
            }

            $properties[] = $property;
        }

        return '$parser->object(['.PHP_EOL.implode(','.PHP_EOL, $properties).PHP_EOL.'])';
    }
}
