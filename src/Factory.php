<?php

namespace Sokil\Image;

class Factory
{
    public function createImage()
    {
        return new Image;
    }
    
    public function openImage($image)
    {
        return new Image($image);
    }
}