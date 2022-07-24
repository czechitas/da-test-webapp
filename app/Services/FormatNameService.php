<?php

declare(strict_types=1);

namespace CzechitasApp\Services;

use Tamtamchik\NameCase\Formatter;
use Vokativ\Name;

class FormatNameService
{
    private Name $nameLib;

    public function __construct(Name $nameLib)
    {
        $this->nameLib = $nameLib;
    }

    public function vocative(string $name, bool $firstNameOnly = true): string
    {
        $splitted = \collect(\explode(' ', $name))->filter();

        if ($firstNameOnly) {
            $splitted = $splitted->slice(0, 1);
        }

        return $splitted->map(function (string $name) {
            if (\mb_strtolower($name) === $this->formatCase($name)) {
                return \mb_strtolower($name);
            }

            return $this->formatCase($this->nameLib->vokativ($name));
        })->implode(' ');
    }

    public function isWoman(string $name): bool
    {
        return !$this->nameLib->isMale($name);
    }

    public function formatCase(string $name): string
    {
        return Formatter::nameCase($name);
    }
}
