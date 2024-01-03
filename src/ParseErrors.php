<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

final class ParseErrors extends \RuntimeException implements ParseErrorInterface
{
    /**
     * @var array<int|string, ParseErrorInterface>
     */
    private array $parseErrors;

    /**
     * @param array<ParseErrorInterface> $parseErrors
     */
    public function __construct(array $parseErrors)
    {
        foreach ($parseErrors as $key => $parseError) {
            if (!$parseError instanceof ParseErrorInterface) {
                $type = \is_object($parseError) ? $parseError::class : \gettype($parseError);

                throw new \InvalidArgumentException(sprintf('Argument #1 value of #%s ($parseErrors) must be of type ParseErrorInterface, %s given', (string) $key, $type));
            }
        }

        $this->parseErrors = $parseErrors;
    }

    public function __toString(): string
    {
        return json_encode($this->getData());
    }

    /**
     * @return array<ParseErrorInterface>
     */
    public function getParseErrors(): array
    {
        return $this->parseErrors;
    }

    public function getData(): array|string
    {
        $data = [];
        foreach ($this->parseErrors as $key => $parseError) {
            $data[$key] = $parseError->getData();
        }

        return $data;
    }
}
