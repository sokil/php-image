<?php

namespace Sokil\Image\ColorModel;

use PHPUnit\Framework\TestCase;

class RgbTest extends TestCase
{
    public function testFromHex()
    {       
        $this->assertEquals(array(239, 239, 239, 0), Rgb::fromHex('#efefef')->toArray());
        
        $this->assertEquals(array(239, 239, 239, 0), Rgb::fromHex('efefef')->toArray());
        
        $this->assertEquals(array(239, 239, 239, 64), Rgb::fromHex('#80efefef')->toArray());
        
        $this->assertEquals(array(239, 239, 239, 64), Rgb::fromHex('80efefef')->toArray());
    }
}