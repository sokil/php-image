<?php

namespace Sokil\Image\ResizeStrategy;

class CropResizeStrategy extends \Sokil\Image\AbstractResizeStrategy
{

    public function resize($originalImageResource, $targetImageWidth, $targetImageHeight)
    {
        $originalImageWidth = imagesx($originalImageResource);
        $originalImageHeight = imagesy($originalImageResource);

        $origRatio = $originalImageHeight / $originalImageWidth;
        $newRatio = $targetImageHeight / $targetImageWidth;

        if ($origRatio < $newRatio) {
            $dst_w = $targetImageWidth;
            $dst_h = $targetImageHeight;
            $dst_x = 0;
            $dst_y = 0;

            $src_w = $originalImageHeight / $newRatio;
            $src_h = $originalImageHeight;
            $src_x = ($originalImageWidth - $src_w) / 2;
            $src_y = 0;
        } else {
            $dst_w = $targetImageWidth;
            $dst_h = $targetImageHeight;
            $dst_x = 0;
            $dst_y = 0;

            $src_w = $originalImageWidth;
            $src_h = $originalImageWidth * $newRatio;
            $src_x = 0;
            $src_y = ($originalImageHeight - $src_h) / 2;
        }

        $targetImageResource = imagecreatetruecolor(
            $targetImageWidth, 
            $targetImageHeight
        );
        
        imagecopyresampled(
            $targetImageResource, 
            $originalImageResource, 
            $dst_x, $dst_y, 
            $src_x, $src_y, 
            $dst_w, $dst_h, 
            $src_w, $src_h
        );
        
        return $targetImageResource;
    }

}
