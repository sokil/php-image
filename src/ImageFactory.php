<?php

namespace Sokil;

class ImageFactory
{
    /**
     * Create empty image
     * @param int $width
     * @param int $height
     * @return \Sokil\Image
     */
    public function createImage($width, $height)
    {
        $image = new \Sokil\Image;
        return $image->create($width, $height);
    }
    
    /**
     * Open existed image
     * @param string|resource $image path to file on disk or image resource
     * @return \Sokil\Image
     */
    public function openImage($image)
    {
        return new Image($image);
    }
}