<?php

declare(strict_types=1);

namespace CzechitasApp\Services;

class VariableSymbolService
{
    public const PREPEND_NUMBER = 9;
    public const PREPEND_NOT_MODULO_NUMBER = 9;
    public const WEIGHTS = [6, 3, 7, 9, 10, 5, 8, 4, 2, 1];
    public const MIN_ID_LENGTH = 5;

    public function generate(int $baseId): string
    {
        return $this->generateCustom(
            $baseId,
            self::MIN_ID_LENGTH,
            self::PREPEND_NUMBER,
            self::PREPEND_NOT_MODULO_NUMBER,
        );
    }

    public function generateCustom(int $baseId, int $minIdLength, int $prepend, int $prependNotModulo): string
    {
        $formattedId = \str_pad((string)$baseId, $minIdLength, '0', \STR_PAD_LEFT);

        $result = $this->addMod11Parity(\sprintf('%d%s', $prepend, $formattedId));
        if ($result === null) {
            $result = $this->addMod11Parity(\sprintf(
                '%d%d%s',
                $prepend,
                $prependNotModulo,
                $formattedId,
            ));
        }

        return $result;
    }

    private function addMod11Parity(string $formattedId): ?string
    {
        // Only 9 pad - last one is parity
        $padded = \str_pad($formattedId, 9, '0', \STR_PAD_LEFT);
        $checkSum = 0;
        $paddedLength = \strlen($padded);
        for ($i = 0; $i < $paddedLength; $i += 1) {
            $checkSum += self::WEIGHTS[$i] * (int)$padded[$i];
        }
        $remain = $checkSum % 11;

        if ($remain === 1) {
            // if remains 1 -> we would have to add 10, which is not possible
            return null;
        }

        return \sprintf('%s%d', $formattedId, $remain === 0 ? 0 : 11 - $remain);
    }

    public function validate(string $variableSymbol): bool
    {
        $regex = \sprintf('/^%d[0-9]{%d,9}$/', self::PREPEND_NUMBER, self::MIN_ID_LENGTH + 1);
        if (!\preg_match($regex, $variableSymbol)) {
            return false;
        }
        $variableSymbol = \str_pad($variableSymbol, 10, '0', \STR_PAD_LEFT);

        // Check main part
        $checkSum = 0;
        $variableSymbolLength = \strlen($variableSymbol);
        for ($i = 0; $i < $variableSymbolLength; $i += 1) {
            $checkSum += self::WEIGHTS[$i] * (int)$variableSymbol[$i];
        }

        return $checkSum % 11 === 0;
    }
}
