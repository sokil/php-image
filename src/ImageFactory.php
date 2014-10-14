<?php

namespace Sokil;

class ImageFactory
{
    private $_writeStrategyNamespaces = array(
        '\Sokil\Image\WriteStrategy',
    );
    
    private $_resizeStrategyNamespaces = array(
        '\Sokil\Image\ResizeStrategy',
    );
    
    private $_filterStrategyNamespaces = array(
        '\Sokil\Image\FilterStrategy',
    );
    
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
    
    private function getResizeStrategyClassNameByResizeMode($resizeMode)
    {
        // save strategy
        foreach ($this->_resizeStrategyNamespaces as $namespace) {
            $resizeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($resizeMode)) . 'ResizeStrategy';
            if (class_exists($resizeStrategyClassName)) {
                return $resizeStrategyClassName;
            }
        }

        throw new \Exception('Resize mode ' . $resizeMode . ' not supported');
    }
    
    public function resizeImage(Image $image, $mode, $width, $height)
    {
        $resizeStrategyClassName = $this->getResizeStrategyClassNameByResizeMode($mode);

        /* @var $resizeStrategy \Sokil\Image\AbstractResizeStrategy */
        $resizeStrategy = new $resizeStrategyClassName();
        if (!($resizeStrategy instanceof \Sokil\Image\AbstractResizeStrategy)) {
            throw new \Exception('Resize strategy must extend AbstractResizeStrategy');
        }

        $image->resize($resizeStrategy, $width, $height);
        
        return $this;
    }
    
    private function getFilterStrategyClassnameByFilterName($name)
    {
        // save strategy
        foreach ($this->_filterStrategyNamespaces as $namespace) {
            $filterStrategyClassName = $namespace . '\\' . ucfirst(strtolower($name)) . 'FilterStrategy';
            if (class_exists($filterStrategyClassName)) {
                return $filterStrategyClassName;
            }
        }

        throw new \Exception('Filter ' . $name . ' not supported');
    }
    
    public function filterImage(Image $image, $name, $configuratorCallable = null)
    {
        $filterStrategyClassName = $this->getFilterStrategyClassnameByFilterName($name);

        $filterStrategy = new $filterStrategyClassName;
        if (!($filterStrategy instanceof \Sokil\Image\AbstractFilterStrategy)) {
            throw new \Exception('Filter strategy must extend AbstractFilterStrategy');
        }

        // configure strategy
        if($configuratorCallable) {
            call_user_func($configuratorCallable, $filterStrategy);
        }
        
        $image->filter($filterStrategy);
        
        return $this;
    }
    
    private function getWriteStrategyClassNameByWriteFormat($format)
    {
        // save strategy
        foreach ($this->_writeStrategyNamespaces as $namespace) {
            $writeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($format)) . 'WriteStrategy';
            if (class_exists($writeStrategyClassName)) {
                return $writeStrategyClassName;
            }
        }
        
        throw new \Exception('Format ' . $format . ' not supported');
    }
    /**
     * @param string $format
     * @return \Sokil\Image\AbstractWriteStrategy
     */
    public function writeImage(Image $image, $format, $configuratorCallable = null)
    {
        $writeStrategyClassName = $this->getWriteStrategyClassNameByWriteFormat($format);

        $writeStrategy = new $writeStrategyClassName;
        if (!($writeStrategy instanceof \Sokil\Image\AbstractWriteStrategy)) {
            throw new \Exception('Write strategy must extend AbstractWriteStrategy');
        }
        
        // configure 
        if($configuratorCallable) {
            if(!is_callable($configuratorCallable)) {
                throw new \Exception('Wrong configurator specified');
            }

            call_user_func($configuratorCallable, $writeStrategy);
        }
        
        $image->write($writeStrategy);
        
        return $this;
    }
    
    private function getElementClassNameByElementName($name)
    {
        foreach($this->_elementNamespaces as $namespace) {
            $elementClassName = $namespace . '\\' . ucfirst(strtolower($name));
            if(class_exists($elementClassName)) {
                return $elementClassName;
            }
        }
        
        throw new \InvalidArgumentException('Element "' . $elementClassName . '" not exists');
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
        $elementClassName = $this->getElementClassNameByElementName($name);
        
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