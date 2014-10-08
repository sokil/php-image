<?php

namespace Sokil\Image\WriteStrategy;

class PngWriteStrategy extends \Sokil\Image\AbstractWriteStrategy
{
    private $_quality = 9;
    
    private $_filter = PNG_NO_FILTER;
    
    public function setQuality($quality)
    {
        $this->_quality = (int) $quality;
        if($this->_quality < 0 || $this->_quality > 9) {
            throw new \Exception('Quality of PNG must be between 0 and 9.');
        }
        
        return $this;
    }
    
    public function applyNoneFilter()
    {
        $this->_filter += PNG_FILTER_NONE;
        return $this;
    }
    
    public function applySubFilter()
    {
        $this->_filter += PNG_FILTER_SUB;
        return $this;
    }

    public function applyUpFilter()
    {
        $this->_filter += PNG_FILTER_UP;
        return $this;
    }
    
    public function applyAvgFilter()
    {
        $this->_filter += PNG_FILTER_AVG;
        return $this;
    }
    
    public function applyPaethFilter()
    {
        $this->_filter += PNG_FILTER_PAETH;
        return $this;
    }
    
    public function clearAllFilters()
    {
        $this->_filter = PNG_NO_FILTER;
        return $this;
    }
    
    public function applyAllFilters()
    {
        $this->_filter = PNG_ALL_FILTERS;
        return $this;
    }
    
    public function toFile($targetPath)
    {
        if('png' !== strtolower(pathinfo($targetPath, PATHINFO_EXTENSION))) {
            $targetPath .= '.png';
        }
        
        if(!imagepng($this->_resource, $targetPath, $this->_quality, $this->_filter)) {
            throw new \Exception('Error writing PNG file');
        }
    }
    
    public function toStdout()
    {
        imagepng($this->_resource, null, $this->_quality, $this->_filter);
    }
}