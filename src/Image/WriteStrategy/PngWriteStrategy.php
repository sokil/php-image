<?php

namespace Sokil\Image\WriteStrategy;

class PngWriteStrategy extends \Sokil\Image\AbstractWriteStrategy
{
    private $_quality = 100;
    
    public function setQuality($quality)
    {
        $this->_quality = (int) $quality;
        return $this;
    }
    
    public function toFile($targetPath)
    {
        if('png' !== strtolower(pathinfo($targetPath, PATHINFO_EXTENSION))) {
            $targetPath .= '.png';
        }
        
        if(!imagepng($this->_resource, $targetPath, $this->_quality)) {
            throw new \Exception('Error writing PNG file');
        }
    }
    
    public function toStdout()
    {
        imagepng($this->_resource, null);
    }
}