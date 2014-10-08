<?php

namespace Sokil\Image;

abstract class AbstractFilterStrategy
{
    protected $_resource;
    
    public function __construct($resource)
    {
        if(!is_resource($resource)  || 'gd' !== get_resource_type($resource)) {
            throw new \Exception('Resource must be given');
        }
        
        $this->_resource = $resource;
    }
    
    /**
     * @return resource filtered gd resource
     */
    abstract public function filter();
}