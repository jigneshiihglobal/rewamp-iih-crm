<?php

namespace App\Helpers;

class CSSHelper
{
    public static $color_class_map = [
        "Aqua" => "custom-bg-Aqua",
        "Blue" => "custom-bg-Blue",
        "Brown" => "custom-bg-Brown",
        "Chocolate" => "custom-bg-Chocolate",
        "Cyan" => "custom-bg-Cyan",
        "Danger" => "bg-danger",
        "DarkBlue" => "custom-bg-DarkBlue",
        "DarkGreen" => "custom-bg-DarkGreen",
        "DarkMagenta" => "custom-bg-DarkMagenta",
        "DarkOliveGreen" => "custom-bg-DarkOliveGreen",
        "DarkOrange" => "custom-bg-DarkOrange",
        "DarkOrchid" => "custom-bg-DarkOrchid",
        "DarkRed" => "custom-bg-DarkRed",
        "DeepPink" => "custom-bg-DeepPink",
        "FireBrick" => "custom-bg-FireBrick",
        "ForestGreen" => "custom-bg-ForestGreen",
        "Gold" => "custom-bg-Gold",
        "Gradient Blue" => "bg-blue",
        "Gradient Orange" => "bg-orange",
        "Gradient Pink" => "bg-pink text-light",
        "Gradient Yellow" => "bg-yellow text-dark",
        "Gray" => "custom-bg-Gray",
        "Green" => "custom-bg-Green",
        "GreenYellow" => "custom-bg-GreenYellow",
        "HotPink" => "custom-bg-HotPink",
        "Indigo" => "custom-bg-Indigo",
        "Info" => "bg-info text-dark",
        "Lime" => "custom-bg-Lime",
        "LimeGreen" => "custom-bg-LimeGreen",
        "Magenta" => "custom-bg-Magenta",
        "Maroon" => "custom-bg-Maroon",
        "MediumBlue" => "custom-bg-MediumBlue",
        "MidnightBlue" => "custom-bg-MidnightBlue",
        "Navy" => "custom-bg-Navy",
        "Olive" => "custom-bg-Olive",
        "Orange" => "custom-bg-Orange",
        "OrangeRed" => "custom-bg-OrangeRed",
        "Orchid" => "custom-bg-Orchid",
        "Primary" => "bg-primary",
        "Purple" => "custom-bg-Purple",
        "Red" => "custom-bg-Red",
        "RoyalBlue" => "custom-bg-RoyalBlue",
        "SeaGreen" => "custom-bg-SeaGreen",
        "Secondary" => "bg-secondary",
        "Sienna" => "custom-bg-Sienna",
        "SteelBlue" => "custom-bg-SteelBlue",
        "Success" => "bg-success",
        "Teal" => "custom-bg-Teal",
        "Tomato" => "custom-bg-Tomato",
        "Yellow" => "custom-bg-Yellow",
        "YellowGreen" => "custom-bg-YellowGreen"
    ];

    public static function class(string $color): string
    {
        return array_key_exists($color, self::$color_class_map)
            ? self::$color_class_map[$color]
            : '';
    }

    public static function color(string $class): string
    {
        $color = array_search($class, self::$color_class_map);
        return $color ?? '';
    }
}
