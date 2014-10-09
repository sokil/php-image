<?php

namespace Sokil;

class ImageFactory
{
    private static $_writeStrategyNamespaces = array(
        '\Sokil\Image\WriteStrategy',
    );
    
    private static $_resizeStrategyNamespaces = array(
        '\Sokil\Image\ResizeStrategy',
    );
    
    private static $_filterStrategyNamespaces = array(
        '\Sokil\Image\FilterStrategy',
    );
    
    private static $_elementNamespaces = array(
        '\Sokil\Image\Element',
    );
    
    public static function addWriteStrategyNamespace($namespace)
    {
        self::$_writeStrategyNamespaces[] = rtrim($namespace, '\\');
    }
    
    public static function getWriteStrategyNamespaces()
    {
        return self::$_writeStrategyNamespaces;
    }

    public static function addResizeStrategyNamespace($namespace)
    {
        self::$_resizeStrategyNamespaces[] = rtrim($namespace, '\\');
    }
    
    public static function getResizeStrategyNamespaces()
    {
        return self::$_resizeStrategyNamespaces;
    }
    
    public static function addFilterStrategyNamespace($namespace)
    {
        self::$_filterStrategyNamespaces[] = rtrim($namespace, '\\');
    }
    
    public static function getFilterStrategyNamespaces()
    {
        return self::$_filterStrategyNamespaces;
    }

    public static function addElementNamespace($namespace)
    {
        self::$_elementNamespaces[] = rtrim($namespace, '\\');
    }
    
    public static function getElementNamespaces()
    {
        return self::$_elementNamespaces;
    }
    
    /**
     * Create empty image
     * @param int $width
     * @param int $height
     * @return \Sokil\Image
     */
    public function createImage($width, $height)
    {
        $image = new \Sokil\Image;
        return $image->create($width, $height);
    }
    
    /**
     * Open existed image
     * @param string|resource $image path to file on disk or image resource
     * @return \Sokil\Image
     */
    public function openImage($image)
    {
        return new Image($image);
    }
    
    /**
     * Create element
     * 
     * @param string $name name of element
     * @return \Sokil\Image\AbstractElement
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function createElement($name)
    {
        foreach(self::$_elementNamespaces as $namespace) {
            $elementClassName = $namespace . '\\' . ucfirst(strtolower($name));
            if(!class_exists($elementClassName)) {
                continue;
            }
        }
        
        if(!isset($elementClassName)) {
            throw new \InvalidArgumentException('Element "' . $elementClassName . '" not exists');
        }
        
        $element = new $elementClassName;

        if(!($element instanceof \Sokil\Image\AbstractElement)) {
            throw new \Exception('Element must implement AbstractElement class');
        }

        return $element;
    }
    
    /**
     * 
     * @return \Sokil\Image\Element\Text
     */
    public function createTextElement()
    {
        return $this->createElement('text');
    }
}