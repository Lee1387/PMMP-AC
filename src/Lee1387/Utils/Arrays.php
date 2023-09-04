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
        array_shift($array);
        return $array;
    }

}