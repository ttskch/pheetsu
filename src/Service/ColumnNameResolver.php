<?php

namespace Ttskch\Pheetsu\Service;

class ColumnNameResolver
{
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

    public function getNumber($name)
    {
        // todo
    }
}
