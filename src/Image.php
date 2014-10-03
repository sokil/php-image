<?php

namespace Sokil;

class Image
{
    const RESIZE_MODE_CROP  = 0;
    const RESIZE_MODE_FIT   = 1;
    const RESIZE_MODE_CASHE = 2;
    const RESIZE_MODE_SCALE = 3;

    /**
     * full path to image file
     * @var type
     */
    private $_filename;

    private $_imageInfo;

    private $_options;
    
    private $_originalSize = array();

    public function __construct($filename, $options = array())
    {
        // test source filename for accessibility
        if(!file_exists($filename)) {
            throw new \Exception('File '  . $filename . ' not found');
        }
        
        if(!is_readable($filename)) {
            throw new \Exception('File '  . $filename . ' not readable');
        }
        
        $this->_filename = $filename;
        
        $imageInfo = $this->getImageInfo();
        $this->_originalSize['width'] = $imageInfo[0];
        $this->_originalSize['height'] = $imageInfo[1];
         
        $this->_options = array_merge(array(
            'height'        => $this->getOriginalHeight(),
            'width'         => $this->getOriginalWidth(),
            'quality'       => 100,
            'resizeMode'    => self::RESIZE_MODE_SCALE,
            'sourceRegion'  => null,
        ), $options);

    }

    public function getImageInfo()
    {
        if(is_null($this->_imageInfo))
        {
            // test for correct source image
            if(!($imageInfo = @getimagesize($this->_filename))) {
                throw new \Exception('Wrong image format');
            }

            if(!in_array($imageInfo[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))) {
                throw new \Exception('Only image of JPEG, PNG and GIF formats supported');
            }

            $this->_imageInfo = $imageInfo;
        }

        return $this->_imageInfo;
    }
    
    public function getOriginalWidth()
    {
        return $this->_originalSize['width'];
    }
    
    public function getOriginalHeight()
    {
        return $this->_originalSize['height'];
    }

    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $filename
     * @return mixed
     */
    public function resize($targetPath)
    { 
        // test if target image suported
        $targetFormat = pathinfo($targetPath, PATHINFO_EXTENSION);
        if(!in_array($targetFormat, array('jpg', 'png', 'gif'))) {
            throw new \Exception('Converting to ' . $targetFormat . ' not supported');
        }
        
        // test target path
        $targetDir = dirname($targetPath);
        if(!file_exists($targetDir))
            mkdir($targetDir, 0777, true);

        $imageInfo = $this->getImageInfo();

        // read source image
        switch($imageInfo[2])
        {
            case IMAGETYPE_JPEG:    $image = @imagecreatefromjpeg($this->_filename); break;
            case IMAGETYPE_PNG:     $image = @imagecreatefrompng($this->_filename); break;
            case IMAGETYPE_GIF:     $image = @imagecreatefromgif($this->_filename); break;
        }

        // test if file opened
        if(!$image) {
            return null;
        }

        // get source region
        if(!empty($this->_options['sourceRegion'])) {
            $o = $this->_options['sourceRegion'];
            $sourceImage = imagecreatetruecolor($o['width'], $o['height']);
            imagecopyresampled($sourceImage, $image, 0, 0, $o['x'], $o['y'], $o['width'], $o['height'], $o['width'], $o['height']);
            $image = $sourceImage;
        }

        // transform image
        switch($this->_options['resizeMode'])
        {
            case self::RESIZE_MODE_CROP:    $image = $this->_crop($image, $this->_options['width'], $this->_options['height']); break;
            case self::RESIZE_MODE_FIT:     $image = $this->_fit($image, $this->_options['width'], $this->_options['height']); break;
            case self::RESIZE_MODE_CASHE:   $image = $this->_cashe($image, $this->_options['width'], $this->_options['height']); break;
            case self::RESIZE_MODE_SCALE:   $image = $this->_scale($image, $this->_options['width'], $this->_options['height']); break;
            default:
                throw new Exception('Unknown resize mode'); break;
        }

        // remove old file if exist
        if(file_exists($targetPath))
            unlink($targetPath);

        // save image
        switch($targetFormat)
        {
            case 'jpg':
                imagejpeg($image, $targetPath, $this->_options['quality']);
                break;
            case 'png':
                imagepng($image, $targetPath);
                break;
            case 'gif':
                imagegif($image, $targetPath);
                break;
        }

        return new self($targetPath, $this->_options);
    }

    public function setSourceRegion($region)
    {
        $this->_options['sourceRegion'] = $region;
    }

    public function setHeight($height)
    {
        if(!is_numeric($height) || $height < 1)
            throw new Exception('Wrong image height set');

        $this->_options['height'] = (int) $height;

        return $this;
    }

    public function setWidth($width)
    {
        if(!is_numeric($width) || $width < 1)
            throw new Exception('Wrong image width set');

        $this->_options['width'] = (int) $width;

        return $this;
    }

    public function setQuality($quality)
    {
        if(!is_numeric($quality) || $quality < 0 || $quality > 100)
            throw new \Exception('Quality must be between 0 and 100');

        $this->_options['quality'] = $quality;

        return $this;
    }

    public function setResizeMode($mode)
    {
        $this->_options['resizeMode'] = $mode;

        return $this;
    }

    private function _fit($image, $width, $height)
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        $rWidth = ceil($origWidth / $width);
        $rHeight = ceil($origHeight / $height);

        if($rWidth > $rHeight) {
            $ratio = $rWidth;
        } else {
            $ratio = $rHeight;
        }

        $newWidth = $origWidth / $ratio;
        $newHeight = $origHeight / $ratio;

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        return $resized;
    }

    public function _crop($image, $newWidth, $newHeight)
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        $origRatio = $origHeight / $origWidth;
        $newRatio = $newHeight / $newWidth;

        if($origRatio < $newRatio)
        {
                $dst_w=$newWidth;
                $dst_h=$newHeight;
                $dst_x=0;
                $dst_y=0;

                $src_w=$origHeight/$newRatio;
                $src_h=$origHeight;
                $src_x=($origWidth-$src_w)/2;
                $src_y=0;
        }
        else
        {
                $dst_w=$newWidth;
                $dst_h=$newHeight;
                $dst_x=0;
                $dst_y=0;

                $src_w=$origWidth;
                $src_h=$origWidth*$newRatio;
                $src_x=0;
                $src_y=($origHeight-$src_h)/2;
        }

        $cropped = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($cropped, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        return $cropped;
    }

    private function _cashe($image, $newWidth, $newHeight)
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        $origRatio = $origHeight / $origWidth;
        $newRatio = $newHeight / $newWidth;

        if($origRatio < $newRatio) {
            $dst_w=$newWidth;
            $dst_h=$newWidth*$origRatio;
            $dst_x=0;
            $dst_y=($newHeight-$dst_h)/2;

            $src_w=$origWidth;
            $src_h=$origHeight;
            $src_x=0;
            $src_y=0;
        }
        else {
            $dst_w=$newHeight/$origRatio;
            $dst_h=$newHeight;
            $dst_x=($newWidth-$dst_w)/2;
            $dst_y=0;

            $src_w=$origWidth;
            $src_h=$origHeight;
            $src_x=0;
            $src_y=0;

        }

        $cashe = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($cashe, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        return $cashe;
    }

    private function _scale($image, $newWidth, $newHeight)
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        if($origWidth < $newWidth)
            $newWidth = $origWidth;

        if($origHeight < $newHeight)
            $newHeight = $origHeight;

        $origRatio = $origHeight / $origWidth;
        $newRatio = $newHeight / $newWidth;

        $dst_x=0;
        $dst_y=0;
        $src_x=0;
        $src_y=0;

        if($origRatio < $newRatio) {
            $dst_w=$newWidth;
            $dst_h=$newWidth*$origRatio;

            $src_w=$origWidth;
            $src_h=$origHeight;
        }
        else {
            $dst_w=$newHeight/$origRatio;
            $dst_h=$newHeight;

            $src_w=$origWidth;
            $src_h=$origHeight;

        }

        $scaled = imagecreatetruecolor($dst_w, $dst_h);
        imagecopyresampled($scaled, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        return $scaled;
    }
}
