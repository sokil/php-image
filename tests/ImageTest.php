<?php

namespace Sokil;

use \Sokil\Image\ColorModel\Rgb;
use Sokil\Image\Factory;
use Sokil\Image\WriteStrategy\GifWriteStrategy;
use Sokil\Image\WriteStrategy\JpegWriteStrategy;
use Sokil\Image\WriteStrategy\PngWriteStrategy;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Factory
     */
    protected $factory;
    
    public function setUp()
    {
        $this->factory = new Factory();
    }
    
    /**
     * @expectedException \Sokil\Image\Exception\ImageException
     * @expectedExceptionMessage File /some-unexisted-file.jpg not found
     */
    public function testLoadFile_UnexistedFile()
    {
        $this->factory->openImage('/some-unexisted-file.jpg');
    }
    
    public function testWrite_Jpeg()
    {
        $sourceFilename = __DIR__ . '/test.jpg';
        $targetFilename = sys_get_temp_dir() . '/sokil-php-image.jpg';
        
        $image = $this->factory->openImage($sourceFilename);
        $this->factory->writeImage(
            $image, 
            'jpeg', 
            function(JpegWriteStrategy $writeStrategy) use($targetFilename) {
                $writeStrategy->setQuality(100)->toFile($targetFilename);
            }
        );
            
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
        
        $image = $this->factory->openImage($sourceFilename);
        $this->factory->writeImage(
            $image,
            'gif', 
            function(GifWriteStrategy $writeStrategy) use($targetFilename) {
                $writeStrategy->toFile($targetFilename);
            }
        );
            
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
        
        $image = $this->factory->openImage($sourceFilename);
        $this->factory->writeImage(
            $image,
            'png', 
            function(PngWriteStrategy $writeStrategy) use($targetFilename) {
                $writeStrategy->setQuality(9)->toFile($targetFilename);
            }
        );
            
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
        $image = $this->factory->openImage(__DIR__ . '/test.png');
        $this->factory->resizeImage($image, 'scale', 100, 200);
        
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(66, $image->getHeight());
    }
    
    public function testRotate()
    {
        $image = $this->factory->openImage(__DIR__ . '/test.png');
        $resizedImage = $image->rotate(90, '#FF0000');
        
        $this->assertEquals(200, $resizedImage->getWidth());
        $this->assertEquals(300, $resizedImage->getHeight());
    }
    
    public function testFlipVertical()
    {
        $image = $this->factory->openImage(__DIR__ . '/test.png');
        
        $reflection = new \ReflectionClass($image);
        $method = $reflection->getMethod('flipVertical');
        $method->setAccessible(true);
        
        $flippedImage = $method->invoke($image);

        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 50),
            imagecolorat($flippedImage->getResource(), 50, 150)
        );
    }
    
    public function testFlipHorizontal()
    {
        $image = $this->factory->openImage(__DIR__ . '/test.png');
        
        $reflection = new \ReflectionClass($image);
        $method = $reflection->getMethod('flipHorizontal');
        $method->setAccessible(true);
        
        $flippedImage = $method->invoke($image);
        
        $this->assertEquals(
            imagecolorat($image->getResource(), 50, 100),
            imagecolorat($flippedImage->getResource(), 250, 100)
        );
    }
    
    public function testFlipBoth()
    {
        $image = $this->factory->openImage(__DIR__ . '/test.png');
        
        $reflection = new \ReflectionClass($image);
        $method = $reflection->getMethod('flipBoth');
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
        $image = $this->factory->openImage(__DIR__ . '/test.png');
        $this->factory->filterImage($image, 'greyscale');
        
        $color = imagecolorat($image->getResource(), 0, 0);
        $this->assertEquals(array(29, 29, 29), Rgb::fromIntAsArray($color));
        
        $color = imagecolorat($image->getResource(), 0, 199);
        $this->assertEquals(array(225, 225, 225), Rgb::fromIntAsArray($color));
    }
    
    public function testAppendElement_TextElement()
    {        
        // text element
        $element = $this->factory
            ->createTextElement()
            ->setText('hello world')
            ->setAngle(20)
            ->setSize(40)
            ->setFont(__DIR__ . '/FreeSerif.ttf');
        
        // place text to image
        $image = $this->factory
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
    
    public function testCrop()
    {
        $image = $this->factory->openImage(__DIR__ . '/test.png');
        $image->crop(10, 10, 10, 10);
        
        $this->assertEquals(10, imagesx($image->getResource()));
        $this->assertEquals(10, imagesy($image->getResource()));
        $this->assertEquals(
            array(0, 0, 255, 0),
            Rgb::fromInt(imagecolorat($image->getResource(), 5, 5))->toArray()
        );
    }
}