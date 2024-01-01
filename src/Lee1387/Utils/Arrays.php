<?php

namespace Lee1387\Utils;

class Arrays
{

    /**
     * @param array $array
     * @return array
     */
    public static function removeFirst(array $array) : array
    {
        $array[0] = null;
        array_shift($array);
        return $array;
    }

}