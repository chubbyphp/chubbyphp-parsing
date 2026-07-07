<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit;

use Chubbyphp\Parsing\JsonSchemaToCode;
use Chubbyphp\Parsing\Parser;
use Chubbyphp\Parsing\Schema\SchemaInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Chubbyphp\Parsing\JsonSchemaToCode
 */
final class JsonSchemaToCodeTest extends TestCase
{
    #[DataProvider('provideJsonSchemaToParsingCodeCases')]
    public function testJsonSchemaToParsingCode(string $jsonSchema, string $expectedPhp): void
    {
        $php = (new JsonSchemaToCode())->convert($jsonSchema);

        self::assertSame($expectedPhp, $php);

        $p = new Parser();
        $schema = null;

        eval($php);

        self::assertInstanceOf(SchemaInterface::class, $schema);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideJsonSchemaToParsingCodeCases(): iterable
    {
        yield 'string' => [
            '{"type":"string"}',
            '$schema = $p->string();',
        ];

        yield 'string: minLength' => [
            '{"type":"string","minLength":3}',
            '$schema = $p->string()->minLength(3);',
        ];

        yield 'string: maxLength' => [
            '{"type":"string","maxLength":64}',
            '$schema = $p->string()->maxLength(64);',
        ];

        yield 'string: pattern' => [
            '{"type":"string","pattern":"^[a-z][a-z0-9-]*$"}',
            '$schema = $p->string()->pattern(\'/^[a-z][a-z0-9-]*$/\');',
        ];

        yield 'string: format email' => [
            '{"type":"string","format":"email"}',
            '$schema = $p->string()->email();',
        ];

        yield 'string: format hostname' => [
            '{"type":"string","format":"hostname"}',
            '$schema = $p->string()->hostname();',
        ];

        yield 'string: format ipv4' => [
            '{"type":"string","format":"ipv4"}',
            '$schema = $p->string()->ipV4();',
        ];

        yield 'string: format ipv6' => [
            '{"type":"string","format":"ipv6"}',
            '$schema = $p->string()->ipV6();',
        ];

        yield 'string: format mac' => [
            '{"type":"string","format":"mac"}',
            '$schema = $p->string()->mac();',
        ];

        yield 'string: format uri' => [
            '{"type":"string","format":"uri"}',
            '$schema = $p->string()->uri();',
        ];

        yield 'string: format url' => [
            '{"type":"string","format":"url"}',
            '$schema = $p->string()->url();',
        ];

        yield 'string: format uuid' => [
            '{"type":"string","format":"uuid"}',
            '$schema = $p->string()->uuid();',
        ];

        yield 'string: minLength + maxLength' => [
            '{"type":"string","minLength":3,"maxLength":64}',
            '$schema = $p->string()->minLength(3)->maxLength(64);',
        ];

        yield 'string: minLength + pattern' => [
            '{"type":"string","minLength":3,"pattern":"^[a-z][a-z0-9-]*$"}',
            '$schema = $p->string()->minLength(3)->pattern(\'/^[a-z][a-z0-9-]*$/\');',
        ];

        yield 'string: minLength + format' => [
            '{"type":"string","minLength":3,"format":"email"}',
            '$schema = $p->string()->minLength(3)->email();',
        ];

        yield 'string: maxLength + pattern' => [
            '{"type":"string","maxLength":64,"pattern":"^[a-z][a-z0-9-]*$"}',
            '$schema = $p->string()->maxLength(64)->pattern(\'/^[a-z][a-z0-9-]*$/\');',
        ];

        yield 'string: maxLength + format' => [
            '{"type":"string","maxLength":64,"format":"email"}',
            '$schema = $p->string()->maxLength(64)->email();',
        ];

        yield 'string: pattern + format' => [
            '{"type":"string","pattern":"^[a-z][a-z0-9-]*$","format":"email"}',
            '$schema = $p->string()->pattern(\'/^[a-z][a-z0-9-]*$/\')->email();',
        ];

        yield 'string: minLength + maxLength + pattern' => [
            '{"type":"string","minLength":3,"maxLength":64,"pattern":"^[a-z][a-z0-9-]*$"}',
            '$schema = $p->string()->minLength(3)->maxLength(64)->pattern(\'/^[a-z][a-z0-9-]*$/\');',
        ];

        yield 'string: minLength + maxLength + format' => [
            '{"type":"string","minLength":3,"maxLength":64,"format":"email"}',
            '$schema = $p->string()->minLength(3)->maxLength(64)->email();',
        ];

        yield 'string: minLength + pattern + format' => [
            '{"type":"string","minLength":3,"pattern":"^[a-z][a-z0-9-]*$","format":"email"}',
            '$schema = $p->string()->minLength(3)->pattern(\'/^[a-z][a-z0-9-]*$/\')->email();',
        ];

        yield 'string: maxLength + pattern + format' => [
            '{"type":"string","maxLength":64,"pattern":"^[a-z][a-z0-9-]*$","format":"email"}',
            '$schema = $p->string()->maxLength(64)->pattern(\'/^[a-z][a-z0-9-]*$/\')->email();',
        ];

        yield 'string: minLength + maxLength + pattern + format' => [
            '{"type":"string","minLength":3,"maxLength":64,"pattern":"^[a-z][a-z0-9-]*$","format":"email"}',
            '$schema = $p->string()->minLength(3)->maxLength(64)->pattern(\'/^[a-z][a-z0-9-]*$/\')->email();',
        ];

        yield 'number' => [
            '{"type":"number"}',
            '$schema = $p->float();',
        ];

        yield 'number: minimum' => [
            '{"type":"number","minimum":0}',
            '$schema = $p->float()->minimum(0.0);',
        ];

        yield 'number: maximum' => [
            '{"type":"number","maximum":100}',
            '$schema = $p->float()->maximum(100.0);',
        ];

        yield 'number: exclusiveMinimum' => [
            '{"type":"number","exclusiveMinimum":0}',
            '$schema = $p->float()->exclusiveMinimum(0.0);',
        ];

        yield 'number: exclusiveMaximum' => [
            '{"type":"number","exclusiveMaximum":100}',
            '$schema = $p->float()->exclusiveMaximum(100.0);',
        ];

        yield 'number: multipleOf' => [
            '{"type":"number","multipleOf":0.5}',
            '$schema = $p->float()->multipleOf(0.5);',
        ];

        yield 'number: minimum + maximum' => [
            '{"type":"number","minimum":0,"maximum":100}',
            '$schema = $p->float()->minimum(0.0)->maximum(100.0);',
        ];

        yield 'number: minimum + exclusiveMinimum' => [
            '{"type":"number","minimum":0,"exclusiveMinimum":0}',
            '$schema = $p->float()->minimum(0.0)->exclusiveMinimum(0.0);',
        ];

        yield 'number: minimum + exclusiveMaximum' => [
            '{"type":"number","minimum":0,"exclusiveMaximum":100}',
            '$schema = $p->float()->minimum(0.0)->exclusiveMaximum(100.0);',
        ];

        yield 'number: minimum + multipleOf' => [
            '{"type":"number","minimum":0,"multipleOf":0.5}',
            '$schema = $p->float()->minimum(0.0)->multipleOf(0.5);',
        ];

        yield 'number: maximum + exclusiveMinimum' => [
            '{"type":"number","maximum":100,"exclusiveMinimum":0}',
            '$schema = $p->float()->maximum(100.0)->exclusiveMinimum(0.0);',
        ];

        yield 'number: maximum + exclusiveMaximum' => [
            '{"type":"number","maximum":100,"exclusiveMaximum":100}',
            '$schema = $p->float()->maximum(100.0)->exclusiveMaximum(100.0);',
        ];

        yield 'number: maximum + multipleOf' => [
            '{"type":"number","maximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->maximum(100.0)->multipleOf(0.5);',
        ];

        yield 'number: exclusiveMinimum + exclusiveMaximum' => [
            '{"type":"number","exclusiveMinimum":0,"exclusiveMaximum":100}',
            '$schema = $p->float()->exclusiveMinimum(0.0)->exclusiveMaximum(100.0);',
        ];

        yield 'number: exclusiveMinimum + multipleOf' => [
            '{"type":"number","exclusiveMinimum":0,"multipleOf":0.5}',
            '$schema = $p->float()->exclusiveMinimum(0.0)->multipleOf(0.5);',
        ];

        yield 'number: exclusiveMaximum + multipleOf' => [
            '{"type":"number","exclusiveMaximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->exclusiveMaximum(100.0)->multipleOf(0.5);',
        ];

        yield 'number: minimum + maximum + exclusiveMinimum' => [
            '{"type":"number","minimum":0,"maximum":100,"exclusiveMinimum":0}',
            '$schema = $p->float()->minimum(0.0)->maximum(100.0)->exclusiveMinimum(0.0);',
        ];

        yield 'number: minimum + maximum + exclusiveMaximum' => [
            '{"type":"number","minimum":0,"maximum":100,"exclusiveMaximum":100}',
            '$schema = $p->float()->minimum(0.0)->maximum(100.0)->exclusiveMaximum(100.0);',
        ];

        yield 'number: minimum + maximum + multipleOf' => [
            '{"type":"number","minimum":0,"maximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->minimum(0.0)->maximum(100.0)->multipleOf(0.5);',
        ];

        yield 'number: minimum + exclusiveMinimum + exclusiveMaximum' => [
            '{"type":"number","minimum":0,"exclusiveMinimum":0,"exclusiveMaximum":100}',
            '$schema = $p->float()->minimum(0.0)->exclusiveMinimum(0.0)->exclusiveMaximum(100.0);',
        ];

        yield 'number: minimum + exclusiveMinimum + multipleOf' => [
            '{"type":"number","minimum":0,"exclusiveMinimum":0,"multipleOf":0.5}',
            '$schema = $p->float()->minimum(0.0)->exclusiveMinimum(0.0)->multipleOf(0.5);',
        ];

        yield 'number: minimum + exclusiveMaximum + multipleOf' => [
            '{"type":"number","minimum":0,"exclusiveMaximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->minimum(0.0)->exclusiveMaximum(100.0)->multipleOf(0.5);',
        ];

        yield 'number: maximum + exclusiveMinimum + exclusiveMaximum' => [
            '{"type":"number","maximum":100,"exclusiveMinimum":0,"exclusiveMaximum":100}',
            '$schema = $p->float()->maximum(100.0)->exclusiveMinimum(0.0)->exclusiveMaximum(100.0);',
        ];

        yield 'number: maximum + exclusiveMinimum + multipleOf' => [
            '{"type":"number","maximum":100,"exclusiveMinimum":0,"multipleOf":0.5}',
            '$schema = $p->float()->maximum(100.0)->exclusiveMinimum(0.0)->multipleOf(0.5);',
        ];

        yield 'number: maximum + exclusiveMaximum + multipleOf' => [
            '{"type":"number","maximum":100,"exclusiveMaximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->maximum(100.0)->exclusiveMaximum(100.0)->multipleOf(0.5);',
        ];

        yield 'number: exclusiveMinimum + exclusiveMaximum + multipleOf' => [
            '{"type":"number","exclusiveMinimum":0,"exclusiveMaximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->exclusiveMinimum(0.0)->exclusiveMaximum(100.0)->multipleOf(0.5);',
        ];

        yield 'number: minimum + maximum + exclusiveMinimum + exclusiveMaximum' => [
            '{"type":"number","minimum":0,"maximum":100,"exclusiveMinimum":0,"exclusiveMaximum":100}',
            '$schema = $p->float()->minimum(0.0)->maximum(100.0)->exclusiveMinimum(0.0)->exclusiveMaximum(100.0);',
        ];

        yield 'number: minimum + maximum + exclusiveMinimum + multipleOf' => [
            '{"type":"number","minimum":0,"maximum":100,"exclusiveMinimum":0,"multipleOf":0.5}',
            '$schema = $p->float()->minimum(0.0)->maximum(100.0)->exclusiveMinimum(0.0)->multipleOf(0.5);',
        ];

        yield 'number: minimum + maximum + exclusiveMaximum + multipleOf' => [
            '{"type":"number","minimum":0,"maximum":100,"exclusiveMaximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->minimum(0.0)->maximum(100.0)->exclusiveMaximum(100.0)->multipleOf(0.5);',
        ];

        yield 'number: minimum + exclusiveMinimum + exclusiveMaximum + multipleOf' => [
            '{"type":"number","minimum":0,"exclusiveMinimum":0,"exclusiveMaximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->minimum(0.0)->exclusiveMinimum(0.0)->exclusiveMaximum(100.0)->multipleOf(0.5);',
        ];

        yield 'number: maximum + exclusiveMinimum + exclusiveMaximum + multipleOf' => [
            '{"type":"number","maximum":100,"exclusiveMinimum":0,"exclusiveMaximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->maximum(100.0)->exclusiveMinimum(0.0)->exclusiveMaximum(100.0)->multipleOf(0.5);',
        ];

        yield 'number: minimum + maximum + exclusiveMinimum + exclusiveMaximum + multipleOf' => [
            '{"type":"number","minimum":0,"maximum":100,"exclusiveMinimum":0,"exclusiveMaximum":100,"multipleOf":0.5}',
            '$schema = $p->float()->minimum(0.0)->maximum(100.0)->exclusiveMinimum(0.0)->exclusiveMaximum(100.0)->multipleOf(0.5);',
        ];

        yield 'integer' => [
            '{"type":"integer"}',
            '$schema = $p->int();',
        ];

        yield 'integer: minimum' => [
            '{"type":"integer","minimum":0}',
            '$schema = $p->int()->minimum(0);',
        ];

        yield 'integer: maximum' => [
            '{"type":"integer","maximum":100}',
            '$schema = $p->int()->maximum(100);',
        ];

        yield 'integer: exclusiveMinimum' => [
            '{"type":"integer","exclusiveMinimum":0}',
            '$schema = $p->int()->exclusiveMinimum(0);',
        ];

        yield 'integer: exclusiveMaximum' => [
            '{"type":"integer","exclusiveMaximum":100}',
            '$schema = $p->int()->exclusiveMaximum(100);',
        ];

        yield 'integer: multipleOf' => [
            '{"type":"integer","multipleOf":2}',
            '$schema = $p->int()->multipleOf(2);',
        ];

        yield 'integer: minimum + maximum' => [
            '{"type":"integer","minimum":0,"maximum":100}',
            '$schema = $p->int()->minimum(0)->maximum(100);',
        ];

        yield 'integer: minimum + exclusiveMinimum' => [
            '{"type":"integer","minimum":0,"exclusiveMinimum":0}',
            '$schema = $p->int()->minimum(0)->exclusiveMinimum(0);',
        ];

        yield 'integer: minimum + exclusiveMaximum' => [
            '{"type":"integer","minimum":0,"exclusiveMaximum":100}',
            '$schema = $p->int()->minimum(0)->exclusiveMaximum(100);',
        ];

        yield 'integer: minimum + multipleOf' => [
            '{"type":"integer","minimum":0,"multipleOf":2}',
            '$schema = $p->int()->minimum(0)->multipleOf(2);',
        ];

        yield 'integer: maximum + exclusiveMinimum' => [
            '{"type":"integer","maximum":100,"exclusiveMinimum":0}',
            '$schema = $p->int()->maximum(100)->exclusiveMinimum(0);',
        ];

        yield 'integer: maximum + exclusiveMaximum' => [
            '{"type":"integer","maximum":100,"exclusiveMaximum":100}',
            '$schema = $p->int()->maximum(100)->exclusiveMaximum(100);',
        ];

        yield 'integer: maximum + multipleOf' => [
            '{"type":"integer","maximum":100,"multipleOf":2}',
            '$schema = $p->int()->maximum(100)->multipleOf(2);',
        ];

        yield 'integer: exclusiveMinimum + exclusiveMaximum' => [
            '{"type":"integer","exclusiveMinimum":0,"exclusiveMaximum":100}',
            '$schema = $p->int()->exclusiveMinimum(0)->exclusiveMaximum(100);',
        ];

        yield 'integer: exclusiveMinimum + multipleOf' => [
            '{"type":"integer","exclusiveMinimum":0,"multipleOf":2}',
            '$schema = $p->int()->exclusiveMinimum(0)->multipleOf(2);',
        ];

        yield 'integer: exclusiveMaximum + multipleOf' => [
            '{"type":"integer","exclusiveMaximum":100,"multipleOf":2}',
            '$schema = $p->int()->exclusiveMaximum(100)->multipleOf(2);',
        ];

        yield 'integer: minimum + maximum + exclusiveMinimum' => [
            '{"type":"integer","minimum":0,"maximum":100,"exclusiveMinimum":0}',
            '$schema = $p->int()->minimum(0)->maximum(100)->exclusiveMinimum(0);',
        ];

        yield 'integer: minimum + maximum + exclusiveMaximum' => [
            '{"type":"integer","minimum":0,"maximum":100,"exclusiveMaximum":100}',
            '$schema = $p->int()->minimum(0)->maximum(100)->exclusiveMaximum(100);',
        ];

        yield 'integer: minimum + maximum + multipleOf' => [
            '{"type":"integer","minimum":0,"maximum":100,"multipleOf":2}',
            '$schema = $p->int()->minimum(0)->maximum(100)->multipleOf(2);',
        ];

        yield 'integer: minimum + exclusiveMinimum + exclusiveMaximum' => [
            '{"type":"integer","minimum":0,"exclusiveMinimum":0,"exclusiveMaximum":100}',
            '$schema = $p->int()->minimum(0)->exclusiveMinimum(0)->exclusiveMaximum(100);',
        ];

        yield 'integer: minimum + exclusiveMinimum + multipleOf' => [
            '{"type":"integer","minimum":0,"exclusiveMinimum":0,"multipleOf":2}',
            '$schema = $p->int()->minimum(0)->exclusiveMinimum(0)->multipleOf(2);',
        ];

        yield 'integer: minimum + exclusiveMaximum + multipleOf' => [
            '{"type":"integer","minimum":0,"exclusiveMaximum":100,"multipleOf":2}',
            '$schema = $p->int()->minimum(0)->exclusiveMaximum(100)->multipleOf(2);',
        ];

        yield 'integer: maximum + exclusiveMinimum + exclusiveMaximum' => [
            '{"type":"integer","maximum":100,"exclusiveMinimum":0,"exclusiveMaximum":100}',
            '$schema = $p->int()->maximum(100)->exclusiveMinimum(0)->exclusiveMaximum(100);',
        ];

        yield 'integer: maximum + exclusiveMinimum + multipleOf' => [
            '{"type":"integer","maximum":100,"exclusiveMinimum":0,"multipleOf":2}',
            '$schema = $p->int()->maximum(100)->exclusiveMinimum(0)->multipleOf(2);',
        ];

        yield 'integer: maximum + exclusiveMaximum + multipleOf' => [
            '{"type":"integer","maximum":100,"exclusiveMaximum":100,"multipleOf":2}',
            '$schema = $p->int()->maximum(100)->exclusiveMaximum(100)->multipleOf(2);',
        ];

        yield 'integer: exclusiveMinimum + exclusiveMaximum + multipleOf' => [
            '{"type":"integer","exclusiveMinimum":0,"exclusiveMaximum":100,"multipleOf":2}',
            '$schema = $p->int()->exclusiveMinimum(0)->exclusiveMaximum(100)->multipleOf(2);',
        ];

        yield 'integer: minimum + maximum + exclusiveMinimum + exclusiveMaximum' => [
            '{"type":"integer","minimum":0,"maximum":100,"exclusiveMinimum":0,"exclusiveMaximum":100}',
            '$schema = $p->int()->minimum(0)->maximum(100)->exclusiveMinimum(0)->exclusiveMaximum(100);',
        ];

        yield 'integer: minimum + maximum + exclusiveMinimum + multipleOf' => [
            '{"type":"integer","minimum":0,"maximum":100,"exclusiveMinimum":0,"multipleOf":2}',
            '$schema = $p->int()->minimum(0)->maximum(100)->exclusiveMinimum(0)->multipleOf(2);',
        ];

        yield 'integer: minimum + maximum + exclusiveMaximum + multipleOf' => [
            '{"type":"integer","minimum":0,"maximum":100,"exclusiveMaximum":100,"multipleOf":2}',
            '$schema = $p->int()->minimum(0)->maximum(100)->exclusiveMaximum(100)->multipleOf(2);',
        ];

        yield 'integer: minimum + exclusiveMinimum + exclusiveMaximum + multipleOf' => [
            '{"type":"integer","minimum":0,"exclusiveMinimum":0,"exclusiveMaximum":100,"multipleOf":2}',
            '$schema = $p->int()->minimum(0)->exclusiveMinimum(0)->exclusiveMaximum(100)->multipleOf(2);',
        ];

        yield 'integer: maximum + exclusiveMinimum + exclusiveMaximum + multipleOf' => [
            '{"type":"integer","maximum":100,"exclusiveMinimum":0,"exclusiveMaximum":100,"multipleOf":2}',
            '$schema = $p->int()->maximum(100)->exclusiveMinimum(0)->exclusiveMaximum(100)->multipleOf(2);',
        ];

        yield 'integer: minimum + maximum + exclusiveMinimum + exclusiveMaximum + multipleOf' => [
            '{"type":"integer","minimum":0,"maximum":100,"exclusiveMinimum":0,"exclusiveMaximum":100,"multipleOf":2}',
            '$schema = $p->int()->minimum(0)->maximum(100)->exclusiveMinimum(0)->exclusiveMaximum(100)->multipleOf(2);',
        ];

        yield 'boolean' => [
            '{"type":"boolean"}',
            '$schema = $p->bool();',
        ];

        yield 'array: items' => [
            '{"type":"array","items":{"type":"string"}}',
            '$schema = $p->array($p->string());',
        ];

        yield 'array: prefixItems' => [
            '{"type":"array","prefixItems":[{"type":"number"},{"type":"string"}]}',
            '$schema = $p->tuple([$p->float(), $p->string()]);',
        ];

        yield 'array: items + minItems' => [
            '{"type":"array","items":{"type":"string"},"minItems":1}',
            '$schema = $p->array($p->string())->minItems(1);',
        ];

        yield 'array: items + maxItems' => [
            '{"type":"array","items":{"type":"string"},"maxItems":10}',
            '$schema = $p->array($p->string())->maxItems(10);',
        ];

        yield 'array: items + uniqueItems' => [
            '{"type":"array","items":{"type":"string"},"uniqueItems":true}',
            '$schema = $p->array($p->string())->uniqueItems();',
        ];

        yield 'array: items + minItems + maxItems' => [
            '{"type":"array","items":{"type":"string"},"minItems":1,"maxItems":10}',
            '$schema = $p->array($p->string())->minItems(1)->maxItems(10);',
        ];

        yield 'array: items + minItems + uniqueItems' => [
            '{"type":"array","items":{"type":"string"},"minItems":1,"uniqueItems":true}',
            '$schema = $p->array($p->string())->minItems(1)->uniqueItems();',
        ];

        yield 'array: items + maxItems + uniqueItems' => [
            '{"type":"array","items":{"type":"string"},"maxItems":10,"uniqueItems":true}',
            '$schema = $p->array($p->string())->maxItems(10)->uniqueItems();',
        ];

        yield 'array: items + minItems + maxItems + uniqueItems' => [
            '{"type":"array","items":{"type":"string"},"minItems":1,"maxItems":10,"uniqueItems":true}',
            '$schema = $p->array($p->string())->minItems(1)->maxItems(10)->uniqueItems();',
        ];

        yield 'object: properties' => [
            '{"type":"object","properties":{"name":{"type":"string"}}}',
            '$schema = $p->object([\'name\' => $p->string()])->optional([\'name\']);',
        ];

        yield 'object: additionalProperties' => [
            '{"type":"object","additionalProperties":false}',
            '$schema = $p->object([])->strict();',
        ];

        yield 'object: properties + additionalProperties' => [
            '{"type":"object","properties":{"name":{"type":"string"}},"additionalProperties":false}',
            '$schema = $p->object([\'name\' => $p->string()])->optional([\'name\'])->strict();',
        ];

        yield 'object: properties + required' => [
            '{"type":"object","properties":{"name":{"type":"string"}},"required":["name"]}',
            '$schema = $p->object([\'name\' => $p->string()]);',
        ];

        yield 'object: properties + additionalProperties + required' => [
            '{"type":"object","properties":{"name":{"type":"string"}},"additionalProperties":false,"required":["name"]}',
            '$schema = $p->object([\'name\' => $p->string()])->strict();',
        ];
    }
}
