<?php

declare(strict_types=1);

namespace CzechitasApp\Models\Enums;

use ReflectionClass;

/**
 * Copied from: https://github.com/slevomat/csob-gateway/blob/master/src/Type/Enum.php
 */
abstract class Enum
{
    /** @var mixed */
    private $value;

    /** @var array<mixed> */
    private static $availableValues;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        self::checkValue($value);
        $this->value = $value;
    }

    /**
     * @param mixed $value
     */
    private static function checkValue($value): void
    {
        if (!self::isValidValue($value)) {
            throw new \Exception(
                "Value {$value} is not valid, possible values: " . \implode(', ', self::getAvailableValues()),
            );
        }
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function equals(self $enum): bool
    {
        if (static::class !== $enum::class) {
            throw new \Exception('Invalid enum type ' . $enum::class . ', expected ' . static::class);
        }

        return $this->equalsValue($enum->getValue());
    }

    /**
     * @param mixed $value
     */
    public function equalsValue($value): bool
    {
        self::checkValue($value);

        return $this->getValue() === $value;
    }

    /**
     * @param mixed $value
     */
    private static function isValidValue($value): bool
    {
        return \in_array($value, self::getAvailableValues(), true);
    }

    /**
     * Get available values in current Enum
     *
     * @param  bool $onlyValues If is true, only values are returned, if false, 'key' in array is name of constant
     * @return array<string>|array<string, string> Enum values
     */
    public static function getAvailableValues(bool $onlyValues = true): array
    {
        $index = static::class;
        if (!isset(self::$availableValues[$index])) {
            $classReflection = new ReflectionClass(static::class);
            self::$availableValues[$index] = $classReflection->getConstants();
        }
        if ($onlyValues) {
            return \array_values(self::$availableValues[$index]);
        }

        return self::$availableValues[$index];
    }

    /**
     * Get constant value by its name, or null if constant does not exists
     *
     * @return mixed|null
     */
    public static function getConstant(string $name)
    {
        $values = \array_keys(static::getAvailableValues(false));
        if (\in_array($name, $values)) {
            return \constant('static::' . $name);
        }

        return null;
    }
}
