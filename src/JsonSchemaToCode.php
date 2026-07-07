<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\PrettyPrinter\Standard;

final class JsonSchemaToCode
{
    private const string ARG_INT = 'int';
    private const string ARG_FLOAT = 'float';
    private const string ARG_PATTERN = 'pattern';
    private const string ARG_FORMAT = 'format';
    private const string ARG_FLAG = 'flag';

    /**
     * @var array<string, array{
     *     method?: string,
     *     factories?: array<string, array{method: string, list: bool}>,
     *     keywords: array<string, self::ARG_*>,
     * }>
     */
    private const array TYPES = [
        'string' => [
            'method' => 'string',
            'keywords' => [
                'minLength' => self::ARG_INT,
                'maxLength' => self::ARG_INT,
                'pattern' => self::ARG_PATTERN,
                'format' => self::ARG_FORMAT,
            ],
        ],
        'number' => [
            'method' => 'float',
            'keywords' => [
                'minimum' => self::ARG_FLOAT,
                'maximum' => self::ARG_FLOAT,
                'exclusiveMinimum' => self::ARG_FLOAT,
                'exclusiveMaximum' => self::ARG_FLOAT,
                'multipleOf' => self::ARG_FLOAT,
            ],
        ],
        'integer' => [
            'method' => 'int',
            'keywords' => [
                'minimum' => self::ARG_INT,
                'maximum' => self::ARG_INT,
                'exclusiveMinimum' => self::ARG_INT,
                'exclusiveMaximum' => self::ARG_INT,
                'multipleOf' => self::ARG_INT,
            ],
        ],
        'boolean' => [
            'method' => 'bool',
            'keywords' => [],
        ],
        'array' => [
            'factories' => [
                'items' => ['method' => 'array', 'list' => false],
                'prefixItems' => ['method' => 'tuple', 'list' => true],
            ],
            'keywords' => [
                'minItems' => self::ARG_INT,
                'maxItems' => self::ARG_INT,
                'uniqueItems' => self::ARG_FLAG,
            ],
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const array FORMAT_TO_METHOD = [
        'email' => 'email',
        'hostname' => 'hostname',
        'ipv4' => 'ipV4',
        'ipv6' => 'ipV6',
        'mac' => 'mac',
        'uri' => 'uri',
        'url' => 'url',
        'uuid' => 'uuid',
    ];

    private Standard $prettyPrinter;

    public function __construct()
    {
        $this->prettyPrinter = new Standard();
    }

    public function convert(string $jsonSchema): string
    {
        /** @var mixed $schema */
        $schema = json_decode($jsonSchema, true, 512, JSON_THROW_ON_ERROR);

        if (!\is_array($schema)) {
            throw new \InvalidArgumentException('Json schema must decode to an object');
        }

        $statement = new Expression(new Assign(new Variable('schema'), $this->convertSchema($schema)));

        return $this->prettyPrinter->prettyPrint([$statement]);
    }

    /**
     * @param array<mixed> $schema
     */
    private function convertSchema(array $schema): Expr
    {
        $type = $schema['type'] ?? null;

        if ('object' === $type) {
            return $this->convertObject($schema);
        }

        if (!\is_string($type) || !isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException(
                \sprintf('Unsupported type "%s"', \is_string($type) ? $type : \gettype($type))
            );
        }

        $typeDefinition = self::TYPES[$type];

        $expr = $this->createSchema($type, $typeDefinition, $schema);

        foreach ($typeDefinition['keywords'] as $keyword => $argType) {
            if (isset($schema[$keyword])) {
                $expr = $this->addKeyword($expr, $keyword, $argType, $schema[$keyword]);
            }
        }

        return $expr;
    }

    /**
     * @param array<mixed> $schema
     */
    private function convertObject(array $schema): Expr
    {
        $required = [];
        foreach ($this->subSchema($schema['required'] ?? [], 'required') as $fieldName) {
            $required[] = $this->string($fieldName, 'required');
        }

        $fields = [];
        $optional = [];
        foreach ($this->subSchema($schema['properties'] ?? [], 'properties') as $fieldName => $fieldSchema) {
            $fieldName = (string) $fieldName;
            $fields[] = new ArrayItem(
                $this->convertSchema($this->subSchema($fieldSchema, 'properties')),
                new String_($fieldName)
            );

            if (!\in_array($fieldName, $required, true)) {
                $optional[] = new ArrayItem(new String_($fieldName));
            }
        }

        $expr = new MethodCall(new Variable('p'), 'object', [new Arg(new Array_($fields))]);

        if ([] !== $optional) {
            $expr = new MethodCall($expr, 'optional', [new Arg(new Array_($optional))]);
        }

        if (isset($schema['additionalProperties']) && !$this->bool($schema['additionalProperties'], 'additionalProperties')) {
            $expr = new MethodCall($expr, 'strict');
        }

        return $expr;
    }

    /**
     * @param array{
     *     method?: string,
     *     factories?: array<string, array{method: string, list: bool}>,
     *     keywords: array<string, self::ARG_*>,
     * } $typeDefinition
     * @param array<mixed> $schema
     */
    private function createSchema(string $type, array $typeDefinition, array $schema): Expr
    {
        $parser = new Variable('p');

        foreach ($typeDefinition['factories'] ?? [] as $keyword => $factory) {
            if (!isset($schema[$keyword])) {
                continue;
            }

            $subSchema = $this->subSchema($schema[$keyword], $keyword);

            $arg = $factory['list']
                ? new Array_(array_map(
                    fn (mixed $itemSchema) => new ArrayItem($this->convertSchema($this->subSchema($itemSchema, $keyword))),
                    array_values($subSchema)
                ))
                : $this->convertSchema($subSchema);

            return new MethodCall($parser, $factory['method'], [new Arg($arg)]);
        }

        return new MethodCall(
            $parser,
            $typeDefinition['method'] ?? throw new \InvalidArgumentException(
                \sprintf('Type "%s" requires one of: %s', $type, implode(', ', array_keys($typeDefinition['factories'] ?? [])))
            )
        );
    }

    /**
     * @param self::ARG_* $argType
     */
    private function addKeyword(Expr $expr, string $keyword, string $argType, mixed $value): Expr
    {
        return match ($argType) {
            self::ARG_INT => new MethodCall($expr, $keyword, [
                new Arg(new Int_($this->int($value, $keyword))),
            ]),
            self::ARG_FLOAT => new MethodCall($expr, $keyword, [
                new Arg(new Float_($this->float($value, $keyword))),
            ]),
            self::ARG_PATTERN => new MethodCall($expr, $keyword, [
                new Arg(new String_('/'.str_replace('/', '\/', $this->string($value, $keyword)).'/')),
            ]),
            self::ARG_FORMAT => new MethodCall($expr, $this->formatMethod($this->string($value, $keyword))),
            self::ARG_FLAG => $this->bool($value, $keyword) ? new MethodCall($expr, $keyword) : $expr,
        };
    }

    private function formatMethod(string $format): string
    {
        return self::FORMAT_TO_METHOD[$format]
            ?? throw new \InvalidArgumentException(\sprintf('Unsupported format "%s"', $format));
    }

    /**
     * @return array<mixed>
     */
    private function subSchema(mixed $value, string $keyword): array
    {
        if (!\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('Keyword "%s" must be a schema or a list of schemas', $keyword));
        }

        return $value;
    }

    private function int(mixed $value, string $keyword): int
    {
        if (!\is_int($value)) {
            throw new \InvalidArgumentException(\sprintf('Keyword "%s" must be an integer', $keyword));
        }

        return $value;
    }

    private function float(mixed $value, string $keyword): float
    {
        if (!\is_int($value) && !\is_float($value)) {
            throw new \InvalidArgumentException(\sprintf('Keyword "%s" must be a number', $keyword));
        }

        return (float) $value;
    }

    private function bool(mixed $value, string $keyword): bool
    {
        if (!\is_bool($value)) {
            throw new \InvalidArgumentException(\sprintf('Keyword "%s" must be a boolean', $keyword));
        }

        return $value;
    }

    private function string(mixed $value, string $keyword): string
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException(\sprintf('Keyword "%s" must be a string', $keyword));
        }

        return $value;
    }
}
