<?php

namespace Sokil\Image\ResizeStrategy;

class CacheResizeStrategy extends \Sokil\Image\AbstractResizeStrategy
{
    public function resize($originalImageResource, $targetImageWidth, $targetImageHeight)
    {
        $origWidth = imagesx($originalImageResource);
        $origHeight = imagesy($originalImageResource);

        $origRatio = $origHeight / $origWidth;
        $newRatio = $targetImageHeight / $targetImageWidth;

        if($origRatio < $newRatio) {
            $dst_w=$targetImageWidth;
            $dst_h=$targetImageWidth*$origRatio;
            $dst_x=0;
            $dst_y=($targetImageHeight-$dst_h)/2;

            $src_w=$origWidth;
            $src_h=$origHeight;
            $src_x=0;
            $src_y=0;
        }
        else {
            $dst_w=$targetImageHeight/$origRatio;
            $dst_h=$targetImageHeight;
            $dst_x=($targetImageWidth-$dst_w)/2;
            $dst_y=0;

            $src_w=$origWidth;
            $src_h=$origHeight;
            $src_x=0;
            $src_y=0;

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