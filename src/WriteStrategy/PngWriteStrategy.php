<?php

namespace Sokil\Image\WriteStrategy;

use Sokil\Image\AbstractWriteStrategy;

class PngWriteStrategy extends AbstractWriteStrategy
{
    private $quality = 9;
    
    private $filter = PNG_NO_FILTER;
    
    public function setQuality($quality)
    {
        $this->quality = (int) $quality;
        if ($this->quality < 0 || $this->quality > 9) {
            throw new ImageException('Quality of PNG must be between 0 and 9.');
        }
        
        return $this;
    }
    
    public function applyNoneFilter()
    {
        $this->filter += PNG_FILTER_NONE;
        return $this;
    }
    
    public function applySubFilter()
    {
        $this->filter += PNG_FILTER_SUB;
        return $this;
    }

    public function applyUpFilter()
    {
        $this->filter += PNG_FILTER_UP;
        return $this;
    }
    
    public function applyAvgFilter()
    {
        $this->filter += PNG_FILTER_AVG;
        return $this;
    }
    
    public function applyPaethFilter()
    {
        $this->filter += PNG_FILTER_PAETH;
        return $this;
    }
    
    public function clearAllFilters()
    {
        $this->filter = PNG_NO_FILTER;
        return $this;
    }
    
    public function applyAllFilters()
    {
        $this->filter = PNG_ALL_FILTERS;
        return $this;
    }
    
    public function write($resource)
    {
        if(!is_resource($resource)  || 'gd' !== get_resource_type($resource)) {
            throw new \Exception('Resource must be given');
        }

        if (!empty($this->targetPath)) {
            $targetPath = $this->targetPath;
            if('png' !== strtolower(pathinfo($targetPath, PATHINFO_EXTENSION))) {
                $targetPath .= '.png';
            }
        } else {
            $targetPath = null;
        }

        if (!imagepng($resource, $targetPath, $this->quality, $this->filter)) {
            throw new \Exception('Error writing PNG file');
        }
    }
}