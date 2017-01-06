<?php

namespace Sokil\Image;

abstract class AbstractElement
{
    public function __construct()
    {
        $this->init();
    }
    
    protected function init() {}
    
    /**
     * Draw element on image represented by $resouce at position [$x, $y]
     * @param resource $resource Image resource where to draw element
     * @param int $x X-coordinate
     * @param int $y Y-coordinate
     * @return \Sokil\Image\AbstractElement
     */
    abstract public function draw($resource, $x, $y);
}