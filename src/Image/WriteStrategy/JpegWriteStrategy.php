<?php

namespace Sokil\Image\WriteStrategy;

class JpegWriteStrategy extends \Sokil\Image\AbstractWriteStrategy
{
    private $_quality = 100;
    
    public function setQuality($quality)
    {
        $this->_quality = (int) $quality;
        if($this->_quality < 0 || $this->_quality > 100) {
            throw new \Exception('Quality of JPEG must be between 0 and 100.');
        }
        
        return $this;
    }
    
    public function write($resource)
    {
        if(!is_resource($resource)  || 'gd' !== get_resource_type($resource)) {
            throw new \Exception('Resource must be given');
        }
        
        $targetPath = $this->_targetPath;
        
        if(!in_array(strtolower(pathinfo($targetPath, PATHINFO_EXTENSION)), array('jpg', 'jpeg'))) {
            $targetPath .= '.jpg';
        }
        
        if(!imagejpeg($resource, $targetPath, $this->_quality)) {
            throw new \Exception('Error writing JPEG file');
        }
    }
}