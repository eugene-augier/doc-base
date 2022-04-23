<?php

namespace PHPDoc\Internal\String;

use InvalidArgumentException;

class Conv
{
    public static function toString($item, int $tab = 0): string
    {
        return match (gettype($item)) {
            'string', 'integer', 'double' => $item,
            'NULL' => 'NULL',
            'boolean' => $item ? 'true' : 'false',
            'array' => static::arrayToString($item, $tab + 2),
            'object' => "Object ".$item::class,
            default => throw new InvalidArgumentException(sprintf('Use of invalid type "%s"', $item)),
        };
    }

    private static function arrayToString(array $arr, int $tab = 0): string
    {
        $str = "[\n";
        foreach ($arr as $key => $value) {
            $str .= sprintf(
                '%s%s: %s',
                str_repeat(' ', $tab),
                $key,
                static::toString($value, $tab + 2)
            )."\n";
        }

        $tabs = str_repeat(' ', $tab - 2);

        return $str.$tabs."]";
    }
}
