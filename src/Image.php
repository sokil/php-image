<?php

namespace Sokil;

use Sokil\Image\ColorModel\Rgb;
use Sokil\Image\AbstractElement;

class Image
{
    private $_resource;
    
    private $_width;
    
    private $_height;
    
    private $_writeStrategyNamespaces = array(
        '\Sokil\Image\WriteStrategy',
    );
    
    private $_resizeStrategyNamespaces = array(
        '\Sokil\Image\ResizeStrategy',
    );
    
    private $_filterStrategyNamespaces = array(
        '\Sokil\Image\FilterStrategy',
    );

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
    
    public function addWriteStrategyNamespace($namespace)
    {
        $this->_writeStrategyNamespaces[] = rtrim($namespace, '\\');
        return $this;
    }
    
    public function addWriteStrategyNamespaces(array $namespaces)
    {
        array_map(array($this, 'addWriteStrategyNamespace'), $namespaces);
        return $this;
    }

    public function addResizeStrategyNamespace($namespace)
    {
        $this->_resizeStrategyNamespaces[] = rtrim($namespace, '\\');
    }
    
    public function addResizeStrategyNamespaces(array $namespaces)
    {
        array_map(array($this, 'addResizeStrategyNamespace'), $namespaces);
        return $this;
    }
    
    public function addFilterStrategyNamespace($namespace)
    {
        $this->_filterStrategyNamespaces[] = rtrim($namespace, '\\');
    }
    
    public function addFilterStrategyNamespaces(array $namespaces)
    {
        array_map(array($this, 'addFilterStrategyNamespace'), $namespaces);
        return $this;
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
    
    public function create($width, $height)
    {
        return $this->loadResource(imagecreatetruecolor($width, $height));
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

    public function resize($mode, $width, $height)
    {
        // save strategy
        foreach ($this->_resizeStrategyNamespaces as $namespace) {
            $resizeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($mode)) . 'ResizeStrategy';
            if (!class_exists($resizeStrategyClassName)) {
                continue;
            }
        }

        if (!isset($resizeStrategyClassName)) {
            throw new \Exception('Resize mode ' . $mode . ' not supported');
        }

        /* @var $resizeStrategy \Sokil\Image\AbstractResizeStrategy */
        $resizeStrategy = new $resizeStrategyClassName();
        if (!($resizeStrategy instanceof \Sokil\Image\AbstractResizeStrategy)) {
            throw new \Exception('Resize strategy must extend AbstractResizeStrategy');
        }

        return new self($resizeStrategy->resize($this->_resource, $width, $height));
    }

    /**
     * @param string $format
     * @return \Sokil\Image\AbstractWriteStrategy
     */
    public function write($format, $configuratorCallable)
    {
        // save strategy
        foreach ($this->_writeStrategyNamespaces as $namespace) {
            $writeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($format)) . 'WriteStrategy';
            if (!class_exists($writeStrategyClassName)) {
                continue;
            }
        }

        if (!isset($writeStrategyClassName)) {
            throw new \Exception('Format ' . $format . ' not supported');
        }

        $writeStrategy = new $writeStrategyClassName($this->_resource);
        if (!($writeStrategy instanceof \Sokil\Image\AbstractWriteStrategy)) {
            throw new \Exception('Write strategy must extend AbstractWriteStrategy');
        }

        return call_user_func(
            $configuratorCallable, $writeStrategy
        );
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

        return new self($rotatedImageResource);
    }

    public function flipVertical()
    {
        // use native function
        if(version_compare(PHP_VERSION, '5.5', '>=')) {
            return new self(imageflip($this->_resource, IMG_FLIP_VERTICAL));
        }

        return $this->_flipVertical();
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
        
        return new self($flippedImageResource);
    }

    public function flipHorizontal()
    {
        // use native function
        if(version_compare(PHP_VERSION, '5.5', '>=')) {
            return new self(imageflip($this->_resource, IMG_FLIP_HORIZONTAL));
        }

        return $this->_flipHorizontal();
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
        
        return new self($flippedImageResource);
    }

    public function flipBoth()
    {
        // use native function
        if(version_compare(PHP_VERSION, '5.5', '>=')) {
            return new self(imageflip($this->_resource, IMG_FLIP_BOTH));
        }

        return $this->_flipBoth();
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
        
        return new self($flippedImageResource);
    }
    
    public function filter($name, $configuratorCallable = null)
    {
        // save strategy
        foreach ($this->_filterStrategyNamespaces as $namespace) {
            $filterStrategyClassName = $namespace . '\\' . ucfirst(strtolower($name)) . 'FilterStrategy';
            if (!class_exists($filterStrategyClassName)) {
                continue;
            }
        }

        if (!isset($filterStrategyClassName)) {
            throw new \Exception('Filter ' . $name . ' not supported');
        }

        $filterStrategy = new $filterStrategyClassName($this->_resource);
        if (!($filterStrategy instanceof \Sokil\Image\AbstractFilterStrategy)) {
            throw new \Exception('Filter strategy must extend AbstractFilterStrategy');
        }

        // configure strategy
        if($configuratorCallable) {
            call_user_func($configuratorCallable, $filterStrategy);
        }
        
        return new self($filterStrategy->filter());
    }
    
    public function appendElementAtPosition(AbstractElement $element, $x, $y)
    {
        $element->draw($this->_resource, $x, $y);
        
        return $this;
    }
}
