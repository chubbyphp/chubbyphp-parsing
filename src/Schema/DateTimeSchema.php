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

            return $this->transformOutput($input);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function min(\DateTimeImmutable $min): static
    {
        return $this->transform(static function (\DateTimeImmutable $output) use ($min) {
            if ($output < $min) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MIN_CODE,
                        self::ERROR_MIN_TEMPLATE,
                        ['min' => $min->format('c'), 'given' => $output->format('c')]
                    )
                );
            }

            return $output;
        });
    }

    public function max(\DateTimeImmutable $max): static
    {
        return $this->transform(static function (\DateTimeImmutable $output) use ($max) {
            if ($output > $max) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_MAX_CODE,
                        self::ERROR_MAX_TEMPLATE,
                        ['max' => $max->format('c'), 'given' => $output->format('c')]
                    )
                );
            }

            return $output;
        });
    }

    public function toInt(): static
    {
        return $this->transform(static fn (\DateTimeInterface $output) => $output->getTimestamp());
    }

    public function toString(): static
    {
        return $this->transform(static fn (\DateTimeInterface $output) => $output->format('c'));
    }
}
