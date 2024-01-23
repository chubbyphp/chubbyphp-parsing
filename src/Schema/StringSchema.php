<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class StringSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'string.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "string", "{{given}}" given';

    public const ERROR_MIN_CODE = 'string.min';
    public const ERROR_MIN_TEMPLATE = 'Min length {{min}}, {{given}} given';

    public const ERROR_MAX_CODE = 'string.max';
    public const ERROR_MAX_TEMPLATE = 'Max length {{max}}, {{given}} given';

    public const ERROR_LENGTH_CODE = 'string.length';
    public const ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    public const ERROR_CONTAINS_CODE = 'string.contains';
    public const ERROR_CONTAINS_TEMPLATE = '"{{given}}" does not contain "{{contain}}"';

    public const ERROR_STARTSWITH_CODE = 'string.startsWith';
    public const ERROR_STARTSWITH_TEMPLATE = '"{{given}}" does not starts with "{{startsWith}}"';

    public const ERROR_ENDSWITH_CODE = 'string.endsWith';
    public const ERROR_ENDSWITH_TEMPLATE = '"{{given}}" does not ends with "{{endsWith}}"';

    public const ERROR_REGEX_CODE = 'string.regex';
    public const ERROR_REGEX_TEMPLATE = '"{{given}}" does not regex "{{regex}}"';

    public const ERROR_EMAIL_CODE = 'string.email';
    public const ERROR_EMAIL_TEMPLATE = 'Invalid email "{{given}}"';

    public const ERROR_IP_CODE = 'string.ip';
    public const ERROR_IP_TEMPLATE = 'Invalid ip {{version}} "{{given}}"';

    public const ERROR_URL_CODE = 'string.url';
    public const ERROR_URL_TEMPLATE = 'Invalid url "{{given}}"';

    public const ERROR_UUID_CODE = 'string.uuid';
    public const ERROR_UUID_TEMPLATE = 'Invalid uuid {{version}} "{{given}}"';

    public const ERROR_INT_CODE = 'string.int';
    public const ERROR_INT_TEMPLATE = 'Invalid int "{{given}}"';

    public const ERROR_DATETIME_CODE = 'string.datetime';
    public const ERROR_DATETIME_TEMPLATE = 'Invalid datetime "{{given}}"';

    private const UUID_V4_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-(8|9|a|b)[0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const UUID_V5_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-(8|9|a|b)[0-9a-f]{3}-[0-9a-f]{12}$/i';

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_string($input)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            return $this->transformOutput($input);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function min(int $min): static
    {
        return $this->transform(static function (string $output) use ($min) {
            $outputLength = \strlen($output);

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
        return $this->transform(static function (string $output) use ($max) {
            $outputLength = \strlen($output);

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
        return $this->transform(static function (string $output) use ($length) {
            $outputLength = \strlen($output);

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

    public function contains(string $contains): static
    {
        return $this->transform(static function (string $output) use ($contains) {
            if (!str_contains($output, $contains)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_CONTAINS_CODE,
                        self::ERROR_CONTAINS_TEMPLATE,
                        ['given' => $output, 'contain' => $contains]
                    )
                );
            }

            return $output;
        });
    }

    public function startsWith(string $startsWith): static
    {
        return $this->transform(static function (string $output) use ($startsWith) {
            if (!str_starts_with($output, $startsWith)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_STARTSWITH_CODE,
                        self::ERROR_STARTSWITH_TEMPLATE,
                        ['given' => $output, 'startsWith' => $startsWith]
                    )
                );
            }

            return $output;
        });
    }

    public function endsWith(string $endsWith): static
    {
        return $this->transform(static function (string $output) use ($endsWith) {
            if (!str_ends_with($output, $endsWith)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_ENDSWITH_CODE,
                        self::ERROR_ENDSWITH_TEMPLATE,
                        ['given' => $output, 'endsWith' => $endsWith]
                    )
                );
            }

            return $output;
        });
    }

    public function regex(string $regex): static
    {
        if (false === @preg_match($regex, '')) {
            throw new \InvalidArgumentException(sprintf('Invalid regex "%s" given', $regex));
        }

        return $this->transform(static function (string $output) use ($regex) {
            if (0 === preg_match($regex, $output)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_REGEX_CODE,
                        self::ERROR_REGEX_TEMPLATE,
                        ['given' => $output, 'regex' => $regex]
                    )
                );
            }

            return $output;
        });
    }

    public function email(): static
    {
        return $this->transform(static function (string $output) {
            if (!filter_var($output, FILTER_VALIDATE_EMAIL)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_EMAIL_CODE,
                        self::ERROR_EMAIL_TEMPLATE,
                        ['given' => $output]
                    )
                );
            }

            return $output;
        });
    }

    public function ipV4(): static
    {
        return $this->transform(static function (string $output) {
            if (!filter_var($output, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_IP_CODE,
                        self::ERROR_IP_TEMPLATE,
                        ['version' => 'v4', 'given' => $output]
                    )
                );
            }

            return $output;
        });
    }

    public function ipV6(): static
    {
        return $this->transform(static function (string $output) {
            if (!filter_var($output, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_IP_CODE,
                        self::ERROR_IP_TEMPLATE,
                        ['version' => 'v6', 'given' => $output]
                    )
                );
            }

            return $output;
        });
    }

    public function url(): static
    {
        return $this->transform(static function (string $output) {
            if (!filter_var($output, FILTER_VALIDATE_URL)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_URL_CODE,
                        self::ERROR_URL_TEMPLATE,
                        ['given' => $output]
                    )
                );
            }

            return $output;
        });
    }

    public function uuidV4(): static
    {
        return $this->transform(static function (string $output) {
            if (0 === preg_match(self::UUID_V4_PATTERN, $output)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_UUID_CODE,
                        self::ERROR_UUID_TEMPLATE,
                        ['version' => 'v4', 'given' => $output]
                    )
                );
            }

            return $output;
        });
    }

    public function uuidV5(): static
    {
        return $this->transform(static function (string $output) {
            if (0 === preg_match(self::UUID_V5_PATTERN, $output)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_UUID_CODE,
                        self::ERROR_UUID_TEMPLATE,
                        ['version' => 'v5', 'given' => $output]
                    )
                );
            }

            return $output;
        });
    }

    public function trim(): static
    {
        return $this->transform(static fn (string $output) => trim($output));
    }

    public function lower(): static
    {
        return $this->transform(static fn (string $output) => strtolower($output));
    }

    public function upper(): static
    {
        return $this->transform(static fn (string $output) => strtoupper($output));
    }

    public function toInt(): static
    {
        return $this->transform(static function (string $output) {
            $intOutput = (int) $output;

            if ((string) $intOutput !== $output) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_INT_CODE,
                        self::ERROR_INT_TEMPLATE,
                        ['given' => $output]
                    )
                );
            }

            return $intOutput;
        });
    }

    public function toDateTime(): static
    {
        return $this->transform(static function (string $output) {
            try {
                return new \DateTimeImmutable($output);
            } catch (\Exception $e) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_DATETIME_CODE,
                        self::ERROR_DATETIME_TEMPLATE,
                        ['given' => $output]
                    )
                );
            }
        });
    }
}
