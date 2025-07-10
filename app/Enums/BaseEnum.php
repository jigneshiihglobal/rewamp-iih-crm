<?php

namespace App\Enums;

use ReflectionClass;

class BaseEnum
{
    public static function values()
    {
        return array_values(static::all());
    }

    public static function all()
    {
        $reflection = new ReflectionClass(static::class);
        $constants = $reflection->getReflectionConstants();

        $result = array();
        foreach ($constants as $constant) {
            $result[$constant->getName()] = $constant->getValue();
        }

        return $result;
    }
}
