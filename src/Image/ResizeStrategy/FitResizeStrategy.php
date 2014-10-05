<?php

namespace Sokil\Image\ResizeStrategy;

class FitResizeStrategy extends \Sokil\Image\AbstractResizeStrategy
{
    public function resize($originalImageResource, $targetImageWidth, $targetImageHeight)
    {
        $originalImageWidth = imagesx($originalImageResource);
        $originalImageHeight = imagesy($originalImageResource);

        $widthRatio = ceil($originalImageWidth / $targetImageWidth);
        $heightRatio = ceil($originalImageHeight / $targetImageHeight);

        if($widthRatio > $heightRatio) {
            $ratio = $widthRatio;
        } else {
            $ratio = $heightRatio;
        }

        $newWidth = $originalImageWidth / $ratio;
        $newHeight = $originalImageHeight / $ratio;

        $targetImageResource = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled(
            $targetImageResource, 
            $originalImageResource, 
            0, 0, 
            0, 0, 
            $newWidth, $newHeight, 
            $originalImageWidth, $originalImageHeight
        );

        return $targetImageResource;
    }
}