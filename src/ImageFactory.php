<?php

namespace Sokil;

class ImageFactory
{
    private $_writeStrategyNamespaces = array();
    
    private $_resizeStrategyNamespaces = array();
    
    private $_filterStrategyNamespaces = array();
    
    private $_elementNamespaces = array(
        '\Sokil\Image\Element',
    );
    
    public function __construct(array $options = array())
    {
        if(isset($options['namespace'])) {
            
            if(isset($options['namespace']['write'])) {
                $this->addWriteStrategyNamespaces($options['namespace']['write']);
            }
            
            if(isset($options['namespace']['resize'])) {
                $this->addResizeStrategyNamespaces($options['namespace']['resize']);
            }
            
            if(isset($options['namespace']['filter'])) {
                $this->addFilterStrategyNamespaces($options['namespace']['write']);
            }
            
            if(isset($options['namespace']['element'])) {
                $this->addElementNamespaces($options['namespace']['element']);
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

    public function addElementNamespace($namespace)
    {
        $this->_elementNamespaces[] = rtrim($namespace, '\\');
    }
    
    public function addElementNamespaces(array $namespaces)
    {
        array_map(array($this, 'addElementNamespace'), $namespaces);
        return $this;
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
        foreach($this->_elementNamespaces as $namespace) {
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