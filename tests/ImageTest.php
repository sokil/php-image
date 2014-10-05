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
    
    public function testWrite()
    {
        $sourceFilename = __DIR__ . '/test.png';
        $targetFilename = sys_get_temp_dir() . '/sokil-php-image.png';
        
        $image = new Image($sourceFilename);
        $image
            ->write('png', function(\Sokil\Image\WriteStrategy\PngWriteStrategy $writeStrategy) use($targetFilename) {                
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
    
    public function testResize()
    {
        $image = new Image(__DIR__ . '/test.png');
        $resizedImage = $image->resize('scale', 100, 200);
        
        $this->assertEquals(100, $resizedImage->getWidth());
        $this->assertEquals(66, $resizedImage->getHeight());
    }
}