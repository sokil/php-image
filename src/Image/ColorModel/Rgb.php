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
    
    public static function createBlack()
    {
        return new self(0, 0, 0);
    }
    
    public static function createWhite()
    {
        return new self(255, 255, 255);
    }
    
    /**
     * Get Rgb color from any source
     * 
     * @param int|string|array $color
     * @return \sSokil\Image\ColorModel\Rgb
     * @throws \InvalidArgumentException
     */
    public static function normalize($color)
    {
        // already Rgb
        if($color instanceof self) {
            return $color;
        }
        
        // int
        if(is_int($color)) {
            return self::fromInt($color);
        }
        
        // hex
        if (is_string($color)) {
            return self::fromHex($color);
        } 
        
        // array
        if (is_array($color)) {
            if (count($color) < 3 || count($color) > 4) {
                throw new \InvalidArgumentException('Wrong color specified');
            }
            // check is alpha specified
            if (!isset($color[4])) {
                $color[4] = 127;
            }
            
            return new self($color[0], $color[1], $color[2], $color[3]);
        }
        
        throw new \InvalidArgumentException('Wrong color specified');
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
        return array(
            ($intColor >> 16) & 0xFF,
            ($intColor >> 8) & 0xFF,
            $intColor & 0xFF,
        );
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
        return array($this->red, $this->green, $this->blue, $this->alpha);
    }
}