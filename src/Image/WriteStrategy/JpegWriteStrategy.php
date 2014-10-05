<?php

namespace Sokil\Image\WriteStrategy;

class JpegWriteStrategy extends \Sokil\Image\AbstractWriteStrategy
{
    private $_quality = 100;
    
    public function setQuality($quality)
    {
        $this->_quality = (int) $quality;
        return $this;
    }
    
    public function toFile($targetPath)
    {
        if(!in_array(strtolower(pathinfo($targetPath, PATHINFO_EXTENSION)), ['jpg', 'jpeg'])) {
            $targetPath .= '.jpg';
        }
        
        if(!imagejpeg($this->_resource, $targetPath, $this->_quality)) {
            throw new \Exception('Error writing JPEG file');
        }
    }
    
    public function toStdout()
    {
        imagejpeg($this->_resource, null, $this->_quality);
    }
}