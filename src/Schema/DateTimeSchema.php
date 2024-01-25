<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class DateTimeSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'datetime.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "\DateTimeInterface", "{{given}}" given';

    public const ERROR_MIN_CODE = 'datetime.min';
    public const ERROR_MIN_TEMPLATE = 'Min datetime {{min}}, {{given}} given';

    public const ERROR_MAX_CODE = 'datetime.max';
    public const ERROR_MAX_TEMPLATE = 'Max datetime {{max}}, {{given}} given';

    public const ERROR_RANGE_CODE = 'datetime.range';
    public const ERROR_RANGE_TEMPLATE = 'Min datetime {{min}}, Max datetime {{max}}, {{given}} given';

    public function parse(mixed $input): mixed
    {
        $input ??= $this->default;

        if (null === $input && $this->nullable) {
            return null;
        }

        try {
            if (!$input instanceof \DateTimeInterface) {
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

    public function min(\DateTimeImmutable $min): static
    {
        return $this->middleware(static function (\DateTimeImmutable $datetime) use ($min) {
            if ($datetime < $min) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MIN_CODE,
                        self::ERROR_MIN_TEMPLATE,
                        ['min' => $min->format('c'), 'given' => $datetime->format('c')]
                    )
                );
            }

            return $datetime;
        });
    }

    public function max(\DateTimeImmutable $max): static
    {
        return $this->middleware(static function (\DateTimeImmutable $datetime) use ($max) {
            if ($datetime > $max) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MAX_CODE,
                        self::ERROR_MAX_TEMPLATE,
                        ['max' => $max->format('c'), 'given' => $datetime->format('c')]
                    )
                );
            }

            return $datetime;
        });
    }

    public function range(\DateTimeImmutable $min, \DateTimeImmutable $max): static
    {
        return $this->middleware(static function (\DateTimeImmutable $datetime) use ($min, $max) {
            if ($datetime < $min || $datetime > $max) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_RANGE_CODE,
                        self::ERROR_RANGE_TEMPLATE,
                        ['min' => $min->format('c'), 'max' => $max->format('c'), 'given' => $datetime->format('c')]
                    )
                );
            }

            return $datetime;
        });
    }

    public function toInt(): static
    {
        return $this->middleware(static fn (\DateTimeInterface $datetime) => $datetime->getTimestamp());
    }

    public function toString(): static
    {
        return $this->middleware(static fn (\DateTimeInterface $datetime) => $datetime->format('c'));
    }
}
