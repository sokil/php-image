<?php

namespace Sokil;

class ImageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var \Sokil\ImageFactory
     */
    protected $_factory;
    
    public function setUp()
    {
        $this->_factory = new \Sokil\ImageFactory;
    }
    
    public function testAddWriteStrategyNamespace()
    {
        $this->_factory->addWriteStrategyNamespace('\Vendor\WriteStrategy\\');
        
        $reflactionClass = new \ReflectionClass($this->_factory);
        $propery = $reflactionClass->getProperty('_writeStrategyNamespaces');
        $propery->setAccessible('true');
        
        $this->assertEquals(array(
            '\Sokil\Image\WriteStrategy',
            '\Vendor\WriteStrategy',
        ), $propery->getValue($this->_factory));
    }
    
    public function testAddResizeStrategyNamespace()
    {
        $this->_factory->addResizeStrategyNamespace('\Vendor\ResizeStrategy\\');
        
        $reflactionClass = new \ReflectionClass($this->_factory);
        $propery = $reflactionClass->getProperty('_resizeStrategyNamespaces');
        $propery->setAccessible('true');
        
        $this->assertEquals(array(
            '\Sokil\Image\ResizeStrategy',
            '\Vendor\ResizeStrategy',
        ), $propery->getValue($this->_factory));
    }
    
    public function testAddFilterStrategyNamespace()
    {
        $this->_factory->addFilterStrategyNamespace('\Vendor\FilterStrategy\\');
        
        $reflactionClass = new \ReflectionClass($this->_factory);
        $propery = $reflactionClass->getProperty('_filterStrategyNamespaces');
        $propery->setAccessible('true');
        
        $this->assertEquals(array(
            '\Sokil\Image\FilterStrategy',
            '\Vendor\FilterStrategy',
        ), $propery->getValue($this->_factory));
    }
    
    public function testAddElementNamespace()
    {
        $this->_factory->addElementNamespace('\Vendor\Element\\');
        
        $reflactionClass = new \ReflectionClass($this->_factory);
        $propery = $reflactionClass->getProperty('_elementNamespaces');
        $propery->setAccessible('true');
        
        $this->assertEquals(array(
            '\Sokil\Image\Element',
            '\Vendor\Element',
        ), $propery->getValue($this->_factory));
    }
}