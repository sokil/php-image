<?php

namespace Sokil;

use \Sokil\Image\ColorModel\Rgb;

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
        
        $flippedImageResource = $method->invoke($image);
        
        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 50),
            imagecolorat($flippedImageResource, 50, 150)
        );
    }
    
    public function testFlipHorizontal()
    {
        $image = new Image(__DIR__ . '/test.png');
        
        $reflection = new \ReflectionClass($image);
        $method = $reflection->getMethod('_flipHorizontal');
        $method->setAccessible(true);
        
        $flippedImageResource = $method->invoke($image);
        
        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 100),
            imagecolorat($flippedImageResource, 250, 100)
        );
    }
    
    public function testFlipBoth()
    {
        $image = new Image(__DIR__ . '/test.png');
        
        $reflection = new \ReflectionClass($image);
        $method = $reflection->getMethod('_flipBoth');
        $method->setAccessible(true);
        
        $flippedImageResource = $method->invoke($image);
        
        // vertical
        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 50),
            imagecolorat($flippedImageResource, 50, 150)
        );
        
        // horizontal
        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 100),
            imagecolorat($flippedImageResource, 250, 100)
        );
    }
    
    public function testGreyscale()
    {
        $image = new Image(__DIR__ . '/test.png');
        $greyscaleImage = $image->filter('greyscale');
        
        $color = imagecolorat($greyscaleImage->getResource(), 0, 0);
        $this->assertEquals(array(29, 29, 29), Rgb::fromIntAsArray($color));
        
        $color = imagecolorat($greyscaleImage->getResource(), 0, 199);
        $this->assertEquals(array(225, 225, 225), Rgb::fromIntAsArray($color));
    }
    
    public function testAppendElement_TextElement()
    {
        $factory = new ImageFactory();
        
        // text element
        $element = $factory
            ->createTextElement()
            ->setText('hello world')
            ->setAngle(20)
            ->setSize(40)
            ->setFont(__DIR__ . '/FreeSerif.ttf');
        
        // place text to image
        $image = $factory
            ->createImage(300, 300)
            ->fill(Rgb::createWhite())
            // draw shadow
            ->appendElementAtPosition($element->setColor('#ababab'), 50, 150)
            // draw text
            ->appendElementAtPosition($element->setColor('#ff0000'), 49, 149);
        
        $intColor = imagecolorat($image->getResource(), 47, 126);
        $color = Rgb::fromInt($intColor)->toArray();
        
        $this->assertEquals(array(255, 0, 0, 0), $color);
    }
}