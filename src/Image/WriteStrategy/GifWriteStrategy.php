<?php

namespace Sokil\Image\WriteStrategy;

class GifWriteStrategy extends \Sokil\Image\AbstractWriteStrategy
{   
    public function toFile($targetPath)
    {
        if('gif' !== strtolower(pathinfo($targetPath, PATHINFO_EXTENSION))) {
            $targetPath .= '.gif';
        }
        
        if(!imagegif($this->_resource, $targetPath)) {
            throw new \Exception('Error writing GIF file');
        }
    }
    
    public function toStdout()
    {
        imagegif($this->_resource);
    }
}