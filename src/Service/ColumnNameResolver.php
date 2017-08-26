<?php

namespace Ttskch\Pheetsu\Service;

class ColumnNameResolver
{
    /**
     * @param $number
     * @return string
     */
    public function getName($number)
    {
        $digits = [];

        do {
            if ($number % 26 === 0) {
                $digits[] = 26;
                $number--;
            } else {
                $digits[] = $number % 26;
            }
        } while (($number = intval($number / 26)) > 0);

        foreach ($digits as $i => $digit) {
            $digits[$i] = chr(ord('A') + $digit - 1);
        }

        return implode('', array_reverse($digits));
    }

    /**
     * @param $name
     * @return int
     */
    public function getNumber($name)
    {
        $number = 0;

        $digits = array_reverse(str_split($name));

        $i = 0;
        foreach ($digits as $digit) {
            $decimalDigit = ord($digit) - ord('A') + 1;
            $number += $decimalDigit * (26 ** $i++);
        }

        return $number;
    }
}
