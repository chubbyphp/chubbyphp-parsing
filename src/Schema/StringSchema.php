<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Enum\Uuid;
use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ErrorsException;

final class StringSchema extends AbstractSchemaInnerParse implements SchemaInterface
{
    public const string ERROR_TYPE_CODE = 'string.type';
    public const string ERROR_TYPE_TEMPLATE = 'Type should be "string", {{given}} given';

    public const string ERROR_LENGTH_CODE = 'string.length';
    public const string ERROR_LENGTH_TEMPLATE = 'Length {{length}}, {{given}} given';

    public const string ERROR_MIN_LENGTH_CODE = 'string.minLength';
    public const string ERROR_MIN_LENGTH_TEMPLATE = 'Min length {{min}}, {{given}} given';

    public const string ERROR_MAX_LENGTH_CODE = 'string.maxLength';
    public const string ERROR_MAX_LENGTH_TEMPLATE = 'Max length {{max}}, {{given}} given';

    public const string ERROR_CONTAINS_CODE = 'string.contains';
    public const string ERROR_CONTAINS_TEMPLATE = '{{given}} does not contain {{contains}}';

    public const string ERROR_INCLUDES_CODE = 'string.includes';
    public const string ERROR_INCLUDES_TEMPLATE = '{{given}} does not include {{includes}}';

    public const string ERROR_STARTSWITH_CODE = 'string.startsWith';
    public const string ERROR_STARTSWITH_TEMPLATE = '{{given}} does not starts with {{startsWith}}';

    public const string ERROR_ENDSWITH_CODE = 'string.endsWith';
    public const string ERROR_ENDSWITH_TEMPLATE = '{{given}} does not ends with {{endsWith}}';

    public const string ERROR_HOSTNAME_CODE = 'string.hostname';
    public const string ERROR_HOSTNAME_TEMPLATE = 'Invalid hostname {{given}}';

    public const string ERROR_DOMAIN_CODE = 'string.domain';
    public const string ERROR_DOMAIN_TEMPLATE = 'Invalid domain {{given}}';

    public const string ERROR_EMAIL_CODE = 'string.email';
    public const string ERROR_EMAIL_TEMPLATE = 'Invalid email {{given}}';

    public const string ERROR_IP_CODE = 'string.ip';
    public const string ERROR_IP_TEMPLATE = 'Invalid ip {{version}} {{given}}';

    public const string ERROR_MAC_CODE = 'string.mac';
    public const string ERROR_MAC_TEMPLATE = 'Invalid mac {{given}}';

    public const string ERROR_MATCH_CODE = 'string.match';
    public const string ERROR_MATCH_TEMPLATE = '{{given}} does not match {{match}}';

    public const string ERROR_PATTERN_CODE = 'string.pattern';
    public const string ERROR_PATTERN_TEMPLATE = '{{given}} does not pattern {{pattern}}';

    public const string ERROR_REGEXP_CODE = 'string.regexp';
    public const string ERROR_REGEXP_TEMPLATE = '{{given}} does not regexp {{regexp}}';

    public const string ERROR_URI_CODE = 'string.uri';
    public const string ERROR_URI_TEMPLATE = 'Invalid uri {{given}}';

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

    private const string UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-(\d{1})[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    public function length(int $length): static
    {
        return $this->postParse(static function (string $string) use ($length) {
            $stringLength = \strlen($string);

            if ($stringLength === $length) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_LENGTH_CODE,
                    self::ERROR_LENGTH_TEMPLATE,
                    ['length' => $length, 'given' => $stringLength]
                )
            );
        });
    }

    public function minLength(int $minLength): static
    {
        return $this->postParse(static function (string $string) use ($minLength) {
            $stringLength = \strlen($string);

            if ($stringLength >= $minLength) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MIN_LENGTH_CODE,
                    self::ERROR_MIN_LENGTH_TEMPLATE,
                    ['minLength' => $minLength, 'given' => $stringLength]
                )
            );
        });
    }

    public function maxLength(int $maxLength): static
    {
        return $this->postParse(static function (string $string) use ($maxLength) {
            $stringLength = \strlen($string);

            if ($stringLength <= $maxLength) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MAX_LENGTH_CODE,
                    self::ERROR_MAX_LENGTH_TEMPLATE,
                    ['maxLength' => $maxLength, 'given' => $stringLength]
                )
            );
        });
    }

    public function contains(string $contains): static
    {
        return $this->postParse(static function (string $string) use ($contains) {
            if (str_contains($string, $contains)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_CONTAINS_CODE,
                    self::ERROR_CONTAINS_TEMPLATE,
                    ['contains' => $contains, 'given' => $string]
                )
            );
        });
    }

    /**
     * @deprecated use contains
     */
    public function includes(string $includes): static
    {
        @trigger_error('Use contains instead', E_USER_DEPRECATED);

        return $this->postParse(static function (string $string) use ($includes) {
            if (str_contains($string, $includes)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_INCLUDES_CODE,
                    self::ERROR_INCLUDES_TEMPLATE,
                    ['includes' => $includes, 'given' => $string]
                )
            );
        });
    }

    public function startsWith(string $startsWith): static
    {
        return $this->postParse(static function (string $string) use ($startsWith) {
            if (str_starts_with($string, $startsWith)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_STARTSWITH_CODE,
                    self::ERROR_STARTSWITH_TEMPLATE,
                    ['startsWith' => $startsWith, 'given' => $string]
                )
            );
        });
    }

    public function endsWith(string $endsWith): static
    {
        return $this->postParse(static function (string $string) use ($endsWith) {
            if (str_ends_with($string, $endsWith)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_ENDSWITH_CODE,
                    self::ERROR_ENDSWITH_TEMPLATE,
                    ['endsWith' => $endsWith, 'given' => $string]
                )
            );
        });
    }

    public function hostname(): static
    {
        return $this->postParse(static function (string $string) {
            if (filter_var($string, FILTER_VALIDATE_DOMAIN)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_HOSTNAME_CODE,
                    self::ERROR_HOSTNAME_TEMPLATE,
                    ['given' => $string]
                )
            );
        });
    }

    /**
     * @deprecated use hostname
     */
    public function domain(): static
    {
        @trigger_error('Use hostname instead', E_USER_DEPRECATED);

        return $this->postParse(static function (string $string) {
            if (filter_var($string, FILTER_VALIDATE_DOMAIN)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_DOMAIN_CODE,
                    self::ERROR_DOMAIN_TEMPLATE,
                    ['given' => $string]
                )
            );
        });
    }

    public function email(): static
    {
        return $this->postParse(static function (string $string) {
            if (filter_var($string, FILTER_VALIDATE_EMAIL)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_EMAIL_CODE,
                    self::ERROR_EMAIL_TEMPLATE,
                    ['given' => $string]
                )
            );
        });
    }

    public function ipV4(): static
    {
        return $this->postParse(static function (string $string) {
            if (filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_IP_CODE,
                    self::ERROR_IP_TEMPLATE,
                    ['version' => 'v4', 'given' => $string]
                )
            );
        });
    }

    public function ipV6(): static
    {
        return $this->postParse(static function (string $string) {
            if (filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_IP_CODE,
                    self::ERROR_IP_TEMPLATE,
                    ['version' => 'v6', 'given' => $string]
                )
            );
        });
    }

    public function mac(): static
    {
        return $this->postParse(static function (string $string) {
            if (filter_var($string, FILTER_VALIDATE_MAC)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MAC_CODE,
                    self::ERROR_MAC_TEMPLATE,
                    ['given' => $string]
                )
            );
        });
    }

    /**
     * @deprecated: use pattern
     */
    public function match(string $match): static
    {
        @trigger_error('Use pattern instead', E_USER_DEPRECATED);

        if (false === @preg_match($match, '')) {
            throw new \InvalidArgumentException(\sprintf('Invalid match "%s" given', $match));
        }

        return $this->postParse(static function (string $string) use ($match) {
            $doesMatch = filter_var($string, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $match]]);

            if ($doesMatch) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_MATCH_CODE,
                    self::ERROR_MATCH_TEMPLATE,
                    ['match' => $match, 'given' => $string]
                )
            );
        });
    }

    public function pattern(string $pattern): static
    {
        if (false === @preg_match($pattern, '')) {
            throw new \InvalidArgumentException(\sprintf('Invalid pattern "%s" given', $pattern));
        }

        return $this->postParse(static function (string $string) use ($pattern) {
            $doesMatch = filter_var($string, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $pattern]]);

            if ($doesMatch) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_PATTERN_CODE,
                    self::ERROR_PATTERN_TEMPLATE,
                    ['pattern' => $pattern, 'given' => $string]
                )
            );
        });
    }

    /**
     * @deprecated: use pattern
     */
    public function regexp(string $regexp): static
    {
        @trigger_error('Use pattern instead', E_USER_DEPRECATED);

        if (false === @preg_match($regexp, '')) {
            throw new \InvalidArgumentException(\sprintf('Invalid regexp "%s" given', $regexp));
        }

        return $this->postParse(static function (string $string) use ($regexp) {
            $doesMatch = filter_var($string, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $regexp]]);

            if ($doesMatch) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_REGEXP_CODE,
                    self::ERROR_REGEXP_TEMPLATE,
                    ['regexp' => $regexp, 'given' => $string]
                )
            );
        });
    }

    public function uri(): static
    {
        return $this->postParse(static function (string $string) {
            if (filter_var($string, FILTER_VALIDATE_URL)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_URI_CODE,
                    self::ERROR_URI_TEMPLATE,
                    ['given' => $string]
                )
            );
        });
    }

    /**
     * @deprecated use uri
     */
    public function url(): static
    {
        @trigger_error('Use uri instead', E_USER_DEPRECATED);

        return $this->postParse(static function (string $string) {
            if (filter_var($string, FILTER_VALIDATE_URL)) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_URL_CODE,
                    self::ERROR_URL_TEMPLATE,
                    ['given' => $string]
                )
            );
        });
    }

    public function uuid(Uuid $version = Uuid::v4): static
    {
        return $this->postParse(static function (string $string) use ($version) {
            $matches = [];
            preg_match(self::UUID_PATTERN, $string, $matches);

            if ((int) ($matches[1] ?? '-1') === $version->value) {
                return $string;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_UUID_CODE,
                    self::ERROR_UUID_TEMPLATE,
                    ['version' => 'v'.$version->value, 'given' => $string]
                )
            );
        });
    }

    /**
     * @deprecated use uuid()
     */
    public function uuidV4(): static
    {
        return $this->uuid(Uuid::v4);
    }

    /**
     * @deprecated use uuid(Chubbyphp\Parsing\Enum\Uuid::v5)
     */
    public function uuidV5(): static
    {
        return $this->uuid(Uuid::v5);
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

            if (null !== $boolInput) {
                return $boolInput;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_BOOL_CODE,
                    self::ERROR_BOOL_TEMPLATE,
                    ['given' => $input]
                )
            );
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

            if (null !== $floatInput) {
                return $floatInput;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_FLOAT_CODE,
                    self::ERROR_FLOAT_TEMPLATE,
                    ['given' => $input]
                )
            );
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

            if (null !== $intInput) {
                return $intInput;
            }

            throw new ErrorsException(
                new Error(
                    self::ERROR_INT_CODE,
                    self::ERROR_INT_TEMPLATE,
                    ['given' => $input]
                )
            );
        })->nullable($this->nullable);
    }

    protected function innerParse(mixed $input): mixed
    {
        if (\is_string($input)) {
            return $input;
        }

        throw new ErrorsException(
            new Error(
                self::ERROR_TYPE_CODE,
                self::ERROR_TYPE_TEMPLATE,
                ['given' => $this->getDataType($input)]
            )
        );
    }
}
