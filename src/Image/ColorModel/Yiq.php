<?php

namespace Sokil\Image\ColorModel;

/**
 * @link https://en.wikipedia.org/wiki/YIQ
 */
class Yiq
{
    private $y;
    
    private $i;
    
    private $q;
    
    public function __construct($y, $i, $q)
    {
        $this->y = $y;
        
        $this->i = $i;
        
        $this->q = $q;
    }
    
    /**
     * Get Y from RGB in YIQ color model
     * 
     * @param \Sokil\Image\ColorModel\Rgb $rgb 
     * @return int Y
     */
    public static function getYFromRgb(Rgb $rgb)
    {
        return floor(($rgb->getRed() * 0.299) + ($rgb->getGreen() * 0.587) + ($rgb->getBlue() * 0.114));
    }
    
    /**
     * Get I from RGB in YIQ color model
     * 
     * @param \Sokil\Image\ColorModel\Rgb $rgb
     * @return int I
     */
    public static function getIFromRgb(Rgb $rgb)
    {
        return floor(($rgb->getRed() * 0.596) - ($rgb->getGreen() * 0.274) - ($rgb->getBlue() * 0.322));
    }
    
    /**
     * Get Q from RGB in YIQ color model
     * 
     * @param \Sokil\Image\ColorModel\Rgb $rgb 
     * @return int Q
     */
    public static function getQFromRgb(Rgb $rgb)
    {
        return floor(($rgb->getRed() * 0.211) - ($rgb->getGreen() * 0.522) + ($rgb->getBlue() * 0.311));
    }
    
    public static function fromRgb(Rgb $rgb)
    {
        return new self(
            self::getYFromRgb($rgb),
            self::getIFromRgb($rgb),
            self::getQFromRgb($rgb)
        );
    }
}