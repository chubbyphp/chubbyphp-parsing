<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class BackedEnumSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'backedEnum.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "int|string", {{given}} given';

    public const ERROR_VALUE_CODE = 'backedEnum.value';
    public const ERROR_VALUE_TEMPLATE = 'Value should be one of {{cases}}, {{given}} given';

    private \BackedEnum $backedEnum;

    /**
     * @param class-string<\BackedEnum> $backedEnumClass
     */
    public function __construct(string $backedEnumClass)
    {
        if (!enum_exists($backedEnumClass)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument #1 ($backedEnum) must be of type \BackedEnum::class, %s given',
                    $this->getDataType($backedEnumClass)
                )
            );
        }

        $cases = $backedEnumClass::cases();

        $backedEnum = array_shift($cases);

        if (!$backedEnum instanceof \BackedEnum) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument #1 ($backedEnum) must be of type \BackedEnum::class, %s given',
                    $this->getDataType($backedEnumClass)
                )
            );
        }

        $this->backedEnum = $backedEnum;
    }

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            if (!\is_int($input) && !\is_string($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            $output = ($this->backedEnum)::tryFrom($input);

            if (null === $output) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_VALUE_CODE,
                        self::ERROR_VALUE_TEMPLATE,
                        [
                            'cases' => $this->casesToCasesValues($this->backedEnum),
                            'given' => $input,
                        ]
                    )
                );
            }

            return $this->dispatchPostParses($output);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function toInt(): IntSchema
    {
        return (new IntSchema())->preParse(function ($input) {
            /** @var null|\BackedEnum $input */
            $input = $this->parse($input);

            return null !== $input ? $input->value : null;
        })->nullable($this->nullable);
    }

    public function toString(): StringSchema
    {
        return (new StringSchema())->preParse(function ($input) {
            /** @var null|\BackedEnum $input */
            $input = $this->parse($input);

            return null !== $input ? $input->value : null;
        })->nullable($this->nullable);
    }

    /**
     * @return array<int|string>
     */
    private function casesToCasesValues(\BackedEnum $enum): array
    {
        $cases = [];
        foreach ($enum::cases() as $i => $case) {
            $cases[$i] = $case->value;
        }

        return $cases;
    }
}
