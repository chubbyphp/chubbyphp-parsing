<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\ParserErrorException;

final class StringSchema extends AbstractSchema implements SchemaInterface
{
    public const UUID_V4 = 4;
    public const UUID_V5 = 5;
    private const VALID_URL_OPTIONS = [
        0,
        FILTER_FLAG_PATH_REQUIRED,
        FILTER_FLAG_QUERY_REQUIRED,
        FILTER_FLAG_PATH_REQUIRED | FILTER_FLAG_QUERY_REQUIRED,
    ];
    private const VALID_IP_OPTIONS = [
        0,
        FILTER_FLAG_IPV4,
        FILTER_FLAG_IPV6,
        FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6,
    ];

    private const VALID_UUID_OPTIONS = [0, self::UUID_V4, self::UUID_V5];

    private const UUID_V4_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-(8|9|a|b)[0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const UUID_V5_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-(8|9|a|b)[0-9a-f]{3}-[0-9a-f]{12}$/i';

    private const INVALID_OPTION_TEMPLATE = 'Invalid option "%s" given';

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!\is_string($input)) {
                throw new ParserErrorException(
                    sprintf('Type should be "string" "%s" given', $this->getDataType($input))
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

    public function min(int $length): static
    {
        return $this->transform(static function (string $output) use ($length) {
            $outputLength = \strlen($output);

            if ($outputLength < $length) {
                throw new ParserErrorException(sprintf('Length should at min %d, %d given', $length, $outputLength));
            }

            return $output;
        });
    }

    public function max(int $length): static
    {
        return $this->transform(static function (string $output) use ($length) {
            $outputLength = \strlen($output);

            if ($outputLength > $length) {
                throw new ParserErrorException(sprintf('Length should at max %d, %d given', $length, $outputLength));
            }

            return $output;
        });
    }

    public function length(int $length): static
    {
        return $this->transform(static function (string $output) use ($length) {
            $outputLength = \strlen($output);

            if ($outputLength !== $length) {
                throw new ParserErrorException(sprintf('Length should be %d, %d given', $length, $outputLength));
            }

            return $output;
        });
    }

    public function email(): static
    {
        return $this->transform(static function (string $output) {
            if (!filter_var($output, FILTER_VALIDATE_EMAIL)) {
                throw new ParserErrorException(sprintf('"%s" is not a valid email', $output));
            }

            return $output;
        });
    }

    /**
     * @param 0|FILTER_FLAG_PATH_REQUIRED|FILTER_FLAG_QUERY_REQUIRED $options
     */
    public function url(int $options = 0): static
    {
        if (!\in_array($options, self::VALID_URL_OPTIONS, true)) {
            throw new \InvalidArgumentException(sprintf(self::INVALID_OPTION_TEMPLATE, $options));
        }

        return $this->transform(static function (string $output) use ($options) {
            if (!filter_var($output, FILTER_VALIDATE_URL, $options)) {
                throw new ParserErrorException(sprintf('"%s" is not a valid url', $output));
            }

            return $output;
        });
    }

    /**
     * @param 0|self::UUID_V4|self::UUID_V5 $options
     */
    public function uuid(int $options = 0): static
    {
        if (!\in_array($options, self::VALID_UUID_OPTIONS, true)) {
            throw new \InvalidArgumentException(sprintf(self::INVALID_OPTION_TEMPLATE, $options));
        }

        return $this->transform(static function (string $output) use ($options) {
            if (self::UUID_V4 === $options) {
                if (0 === preg_match(self::UUID_V4_PATTERN, $output)) {
                    throw new ParserErrorException(sprintf('"%s" is not a valid uuid v4', $output));
                }

                return $output;
            }

            if (self::UUID_V5 === $options) {
                if (0 === preg_match(self::UUID_V5_PATTERN, $output)) {
                    throw new ParserErrorException(sprintf('"%s" is not a valid uuid v5', $output));
                }

                return $output;
            }

            if (0 === preg_match(self::UUID_V4_PATTERN, $output) && 0 === preg_match(self::UUID_V5_PATTERN, $output)) {
                throw new ParserErrorException(sprintf('"%s" is not a valid uuid', $output));
            }

            return $output;
        });
    }

    public function regex(string $pattern): static
    {
        if (false === @preg_match($pattern, '')) {
            throw new \InvalidArgumentException(sprintf('Invalid pattern "%s" given', $pattern));
        }

        return $this->transform(static function (string $output) use ($pattern) {
            if (0 === preg_match($pattern, $output)) {
                throw new ParserErrorException(sprintf('"%s" does not match pattern "%s"', $output, $pattern));
            }

            return $output;
        });
    }

    public function contains(string $contains): static
    {
        return $this->transform(static function (string $output) use ($contains) {
            if (!str_contains($output, $contains)) {
                throw new ParserErrorException(sprintf('"%s" does not contains with "%s"', $output, $contains));
            }

            return $output;
        });
    }

    public function startsWith(string $startsWith): static
    {
        return $this->transform(static function (string $output) use ($startsWith) {
            if (!str_starts_with($output, $startsWith)) {
                throw new ParserErrorException(sprintf('"%s" does not starts with "%s"', $output, $startsWith));
            }

            return $output;
        });
    }

    public function endsWith(string $endsWith): static
    {
        return $this->transform(static function (string $output) use ($endsWith) {
            if (!str_ends_with($output, $endsWith)) {
                throw new ParserErrorException(sprintf('"%s" does not ends with "%s"', $output, $endsWith));
            }

            return $output;
        });
    }

    public function dateTime(): static
    {
        return $this->transform(static function (string $output) {
            try {
                new \DateTimeImmutable($output);
            } catch (\Exception $e) {
                throw new ParserErrorException(sprintf('"%s" is not a valid datetime', $output));
            }

            return $output;
        });
    }

    /**
     * @param 0|FILTER_FLAG_IPV4|FILTER_FLAG_IPV6 $options
     */
    public function ip(int $options = 0): static
    {
        if (!\in_array($options, self::VALID_IP_OPTIONS, true)) {
            throw new \InvalidArgumentException(sprintf(self::INVALID_OPTION_TEMPLATE, $options));
        }

        return $this->transform(static function (string $output) use ($options) {
            if (!filter_var($output, FILTER_VALIDATE_IP, $options)) {
                throw new ParserErrorException(sprintf('"%s" is not a valid ip', $output));
            }

            return $output;
        });
    }

    public function trim(): static
    {
        return $this->transform(static fn (string $output) => trim($output));
    }

    public function toLower(): static
    {
        return $this->transform(static fn (string $output) => strtolower($output));
    }

    public function toUpper(): static
    {
        return $this->transform(static fn (string $output) => strtoupper($output));
    }
}
