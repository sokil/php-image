<?php

namespace Sokil\Image;

abstract class AbstractWriteStrategy
{
    /**
     * if null - write to STDOUT
     * 
     * @var string|null path to file where to write image
     */
    protected $targetPath;

    /**
     * Write resource to file
     */
    public function toFile($targetPath)
    {
        $this->targetPath = $targetPath;
        
        return $this;
    }
    
    abstract public function write($resource);
}