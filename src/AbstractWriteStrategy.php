<?php

namespace Sokil\Image;

abstract class AbstractWriteStrategy
{
    /**
     * if null - write to STDOUT
     * 
     * @var string|null path to file where to write image
     */
    protected $_targetPath;

    /**
     * Write resource to file
     */
    public function toFile($targetPath)
    {
        $this->_targetPath = $targetPath;
        
        return $this;
    }
    
    abstract public function write($resource);
}