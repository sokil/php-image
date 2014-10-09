<?php

namespace Sokil\Image\Element;

use Sokil\Image\ColorModel\Rgb;

class Text extends \Sokil\Image\AbstractElement
{
    private $_text;
    
    private $_angle = 0;
    
    private $_font;
    
    private $_color;
    
    private $_size = 14;
    
    public function setText($text)
    {
        $this->_text = $text;
        return $this;
    }
    
    public function setAngle($angle)
    {
        $this->_angle = (int) $angle;
        return $this;
    }
    
    public function setFont($font)
    {
        $this->_font = $font;
        return $this;
    }
    
    public function setColor($color)
    {
        $this->_color = Rgb::normalize($color);
        return $this;
    }
    
    public function setSize($size)
    {
        $this->_size = (int) $size;
        return $this;
    }
    
    public function draw($resource, $x, $y)
    {
        if(!$this->_color) {
            $this->_color = Rgb::createBlack();
        }
        
        imagettftext(
            $resource,
            $this->_size,
            $this->_angle,
            $x,
            $y,
            imagecolorallocatealpha(
                $resource, 
                $this->_color->getRed(), 
                $this->_color->getGreen(), 
                $this->_color->getBlue(), 
                $this->_color->getAlpha()
            ),
            $this->_font,
            $this->_text
        );
        
        return $this;
    }
}