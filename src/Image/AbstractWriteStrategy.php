<?php

namespace Sokil\Image;

abstract class AbstractWriteStrategy
{
    protected $_resource;
    
    protected $_targetPath;

    public function __construct($resource)
    {
        $this->_resource = $resource;
        if(!is_resource($resource)) {
            throw new \Exception('Resource must be given');
        }
    }

    /**
     * Write resource to file
     */
    abstract public function toFile($targetPath);
    
    /**
     * Write resource to STDOUT
     */
    abstract public function toStdout();
}