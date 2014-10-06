<?php

namespace Sokil;

class Image
{    
    private $_resource;
    
    private $_width;
    
    private $_height;
    
    private static $_writeStrategyNamespaces = [
        '\Sokil\Image\WriteStrategy',
    ];
    
    private static $_resizeStrategyNamespaces = [
        '\Sokil\Image\ResizeStrategy',
    ];

    public function __construct($image = null)
    {
        // load image
        if($image) {
            if(is_string($image)) {
                $this->loadFile($image);
            } elseif(is_resource($image)) {
                $this->loadResource($image);
            } else {
                throw new \Exception('Must be image resource or filename, ' . gettype($image) . ' given');
            }
        }
    }
    
    public static function addWriteStrategyNamespace($namespace)
    {
        self::$_writeStrategyNamespaces[] = rtrim($namespace, '\\');
    }
    
    public static function addResizeStrategyNamespace($namespace) 
    {
        self::$_resizeStrategyNamespaces[] = rtrim($namespace, '\\');
    }
    
    public function loadFile($filename)
    {
        if(!file_exists($filename)) {
            throw new \Exception('File '  . $filename . ' not found');
        }
        
        if(!is_readable($filename)) {
            throw new \Exception('File '  . $filename . ' not readable');
        }
        
        $imageInfo = @getimagesize($filename);
        if(!$imageInfo) {
            throw new \Exception('Wrong image format');
        }

        if(!in_array($imageInfo[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))) {
            throw new \Exception('Only image of JPEG, PNG and GIF formats supported');
        }
        
        $this->_width = $imageInfo[0];
        
        $this->_height = $imageInfo[1];
        
        switch($imageInfo[2]) {
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
        if(!is_resource($resource)) {
            throw new \Exception('Must be resource, ' . gettype($resource) . ' given');
        }
        
        $this->_resource = $resource;
        
        $this->_width = imagesx($resource);
        
        $this->_height = imagesy($resource);
        
        return $this;
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
        foreach(self::$_resizeStrategyNamespaces as $namespace) {
            $resizeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($mode)) . 'ResizeStrategy';
            if(class_exists($resizeStrategyClassName)) {
                continue;
            }
        }
        
        if(!$resizeStrategyClassName) {
            throw new \Exception('Resize mode ' . $mode . ' not supported');
        }
        
        /* @var $resizeStrategy \Sokil\Image\AbstractResizeStrategy */
        $resizeStrategy = new $resizeStrategyClassName();
        
        return new self($resizeStrategy->resize($this->_resource, $width, $height));
    }
    
    /**
     * @param string $format
     * @return \Sokil\Image\AbstractWriteStrategy
     */
    public function write($format, $configuratorCallable)
    {        
        // save strategy
        foreach(self::$_writeStrategyNamespaces as $namespace) {
            $writeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($format)) . 'WriteStrategy';
            if(!class_exists($writeStrategyClassName)) {
                continue;
            }
        }
        
        if(!$writeStrategyClassName) {
            throw new \Exception('Format ' . $format . ' not supported');
        }
        
        return call_user_func(
            $configuratorCallable, 
            new $writeStrategyClassName($this->_resource)
        );
    }
}
