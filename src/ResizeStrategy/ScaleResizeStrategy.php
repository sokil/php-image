<?php

namespace Sokil\Image\ResizeStrategy;

class ScaleResizeStrategy extends \Sokil\Image\AbstractResizeStrategy
{
    public function resize($originalImageResource, $targetImageWidth, $targetImageHeight)
    {
        $origWidth = imagesx($originalImageResource);
        $origHeight = imagesy($originalImageResource);

        if($origWidth < $targetImageWidth) {
            $targetImageWidth = $origWidth;
        }

        if($origHeight < $targetImageHeight) {
            $targetImageHeight = $origHeight;
        }

        $origRatio = $origHeight / $origWidth;
        $newRatio = $targetImageHeight / $targetImageWidth;

        $dst_x=0;
        $dst_y=0;
        $src_x=0;
        $src_y=0;

        if($origRatio < $newRatio) {
            $dst_w = $targetImageWidth;
            $dst_h = $targetImageWidth*$origRatio;

            $src_w = $origWidth;
            $src_h = $origHeight;
        }
        else {
            $dst_w = $targetImageHeight/$origRatio;
            $dst_h = $targetImageHeight;

            $src_w = $origWidth;
            $src_h = $origHeight;

        }

        $targetImageResource = imagecreatetruecolor($dst_w, $dst_h);
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