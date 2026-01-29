<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class StringSchema extends AbstractSchema implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'string.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "string", {{given}} given';

    public const string ERROR_LENGTH_CODE = 'string.length';
    public const string ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    public const string ERROR_MIN_LENGTH_CODE = 'string.minLength';
    public const string ERROR_MIN_LENGTH_TEMPLATE = 'Min length {{min}}, {{given}} given';

    public const string ERROR_MAX_LENGTH_CODE = 'string.maxLength';
    public const string ERROR_MAX_LENGTH_TEMPLATE = 'Max length {{max}}, {{given}} given';

    public const string ERROR_INCLUDES_CODE = 'string.includes';
    public const string ERROR_INCLUDES_TEMPLATE = '{{given}} does not include {{includes}}';

    public const string ERROR_STARTSWITH_CODE = 'string.startsWith';
    public const string ERROR_STARTSWITH_TEMPLATE = '{{given}} does not starts with {{startsWith}}';

    public const string ERROR_ENDSWITH_CODE = 'string.endsWith';
    public const string ERROR_ENDSWITH_TEMPLATE = '{{given}} does not ends with {{endsWith}}';

    public const string ERROR_MATCH_CODE = 'string.match';
    public const string ERROR_MATCH_TEMPLATE = '{{given}} does not match {{match}}';

    public const string ERROR_REGEXP_CODE = 'string.regexp';
    public const string ERROR_REGEXP_TEMPLATE = '{{given}} does not regexp {{regexp}}';

    public const string ERROR_EMAIL_CODE = 'string.email';
    public const string ERROR_EMAIL_TEMPLATE = 'Invalid email {{given}}';

    public const string ERROR_IP_CODE = 'string.ip';
    public const string ERROR_IP_TEMPLATE = 'Invalid ip {{version}} {{given}}';

    public const string ERROR_URL_CODE = 'string.url';
    public const string ERROR_URL_TEMPLATE = 'Invalid url {{given}}';

    public const string ERROR_UUID_CODE = 'string.uuid';
    public const string ERROR_UUID_TEMPLATE = 'Invalid uuid {{version}} {{given}}';

    public const string ERROR_BOOL_CODE = 'string.bool';
    public const string ERROR_BOOL_TEMPLATE = 'Cannot convert {{given}} to bool';

    public const string ERROR_FLOAT_CODE = 'string.float';
    public const string ERROR_FLOAT_TEMPLATE = 'Cannot convert {{given}} to float';

    public const string ERROR_INT_CODE = 'string.int';
    public const string ERROR_INT_TEMPLATE = 'Cannot convert {{given}} to int';

    public const string ERROR_DATETIME_CODE = 'string.datetime';
    public const string ERROR_DATETIME_TEMPLATE = 'Cannot convert {{given}} to datetime';

    private const string UUID_V4_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-(8|9|a|b)[0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const string UUID_V5_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-(8|9|a|b)[0-9a-f]{3}-[0-9a-f]{12}$/i';

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            if (!\is_string($input)) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            return $this->dispatchPostParses($input);
        } catch (ErrorsException $e) {
            if ($this->catch) {
                return ($this->catch)($input, $e);
            }

            throw $e;
        }
    }

    public function length(int $length): static
    {
        return $this->postParse(static function (string $string) use ($length) {
            $stringLength = \strlen($string);

            if ($stringLength !== $length) {
                throw new ErrorsException(
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
        return $this->postParse(static function (string $string) use ($minLength) {
            $stringLength = \strlen($string);

            if ($stringLength < $minLength) {
                throw new ErrorsException(
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
        return $this->postParse(static function (string $string) use ($maxLength) {
            $stringLength = \strlen($string);

            if ($stringLength > $maxLength) {
                throw new ErrorsException(
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

    public function includes(string $includes): static
    {
        return $this->postParse(static function (string $string) use ($includes) {
            if (!str_contains($string, $includes)) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_INCLUDES_CODE,
                        self::ERROR_INCLUDES_TEMPLATE,
                        ['includes' => $includes, 'given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function startsWith(string $startsWith): static
    {
        return $this->postParse(static function (string $string) use ($startsWith) {
            if (!str_starts_with($string, $startsWith)) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_STARTSWITH_CODE,
                        self::ERROR_STARTSWITH_TEMPLATE,
                        ['startsWith' => $startsWith, 'given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function endsWith(string $endsWith): static
    {
        return $this->postParse(static function (string $string) use ($endsWith) {
            if (!str_ends_with($string, $endsWith)) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_ENDSWITH_CODE,
                        self::ERROR_ENDSWITH_TEMPLATE,
                        ['endsWith' => $endsWith, 'given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function regexp(string $regexp): static
    {
        if (false === @preg_match($regexp, '')) {
            throw new \InvalidArgumentException(\sprintf('Invalid regexp "%s" given', $regexp));
        }

        return $this->postParse(static function (string $string) use ($regexp) {
            $doesMatch = filter_var($string, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $regexp]]);

            if (false === $doesMatch) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_REGEXP_CODE,
                        self::ERROR_REGEXP_TEMPLATE,
                        ['regexp' => $regexp, 'given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    /**
     * @deprecated: use regexp
     */
    public function match(string $match): static
    {
        @trigger_error('Use regexp instead', E_USER_DEPRECATED);

        if (false === @preg_match($match, '')) {
            throw new \InvalidArgumentException(\sprintf('Invalid match "%s" given', $match));
        }

        return $this->postParse(static function (string $string) use ($match) {
            $doesMatch = filter_var($string, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $match]]);

            if (false === $doesMatch) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_MATCH_CODE,
                        self::ERROR_MATCH_TEMPLATE,
                        ['match' => $match, 'given' => $string]
                    )
                );
            }

            return $string;
        });
    }

    public function email(): static
    {
        return $this->postParse(static function (string $string) {
            if (!filter_var($string, FILTER_VALIDATE_EMAIL)) {
                throw new ErrorsException(
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
        return $this->postParse(static function (string $string) {
            if (!filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                throw new ErrorsException(
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
        return $this->postParse(static function (string $string) {
            if (!filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                throw new ErrorsException(
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
        return $this->postParse(static function (string $string) {
            if (!filter_var($string, FILTER_VALIDATE_URL)) {
                throw new ErrorsException(
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
        return $this->postParse(static function (string $string) {
            if (0 === preg_match(self::UUID_V4_PATTERN, $string)) {
                throw new ErrorsException(
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
        return $this->postParse(static function (string $string) {
            if (0 === preg_match(self::UUID_V5_PATTERN, $string)) {
                throw new ErrorsException(
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
        return $this->postParse(static fn (string $string) => trim($string));
    }

    public function trimStart(): static
    {
        return $this->postParse(static fn (string $string) => ltrim($string));
    }

    public function trimEnd(): static
    {
        return $this->postParse(static fn (string $string) => rtrim($string));
    }

    public function toLowerCase(): static
    {
        return $this->postParse(static fn (string $string) => strtolower($string));
    }

    public function toUpperCase(): static
    {
        return $this->postParse(static fn (string $string) => strtoupper($string));
    }

    public function toDateTime(): DateTimeSchema
    {
        return (new DateTimeSchema())->preParse(function ($input): ?\DateTimeImmutable {
            /** @var null|string $input */
            $input = $this->parse($input);

            if (null === $input) {
                return null;
            }

            try {
                $dateTime = new \DateTimeImmutable($input);

                $errors = \DateTimeImmutable::getLastErrors();

                // @infection-ignore-all: php < 8.2 returned an array even if there are no errors
                if (false === $errors || 0 === $errors['warning_count'] && 0 === $errors['error_count']) {
                    return $dateTime;
                }
            } catch (\Exception) { // NOSONAR: supress the exception to throw a more specific one
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_DATETIME_CODE,
                    self::ERROR_DATETIME_TEMPLATE,
                    ['given' => $input]
                )
            );
        })->nullable($this->nullable);
    }

    public function toBool(): BoolSchema
    {
        return (new BoolSchema())->preParse(function ($input): ?bool {
            /** @var null|string $input */
            $input = $this->parse($input);

            if (null === $input) {
                return null;
            }

            $boolInput = filter_var($input, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

            if (null === $boolInput) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_BOOL_CODE,
                        self::ERROR_BOOL_TEMPLATE,
                        ['given' => $input]
                    )
                );
            }

            return $boolInput;
        })->nullable($this->nullable);
    }

    public function toFloat(): FloatSchema
    {
        return (new FloatSchema())->preParse(function ($input): ?float {
            /** @var null|string $input */
            $input = $this->parse($input);

            if (null === $input) {
                return null;
            }

            $floatInput = filter_var($input, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

            if (null === $floatInput) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_FLOAT_CODE,
                        self::ERROR_FLOAT_TEMPLATE,
                        ['given' => $input]
                    )
                );
            }

            return $floatInput;
        })->nullable($this->nullable);
    }

    public function toInt(): IntSchema
    {
        return (new IntSchema())->preParse(function ($input): ?int {
            /** @var null|string $input */
            $input = $this->parse($input);

            if (null === $input) {
                return null;
            }

            $intInput = filter_var($input, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if (null === $intInput) {
                throw new ErrorsException(
                    new Error(
                        self::ERROR_INT_CODE,
                        self::ERROR_INT_TEMPLATE,
                        ['given' => $input]
                    )
                );
            }

            return $intInput;
        })->nullable($this->nullable);
    }
}
