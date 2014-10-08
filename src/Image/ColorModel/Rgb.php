<?php

namespace Sokil\Image\ColorModel;

class Rgb
{
    private $red;
    
    private $green;
    
    private $blue;
    
    private $alpha;
    
    public function __construct($red, $green, $blue, $alpha = 0)
    {
        $this->red = $red;
        
        $this->green = $green;
        
        $this->blue = $blue;
        
        $this->alpha = $alpha;
    }
    
    /**
     * Get [R, G, B, Alpha] from '#ARGB'
     * @param string $hexColor
     */
    public static function fromHex($hexColor)
    {
        $hexArray = str_split(ltrim($hexColor, '#'), 2);
        $decimalArray = array_map('hexdec', $hexArray);

        $chunksNum = count($decimalArray);
        if ($chunksNum < 3 || $chunksNum > 4) {
            throw new \InvalidArgumentException('Wrong hex color specified');
        }

        // no alpha passed
        if (3 == $chunksNum) {
            $decimalArray[] = 0;
        } else {
            $alpha = floor(array_shift($decimalArray) / 2);
            $decimalArray[] = $alpha;
        }

        return new self(
            $decimalArray[0],
            $decimalArray[1],
            $decimalArray[2],
            $decimalArray[3]
        );
    }
    
    public static function fromInt($intColor)
    {
        return new self(
            ($intColor >> 16) & 0xFF,
            ($intColor >> 8) & 0xFF,
            $intColor & 0xFF
        );
    }
    
    public static function fromIntAsArray($intColor)
    {
        return [
            ($intColor >> 16) & 0xFF,
            ($intColor >> 8) & 0xFF,
            $intColor & 0xFF,
        ];
    }
    
    public function getRed()
    {
        return $this->red;
    }
    
    public function getGreen()
    {
        return $this->green;
    }
    
    public function getBlue()
    {
        return $this->blue;
    }
    
    public function getAlpha()
    {
        return $this->alpha;
    }
    
    public function toArray()
    {
        return [$this->red, $this->green, $this->blue, $this->alpha];
    }
}