<?php

namespace Sokil;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File /some-unexisted-file.jpg not found
     */
    public function testLoadFile_UnexistedFile()
    {
        $image = new Image;
        $image->loadFile('/some-unexisted-file.jpg');
    }
    
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File /some-unexisted-file.jpg not found
     */
    public function testConstrcut_UnexistedFile()
    {
        $image = new Image('/some-unexisted-file.jpg');
    }
    
    public function testWrite_Jpeg()
    {
        $sourceFilename = __DIR__ . '/test.jpg';
        $targetFilename = sys_get_temp_dir() . '/sokil-php-image.jpg';
        
        $image = new Image($sourceFilename);
        $image
            ->write('jpeg', function(\Sokil\Image\WriteStrategy\JpegWriteStrategy $writeStrategy) use($targetFilename) {                
                $writeStrategy
                    ->setQuality(100)
                    ->toFile($targetFilename);
            });
            
        // check file existance
        $this->assertFileExists($targetFilename);
        
        // check image
        $this->assertEquals(
            getimagesize($sourceFilename), 
            getimagesize($targetFilename)
        );
    }
    
    public function testWrite_Gif()
    {
        $sourceFilename = __DIR__ . '/test.gif';
        $targetFilename = sys_get_temp_dir() . '/sokil-php-image.gif';
        
        $image = new Image($sourceFilename);
        $image
            ->write('gif', function(\Sokil\Image\WriteStrategy\GifWriteStrategy $writeStrategy) use($targetFilename) {                
                $writeStrategy->toFile($targetFilename);
            });
            
        // check file existance
        $this->assertFileExists($targetFilename);
        
        // check image
        $this->assertEquals(
            getimagesize($sourceFilename), 
            getimagesize($targetFilename)
        );
    }
    
    public function testWrite_Png()
    {
        $sourceFilename = __DIR__ . '/test.png';
        $targetFilename = sys_get_temp_dir() . '/sokil-php-image.png';
        
        $image = new Image($sourceFilename);
        $image
            ->write('png', function(\Sokil\Image\WriteStrategy\PngWriteStrategy $writeStrategy) use($targetFilename) {                
                $writeStrategy
                    ->setQuality(9)
                    ->toFile($targetFilename);
            });
            
        // check file existance
        $this->assertFileExists($targetFilename);
        
        // check image
        $this->assertEquals(
            getimagesize($sourceFilename), 
            getimagesize($targetFilename)
        );
    }
    
    public function testResize()
    {
        $image = new Image(__DIR__ . '/test.png');
        $resizedImage = $image->resize('scale', 100, 200);
        
        $this->assertEquals(100, $resizedImage->getWidth());
        $this->assertEquals(66, $resizedImage->getHeight());
    }
    
    public function testGetRgbFromHex()
    {
        $image = new Image();
        
        $imageReflection = new \ReflectionClass($image);
        $method = $imageReflection->getMethod('getRgbFromHex');
        $method->setAccessible(true);
        
        $this->assertEquals([239, 239, 239, 0], $method->invoke($image, '#efefef'));
        
        $this->assertEquals([239, 239, 239, 0], $method->invoke($image, 'efefef'));
        
        $this->assertEquals([239, 239, 239, 64], $method->invoke($image, '#80efefef'));
        
        $this->assertEquals([239, 239, 239, 64], $method->invoke($image, '80efefef'));
    }
    
    public function testRotate()
    {
        $image = new Image(__DIR__ . '/test.png');
        $resizedImage = $image->rotate(90, '#FF0000');
        
        $this->assertEquals(200, $resizedImage->getWidth());
        $this->assertEquals(300, $resizedImage->getHeight());
    }
    
    public function testFlipVertical()
    {
        $image = new Image(__DIR__ . '/test.png');
        
        $reflection = new \ReflectionClass($image);
        $method = $reflection->getMethod('_flipVertical');
        $method->setAccessible(true);
        
        $flippedImage = $method->invoke($image);
        
        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 50),
            imagecolorat($flippedImage->getResource(), 50, 150)
        );
    }
    
    public function testFlipHorizontal()
    {
        $image = new Image(__DIR__ . '/test.png');
        
        $reflection = new \ReflectionClass($image);
        $method = $reflection->getMethod('_flipHorizontal');
        $method->setAccessible(true);
        
        $flippedImage = $method->invoke($image);
        
        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 100),
            imagecolorat($flippedImage->getResource(), 250, 100)
        );
    }
    
    public function testFlipBoth()
    {
        $image = new Image(__DIR__ . '/test.png');
        
        $reflection = new \ReflectionClass($image);
        $method = $reflection->getMethod('_flipBoth');
        $method->setAccessible(true);
        
        $flippedImage = $method->invoke($image);
        
        // vertical
        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 50),
            imagecolorat($flippedImage->getResource(), 50, 150)
        );
        
        // horizontal
        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 100),
            imagecolorat($flippedImage->getResource(), 250, 100)
        );
    }
    
    public function testGreyscale()
    {
        $image = new Image(__DIR__ . '/test.png');
        $greyscaleImage = $image->greyscale();
        
        $color = imagecolorat($greyscaleImage->getResource(), 0, 0);
        $this->assertEquals([29, 29, 29], Image::getRgbFromInt($color));
        
        $color = imagecolorat($greyscaleImage->getResource(), 0, 199);
        $this->assertEquals([225, 225, 225], Image::getRgbFromInt($color));
    }
}