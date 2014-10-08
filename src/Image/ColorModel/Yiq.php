<?php

namespace Sokil\Image\ColorModel;

/**
 * @link https://en.wikipedia.org/wiki/YIQ
 */
class Yiq
{
    
    public function __construct()
    {

    }
    
    /**
     * Get Y from RGB in YIQ color model
     * 
     * @param array $rgb array [red, green, blue]
     * @return int Y
     */
    public static function getYFromRgb(Rgb $rgb)
    {
        return floor(($rgb->getRed() * 0.299) + ($rgb->getGreen() * 0.587) + ($rgb->getBlue() * 0.114));
    }
}