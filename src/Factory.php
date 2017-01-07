<?php

namespace Sokil\Image;

use Sokil\Image\AbstractElement;
use Sokil\Image\AbstractFilterStrategy;
use Sokil\Image\AbstractResizeStrategy;
use Sokil\Image\AbstractWriteStrategy;
use Sokil\Image\Exception\ImageException;

class Factory
{
    private $writeStrategyNamespaces = array(
        '\Sokil\Image\WriteStrategy',
    );
    
    private $resizeStrategyNamespaces = array(
        '\Sokil\Image\ResizeStrategy',
    );
    
    private $filterStrategyNamespaces = array(
        '\Sokil\Image\FilterStrategy',
    );
    
    private $elementNamespaces = array(
        '\Sokil\Image\Element',
    );
    
    public function __construct(array $options = array())
    {
        if(isset($options['namespace'])) {
            $this->configureNamespaces($options['namespace']);
        }
    }
    
    /**
     * Create empty image
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     */
    public function createImage($width, $height)
    {
        $image = new Image();
        return $image->create($width, $height);
    }
    
    /**
     * Open existed image
     *
     * @param string|resource $image path to file on disk or image resource
     *
     * @return Image
     */
    public function openImage($image)
    {
        return new Image($image);
    }

    /**
     * Resize image
     *
     * @param Image $image
     * @param string $mode
     * @param int $width
     * @param int $height
     * @return Image
     *
     * @throws ImageException
     */
    public function resizeImage(
        Image $image,
        $mode,
        $width,
        $height
    ) {
        $resizeStrategyClassName = $this->getResizeStrategyClassNameByResizeMode($mode);

        /* @var $resizeStrategy \Sokil\Image\AbstractResizeStrategy */
        $resizeStrategy = new $resizeStrategyClassName();
        if (!($resizeStrategy instanceof AbstractResizeStrategy)) {
            throw new ImageException('Resize strategy must extend AbstractResizeStrategy');
        }

        return $image->resize($resizeStrategy, $width, $height);
    }

    /**
     * Apply filter to image
     *
     * @param Image $image
     * @param string $name
     * @param callable $configuratorCallable
     *
     * @return Image
     *
     * @throws ImageException
     */
    public function filterImage(
        Image $image,
        $name,
        $configuratorCallable = null
    ) {
        $filterStrategyClassName = $this->getFilterStrategyClassNameByFilterName($name);

        $filterStrategy = new $filterStrategyClassName;
        if (!($filterStrategy instanceof AbstractFilterStrategy)) {
            throw new ImageException('Filter strategy must extend AbstractFilterStrategy');
        }

        // configure strategy
        if ($configuratorCallable) {
            call_user_func($configuratorCallable, $filterStrategy);
        }
        
        return $image->filter($filterStrategy);
    }

    /**
     * Write image to file
     *
     * @param Image $image
     * @param string $format
     * @param callable $configuratorCallable
     *
     * @return Image
     *
     * @throws ImageException
     */
    public function writeImage(
        Image $image,
        $format,
        $configuratorCallable = null
    ) {
        $writeStrategyClassName = $this->getWriteStrategyClassNameByWriteFormat($format);

        $writeStrategy = new $writeStrategyClassName;
        if (!($writeStrategy instanceof AbstractWriteStrategy)) {
            throw new ImageException('Write strategy must extend AbstractWriteStrategy');
        }
        
        // configure 
        if ($configuratorCallable) {
            if (!is_callable($configuratorCallable)) {
                throw new ImageException('Wrong configurator specified');
            }

            call_user_func($configuratorCallable, $writeStrategy);
        }
        
        return $image->write($writeStrategy);
    }
    
    /**
     * Create element
     * 
     * @param string $name name of element
     *
     * @return AbstractElement
     *
     * @throws \InvalidArgumentException
     * @throws ImageException
     */
    public function createElement($name)
    {
        $elementClassName = $this->getElementClassNameByElementName($name);
        
        $element = new $elementClassName;

        if (!($element instanceof AbstractElement)) {
            throw new ImageException('Element must implement AbstractElement class');
        }

        return $element;
    }
    
    /**
     * Create text element
     *
     * @return \Sokil\Image\Element\Text
     */
    public function createTextElement()
    {
        return $this->createElement('text');
    }

    public function addWriteStrategyNamespace($namespace)
    {
        $this->writeStrategyNamespaces[] = rtrim($namespace, '\\');
        return $this;
    }

    public function addWriteStrategyNamespaces(array $namespaces)
    {
        array_map(array($this, 'addWriteStrategyNamespace'), $namespaces);
        return $this;
    }

    public function addResizeStrategyNamespace($namespace)
    {
        $this->resizeStrategyNamespaces[] = rtrim($namespace, '\\');
    }

    public function addResizeStrategyNamespaces(array $namespaces)
    {
        array_map(array($this, 'addResizeStrategyNamespace'), $namespaces);
        return $this;
    }

    public function addFilterStrategyNamespace($namespace)
    {
        $this->filterStrategyNamespaces[] = rtrim($namespace, '\\');
    }

    public function addFilterStrategyNamespaces(array $namespaces)
    {
        array_map(array($this, 'addFilterStrategyNamespace'), $namespaces);
        return $this;
    }

    public function addElementNamespace($namespace)
    {
        $this->elementNamespaces[] = rtrim($namespace, '\\');
    }

    public function addElementNamespaces(array $namespaces)
    {
        array_map(array($this, 'addElementNamespace'), $namespaces);
        return $this;
    }

    public function configureNamespaces(array $namespaces)
    {
        if (isset($namespaces['write'])) {
            $this->addWriteStrategyNamespaces($namespaces['write']);
        }

        if (isset($namespaces['resize'])) {
            $this->addResizeStrategyNamespaces($namespaces['resize']);
        }

        if (isset($namespaces['filter'])) {
            $this->addFilterStrategyNamespaces($namespaces['write']);
        }

        if (isset($namespaces['element'])) {
            $this->addElementNamespaces($namespaces['element']);
        }
    }

    private function getResizeStrategyClassNameByResizeMode($resizeMode)
    {
        // save strategy
        foreach ($this->resizeStrategyNamespaces as $namespace) {
            $resizeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($resizeMode)) . 'ResizeStrategy';
            if (class_exists($resizeStrategyClassName)) {
                return $resizeStrategyClassName;
            }
        }

        throw new ImageException('Resize mode ' . $resizeMode . ' not supported');
    }

    private function getFilterStrategyClassNameByFilterName($name)
    {
        // save strategy
        foreach ($this->filterStrategyNamespaces as $namespace) {
            $filterStrategyClassName = $namespace . '\\' . ucfirst(strtolower($name)) . 'FilterStrategy';
            if (class_exists($filterStrategyClassName)) {
                return $filterStrategyClassName;
            }
        }

        throw new ImageException('Filter ' . $name . ' not supported');
    }

    private function getWriteStrategyClassNameByWriteFormat($format)
    {
        // save strategy
        foreach ($this->writeStrategyNamespaces as $namespace) {
            $writeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($format)) . 'WriteStrategy';
            if (class_exists($writeStrategyClassName)) {
                return $writeStrategyClassName;
            }
        }

        throw new ImageException('Format ' . $format . ' not supported');
    }

    private function getElementClassNameByElementName($name)
    {
        foreach($this->elementNamespaces as $namespace) {
            $elementClassName = $namespace . '\\' . ucfirst(strtolower($name));
            if(class_exists($elementClassName)) {
                return $elementClassName;
            }
        }

        throw new ImageException('Element "' . $elementClassName . '" not exists');
    }
}