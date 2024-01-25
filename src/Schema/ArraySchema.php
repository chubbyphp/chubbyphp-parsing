<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class ArraySchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'array.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "array", "{{given}}" given';

    public const ERROR_MIN_CODE = 'array.min';
    public const ERROR_MIN_TEMPLATE = 'Min length {{min}}, {{given}} given';

    public const ERROR_MAX_CODE = 'array.max';
    public const ERROR_MAX_TEMPLATE = 'Max length {{max}}, {{given}} given';

    public const ERROR_LENGTH_CODE = 'array.length';
    public const ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    public function __construct(private SchemaInterface $itemSchema) {}

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_array($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            $output = [];

            $childrenParserErrorException = new ParserErrorException();

            foreach ($input as $i => $item) {
                try {
                    $output[$i] = $this->itemSchema->parse($item);
                } catch (ParserErrorException $childParserErrorException) {
                    $childrenParserErrorException->addParserErrorException($childParserErrorException, $i);
                }
            }

            if ($childrenParserErrorException->hasError()) {
                throw $childrenParserErrorException;
            }

            return $this->transformOutput($output);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function min(int $min): static
    {
        return $this->transform(static function (array $output) use ($min) {
            $outputLength = \count($output);

            if ($outputLength < $min) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MIN_CODE,
                        self::ERROR_MIN_TEMPLATE,
                        ['min' => $min, 'given' => $outputLength]
                    )
                );
            }

            return $output;
        });
    }

    public function max(int $max): static
    {
        return $this->transform(static function (array $output) use ($max) {
            $outputLength = \count($output);

            if ($outputLength > $max) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MAX_CODE,
                        self::ERROR_MAX_TEMPLATE,
                        ['max' => $max, 'given' => $outputLength]
                    )
                );
            }

            return $output;
        });
    }

    public function length(int $length): static
    {
        return $this->transform(static function (array $output) use ($length) {
            $outputLength = \count($output);

            if ($outputLength !== $length) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_LENGTH_CODE,
                        self::ERROR_LENGTH_TEMPLATE,
                        ['length' => $length, 'given' => $outputLength]
                    )
                );
            }

            return $output;
        });
    }
}
