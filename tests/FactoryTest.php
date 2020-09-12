<?php

namespace Sokil\Image;

use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     *
     * @var \Sokil\Image\Factory
     */
    protected $factory;
    
    public function setUp(): void
    {
        $this->factory = new Factory();
    }
    
    public function testAddWriteStrategyNamespace()
    {
        $this->factory->addWriteStrategyNamespace('\Vendor\WriteStrategy\\');
        
        $reflectionClass = new \ReflectionClass($this->factory);
        $property = $reflectionClass->getProperty('writeStrategyNamespaces');
        $property->setAccessible('true');
        
        $this->assertEquals(
            array(
                '\Sokil\Image\WriteStrategy',
                '\Vendor\WriteStrategy',
            ),
            $property->getValue($this->factory)
        );
    }
    
    public function testAddResizeStrategyNamespace()
    {
        $this->factory->addResizeStrategyNamespace('\Vendor\ResizeStrategy\\');
        
        $reflectionClass = new \ReflectionClass($this->factory);
        $property = $reflectionClass->getProperty('resizeStrategyNamespaces');
        $property->setAccessible('true');
        
        $this->assertEquals(
            array(
                '\Sokil\Image\ResizeStrategy',
                '\Vendor\ResizeStrategy',
            ),
            $property->getValue($this->factory)
        );
    }
    
    public function testAddFilterStrategyNamespace()
    {
        $this->factory->addFilterStrategyNamespace('\Vendor\FilterStrategy\\');
        
        $reflectionClass = new \ReflectionClass($this->factory);
        $property = $reflectionClass->getProperty('filterStrategyNamespaces');
        $property->setAccessible('true');
        
        $this->assertEquals(
            array(
                '\Sokil\Image\FilterStrategy',
                '\Vendor\FilterStrategy',
            ),
            $property->getValue($this->factory)
        );
    }
    
    public function testAddElementNamespace()
    {
        $this->factory->addElementNamespace('\Vendor\Element\\');
        
        $reflectionClass = new \ReflectionClass($this->factory);
        $property = $reflectionClass->getProperty('elementNamespaces');
        $property->setAccessible('true');
        
        $this->assertEquals(
            array(
                '\Sokil\Image\Element',
                '\Vendor\Element',
            ),
            $property->getValue($this->factory)
        );
    }
}