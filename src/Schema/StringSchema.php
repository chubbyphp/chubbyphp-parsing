<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class StringSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'string.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "string", "{{given}}" given';

    public const ERROR_LENGTH_CODE = 'string.length';
    public const ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    public const ERROR_MIN_LENGTH_CODE = 'string.minLength';
    public const ERROR_MIN_LENGTH_TEMPLATE = 'Min length {{min}}, {{given}} given';

    public const ERROR_MAX_LENGTH_CODE = 'string.maxLength';
    public const ERROR_MAX_LENGTH_TEMPLATE = 'Max length {{max}}, {{given}} given';

    public const ERROR_CONTAINS_CODE = 'string.contains';
    public const ERROR_CONTAINS_TEMPLATE = '"{{given}}" does not contain "{{contains}}"';

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

            return $this->dispatchMiddlewares($input);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function length(int $length): static
    {
        return $this->middleware(static function (string $string) use ($length) {
            $stringLength = \strlen($string);

            if ($stringLength !== $length) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_LENGTH_CODE,
                        self::ERROR_LENGTH_TEMPLATE,
                        ['length' => $length, 'given' => $stringLength]
                    )
                );
            }

            return $string;
        });
    }

    public function minLength(int $minLength): static
    {
        return $this->middleware(static function (string $string) use ($minLength) {
            $stringLength = \strlen($string);

            if ($stringLength < $minLength) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MIN_LENGTH_CODE,
                        self::ERROR_MIN_LENGTH_TEMPLATE,
                        ['minLength' => $minLength, 'given' => $stringLength]
                    )
                );
            }

            return $string;
        });
    }

    public function maxLength(int $maxLength): static
    {
        return $this->middleware(static function (string $string) use ($maxLength) {
            $stringLength = \strlen($string);

            if ($stringLength > $maxLength) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MAX_LENGTH_CODE,
                        self::ERROR_MAX_LENGTH_TEMPLATE,
                        ['maxLength' => $maxLength, 'given' => $stringLength]
                    )
                );
            }

            return $string;
        });
    }

    public function contains(string $contains): static
    {
        return $this->middleware(static function (string $string) use ($contains) {
            if (!str_contains($string, $contains)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_CONTAINS_CODE,
                        self::ERROR_CONTAINS_TEMPLATE,
                        ['given' => $string, 'contains' => $contains]
                    )
                );
            }

            return $string;
        });
    }

    public function startsWith(string $startsWith): static
    {
        return $this->middleware(static function (string $string) use ($startsWith) {
            if (!str_starts_with($string, $startsWith)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_STARTSWITH_CODE,
                        self::ERROR_STARTSWITH_TEMPLATE,
                        ['given' => $string, 'startsWith' => $startsWith]
                    )
                );
            }

            return $string;
        });
    }

    public function endsWith(string $endsWith): static
    {
        return $this->middleware(static function (string $string) use ($endsWith) {
            if (!str_ends_with($string, $endsWith)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_ENDSWITH_CODE,
                        self::ERROR_ENDSWITH_TEMPLATE,
                        ['given' => $string, 'endsWith' => $endsWith]
                    )
                );
            }

            return $string;
        });
    }

    public function regex(string $regex): static
    {
        if (false === @preg_match($regex, '')) {
            throw new \InvalidArgumentException(sprintf('Invalid regex "%s" given', $regex));
        }

        return $this->middleware(static function (string $string) use ($regex) {
            if (0 === preg_match($regex, $string)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_REGEX_CODE,
                        self::ERROR_REGEX_TEMPLATE,
                        ['given' => $string, 'regex' => $regex]
                    )
                );
            }

            return $string;
        });
    }

    public function email(): static
    {
        return $this->middleware(static function (string $string) {
            if (!filter_var($string, FILTER_VALIDATE_EMAIL)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_EMAIL_CODE,
                        self::ERROR_EMAIL_TEMPLATE,
                        ['given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function ipV4(): static
    {
        return $this->middleware(static function (string $string) {
            if (!filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_IP_CODE,
                        self::ERROR_IP_TEMPLATE,
                        ['version' => 'v4', 'given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function ipV6(): static
    {
        return $this->middleware(static function (string $string) {
            if (!filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_IP_CODE,
                        self::ERROR_IP_TEMPLATE,
                        ['version' => 'v6', 'given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function url(): static
    {
        return $this->middleware(static function (string $string) {
            if (!filter_var($string, FILTER_VALIDATE_URL)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_URL_CODE,
                        self::ERROR_URL_TEMPLATE,
                        ['given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function uuidV4(): static
    {
        return $this->middleware(static function (string $string) {
            if (0 === preg_match(self::UUID_V4_PATTERN, $string)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_UUID_CODE,
                        self::ERROR_UUID_TEMPLATE,
                        ['version' => 'v4', 'given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function uuidV5(): static
    {
        return $this->middleware(static function (string $string) {
            if (0 === preg_match(self::UUID_V5_PATTERN, $string)) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_UUID_CODE,
                        self::ERROR_UUID_TEMPLATE,
                        ['version' => 'v5', 'given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function trim(): static
    {
        return $this->middleware(static fn (string $string) => trim($string));
    }

    public function lower(): static
    {
        return $this->middleware(static fn (string $string) => strtolower($string));
    }

    public function upper(): static
    {
        return $this->middleware(static fn (string $string) => strtoupper($string));
    }

    public function toInt(): static
    {
        return $this->middleware(static function (string $string) {
            $intOutput = (int) $string;

            if ((string) $intOutput !== $string) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_INT_CODE,
                        self::ERROR_INT_TEMPLATE,
                        ['given' => $string]
                    )
                );
            }

            return $intOutput;
        });
    }

    public function toDateTime(): static
    {
        return $this->middleware(static function (string $string) {
            try {
                $dateTime = new \DateTimeImmutable($string);

                $errors = \DateTimeImmutable::getLastErrors();

                // @infection-ignore-all: php < 8.2 returned an array even if there are no errors
                if (false === $errors || 0 === $errors['warning_count'] && 0 === $errors['error_count']) {
                    return $dateTime;
                }
            } catch (\Exception) { // NOSONAR: supress the exception to throw a more specific one
            }

            throw new ParserErrorException(
                new Error(
                    self::ERROR_DATETIME_CODE,
                    self::ERROR_DATETIME_TEMPLATE,
                    ['given' => $string]
                )
            );
        });
    }
}
