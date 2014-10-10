<?php

namespace Sokil;

use Sokil\Image\ColorModel\Rgb;
use Sokil\Image\AbstractElement;

class Image
{
    private $_resource;
    
    private $_width;
    
    private $_height;

    public function __construct($image = null)
    {
        // load image
        if ($image) {
            if (is_string($image)) {
                $this->loadFile($image);
            } elseif (is_resource($image)) {
                $this->loadResource($image);
            } else {
                throw new \Exception('Must be image resource or filename, ' . gettype($image) . ' given');
            }
        }
    }
    
    public function create($width, $height)
    {
        return $this->loadResource(imagecreatetruecolor($width, $height));
    }

    public function loadFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception('File ' . $filename . ' not found');
        }

        if (!is_readable($filename)) {
            throw new \Exception('File ' . $filename . ' not readable');
        }

        $imageInfo = @getimagesize($filename);
        if (!$imageInfo) {
            throw new \Exception('Wrong image format');
        }

        if (!in_array($imageInfo[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))) {
            throw new \Exception('Only image of JPEG, PNG and GIF formats supported');
        }

        $this->_width = $imageInfo[0];

        $this->_height = $imageInfo[1];

        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $this->_resource = @imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_PNG:
                $this->_resource = @imagecreatefrompng($filename);
                break;
            case IMAGETYPE_GIF:
                $this->_resource = @imagecreatefromgif($filename);
                break;
        }

        return $this;
    }

    public function loadResource($resource)
    {
        if (!(is_resource($resource) && 'gd' === get_resource_type($resource))) {
            throw new \Exception('Must be resource of type "gd", ' . gettype($resource) . ' given');
        }

        $this->_resource = $resource;

        $this->_width = imagesx($resource);

        $this->_height = imagesy($resource);

        return $this;
    }
    
    public function getResource()
    {
        return $this->_resource;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    public function getHeight()
    {
        return $this->_height;
    }
    
    public function fill($color, $x = 0, $y = 0)
    {
        $color = Rgb::normalize($color);
        
        imagefill(
            $this->_resource, 
            $x, 
            $y, 
            imagecolorallocatealpha(
                $this->_resource, 
                $color->getRed(), 
                $color->getGreen(), 
                $color->getBlue(), 
                $color->getAlpha()
            )
        );
        
        return $this;
    }

    public function resize(\Sokil\Image\AbstractResizeStrategy $resizeStrategy, $width, $height)
    {
        $this->loadResource($resizeStrategy->resize($this->_resource, $width, $height));
        
        return $this;
    }

    /**
     * @return \Sokil\Image\AbstractWriteStrategy
     */
    public function write(\Sokil\Image\AbstractWriteStrategy $writeStrategy)
    {
        $writeStrategy->write($this->_resource);
        
        return $this;
    }

    /**
     * 
     * @param type $angle
     * @param array $backgroundColor
     */
    public function rotate($angle, $backgroundColor = null)
    {
        $backgroundColor = $backgroundColor 
            ? Rgb::normalize($backgroundColor)
            : new Rgb(0, 0, 0, 127);

        // create color
        $backgroundColorId = imageColorAllocateAlpha(
            $this->_resource, 
            $backgroundColor->getRed(), 
            $backgroundColor->getGreen(), 
            $backgroundColor->getBlue(), 
            $backgroundColor->getAlpha()
        );

        // rotate image
        $rotatedImageResource = imagerotate($this->_resource, $angle, $backgroundColorId, true);

        imagealphablending($rotatedImageResource, false);
        imagesavealpha($rotatedImageResource, true);

        $this->loadResource($rotatedImageResource);
        
        return $this;
    }

    public function flipVertical()
    {
        // use native function
        if(version_compare(PHP_VERSION, '5.5', '>=')) {
            $resource = imageflip($this->_resource, IMG_FLIP_VERTICAL);
        } else {
            $resource = $this->_flipVertical();
        }
        
        $this->loadResource($resource);

        return $this;
    }
    
    private function _flipVertical()
    {
        $flippedImageResource = imagecreatetruecolor($this->_width, $this->_height);
        
        for($x = 0; $x < $this->_width; $x++) {
            for($y = 0; $y < $this->_height; $y++) {
                $color = imagecolorat($this->_resource, $x, $y);
                imagesetpixel(
                    $flippedImageResource, 
                    $x, 
                    $this->_height - 1 - $y, 
                    $color
                );
            }
        }
        
        return $flippedImageResource;
    }

    public function flipHorizontal()
    {
        // use native function
        if(version_compare(PHP_VERSION, '5.5', '>=')) {
            $resource = imageflip($this->_resource, IMG_FLIP_HORIZONTAL);
        } else {
            $resource = $this->_flipHorizontal();
        }
        
        $this->loadResource($resource);

        return $this;
    }
    
    private function _flipHorizontal()
    {
        $flippedImageResource = imagecreatetruecolor($this->_width, $this->_height);
        
        for($x = 0; $x < $this->_width; $x++) {
            for($y = 0; $y < $this->_height; $y++) {
                $color = imagecolorat($this->_resource, $x, $y);
                imagesetpixel(
                    $flippedImageResource, 
                    $this->_width - 1 - $x, 
                    $y, 
                    $color
                );
            }
        }
        
        return $flippedImageResource;
    }

    public function flipBoth()
    {
        // use native function
        if(version_compare(PHP_VERSION, '5.5', '>=')) {
            $resource = imageflip($this->_resource, IMG_FLIP_BOTH);
        } else {
            $resource = $this->_flipBoth();
        }
        
        $this->loadResource($resource);

        return $this;
    }
    
    private function _flipBoth()
    {
        $flippedImageResource = imagecreatetruecolor($this->_width, $this->_height);
        
        for($x = 0; $x < $this->_width; $x++) {
            for($y = 0; $y < $this->_height; $y++) {
                $color = imagecolorat($this->_resource, $x, $y);
                imagesetpixel(
                    $flippedImageResource, 
                    $this->_width - 1 - $x, 
                    $this->_height - 1 - $y, 
                    $color
                );
            }
        }
        
        return $flippedImageResource;
    }
    
    public function filter(\Sokil\Image\AbstractFilterStrategy $filterStrategy)
    {
        $this->loadResource($filterStrategy->filter($this->_resource));
        
        return $this;
    }
    
    public function appendElementAtPosition(AbstractElement $element, $x, $y)
    {
        $element->draw($this->_resource, $x, $y);
        
        return $this;
    }
}
