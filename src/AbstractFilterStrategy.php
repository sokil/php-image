<?php

namespace Sokil\Image;

abstract class AbstractFilterStrategy
{    
    /**
     * @return resource filtered gd resource
     */
    abstract public function filter($resource);
}