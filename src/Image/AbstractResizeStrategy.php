<?php

namespace Sokil\Image;

/**
 * Image resize strategy.
 * It reseives original images resource and destination image size and 
 * resize image.
 */
abstract class AbstractResizeStrategy
{
    /**
     * 
     * @param resource $originalImageResource original image resource
     * @param int $targetImageWidth destination width
     * @param int $targetImageHeight destination height
     * @return resource destination image resource
     */
    abstract public function resize($originalImageResource, $targetImageWidth, $targetImageHeight);
}