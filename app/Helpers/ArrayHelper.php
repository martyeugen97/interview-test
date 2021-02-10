<?php

namespace App\Helpers;

class ArrayHelper
{
    public static function arrayEqualsIgnoreOrder(array $array1, array $array2)
    {
        return !array_diff($array1, $array2) && (count($array1) === count($array2));
    }
}