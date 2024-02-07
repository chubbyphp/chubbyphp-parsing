<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing\Schema;

use Chubbyphp\Parsing\Error;
use Chubbyphp\Parsing\ParserErrorException;

final class DateTimeSchema extends AbstractSchema implements SchemaInterface
{
    public const ERROR_TYPE_CODE = 'datetime.type';
    public const ERROR_TYPE_TEMPLATE = 'Type should be "\DateTimeInterface", {{given}} given';

    public const ERROR_FROM_CODE = 'datetime.from';
    public const ERROR_FROM_TEMPLATE = 'From datetime {{from}}, {{given}} given';

    public const ERROR_TO_CODE = 'datetime.to';
    public const ERROR_TO_TEMPLATE = 'To datetime {{to}}, {{given}} given';

    public function parse(mixed $input): mixed
    {
        try {
            $input = $this->dispatchPreParses($input);

            if (null === $input && $this->nullable) {
                return null;
            }

            if (!$input instanceof \DateTimeInterface) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TYPE_CODE,
                        self::ERROR_TYPE_TEMPLATE,
                        ['given' => $this->getDataType($input)]
                    )
                );
            }

            return $this->dispatchPostParses($input);
        } catch (ParserErrorException $parserErrorException) {
            if ($this->catch) {
                return ($this->catch)($input, $parserErrorException);
            }

            throw $parserErrorException;
        }
    }

    public function from(\DateTimeImmutable $from): static
    {
        return $this->postParse(static function (\DateTimeImmutable $datetime) use ($from) {
            if ($datetime < $from) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_FROM_CODE,
                        self::ERROR_FROM_TEMPLATE,
                        ['from' => $from->format('c'), 'given' => $datetime->format('c')]
                    )
                );
            }

            return $datetime;
        });
    }

    public function to(\DateTimeImmutable $to): static
    {
        return $this->postParse(static function (\DateTimeImmutable $datetime) use ($to) {
            if ($datetime > $to) {
                throw new ParserErrorException(
                    new Error(
                        self::ERROR_TO_CODE,
                        self::ERROR_TO_TEMPLATE,
                        ['to' => $to->format('c'), 'given' => $datetime->format('c')]
                    )
                );
            }

            return $datetime;
        });
    }

    public function toInt(): IntSchema
    {
        return (new IntSchema())->preParse(function ($input) {
            /** @var null|\DateTimeInterface $input */
            $input = $this->parse($input);

            return null !== $input ? $input->getTimestamp() : null;
        })->nullable($this->nullable);
    }

    public function toString(): StringSchema
    {
        return (new StringSchema())->preParse(function ($input) {
            /** @var null|\DateTimeInterface $input */
            $input = $this->parse($input);

            return null !== $input ? $input->format('c') : null;
        })->nullable($this->nullable);
    }
}
