<?php

namespace Sokil\Image\ColorModel;

class RgbTest extends \PHPUnit_Framework_TestCase
{
    public function testFromHex()
    {       
        $this->assertEquals([239, 239, 239, 0], Rgb::fromHex('#efefef')->toArray());
        
        $this->assertEquals([239, 239, 239, 0], Rgb::fromHex('efefef')->toArray());
        
        $this->assertEquals([239, 239, 239, 64], Rgb::fromHex('#80efefef')->toArray());
        
        $this->assertEquals([239, 239, 239, 64], Rgb::fromHex('80efefef')->toArray());
    }
}