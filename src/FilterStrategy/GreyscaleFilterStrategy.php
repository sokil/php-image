<?php

namespace Sokil\Image\FilterStrategy;

use Sokil\Image\ColorModel\Rgb;
use Sokil\Image\ColorModel\Yiq;

class GreyscaleFilterStrategy extends \Sokil\Image\AbstractFilterStrategy
{
    public function filter($resource)
    {
        if(!is_resource($resource)  || 'gd' !== get_resource_type($resource)) {
            throw new \Exception('Resource must be given');
        }
        
        $width = imagesx($resource);
        
        $height = imagesy($resource);
        
        $greyscaleImageResource = imagecreatetruecolor(
            $width, 
            $height
        );

        // prepare greyscale palette
        for ($c = 0; $c < 256; $c++) {
            $palette[$c] = imagecolorallocate($greyscaleImageResource, $c, $c, $c);
        }

        // set pixels
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($resource, $x, $y);
                $grey = Yiq::getYFromRgbArray(Rgb::fromIntAsArray($rgb));
                imagesetpixel($greyscaleImageResource, $x, $y, $palette[$grey]);
            }
        }

        return $greyscaleImageResource;
    }
}