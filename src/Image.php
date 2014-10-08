<?php

namespace Sokil;

class Image
{
    private $_resource;
    
    private $_width;
    
    private $_height;

    private static $_writeStrategyNamespaces = [
        '\Sokil\Image\WriteStrategy',
    ];
    
    private static $_resizeStrategyNamespaces = [
        '\Sokil\Image\ResizeStrategy',
    ];

    public function __construct($image = null)
    {

        // load image
        if ($image) {
            if (is_string($image)) {
                $this->loadFile($image);
            } elseif (is_resource($image)) {
                $this->loadResource($image);
            } else {
                throw new \Exception('Must be image resource or filename, ' . gettype($image) . ' given');
            }
        }
    }

    public static function addWriteStrategyNamespace($namespace)
    {
        self::$_writeStrategyNamespaces[] = rtrim($namespace, '\\');
    }

    public static function addResizeStrategyNamespace($namespace)
    {
        self::$_resizeStrategyNamespaces[] = rtrim($namespace, '\\');
    }

    public function loadFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception('File ' . $filename . ' not found');
        }

        if (!is_readable($filename)) {
            throw new \Exception('File ' . $filename . ' not readable');
        }

        $imageInfo = @getimagesize($filename);
        if (!$imageInfo) {
            throw new \Exception('Wrong image format');
        }

        if (!in_array($imageInfo[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))) {
            throw new \Exception('Only image of JPEG, PNG and GIF formats supported');
        }

        $this->_width = $imageInfo[0];

        $this->_height = $imageInfo[1];

        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $this->_resource = @imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_PNG:
                $this->_resource = @imagecreatefrompng($filename);
                break;
            case IMAGETYPE_GIF:
                $this->_resource = @imagecreatefromgif($filename);
                break;
        }

        return $this;
    }

    public function loadResource($resource)
    {
        if (!(is_resource($resource) && 'gd' === get_resource_type($resource))) {
            throw new \Exception('Must be resource of type "gd", ' . gettype($resource) . ' given');
        }

        $this->_resource = $resource;

        $this->_width = imagesx($resource);

        $this->_height = imagesy($resource);

        return $this;
    }

    public function getResource()
    {
        return $this->_resource;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    public function resize($mode, $width, $height)
    {
        // save strategy
        foreach (self::$_resizeStrategyNamespaces as $namespace) {
            $resizeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($mode)) . 'ResizeStrategy';
            if (!class_exists($resizeStrategyClassName)) {
                continue;
            }
        }

        if (!$resizeStrategyClassName) {
            throw new \Exception('Resize mode ' . $mode . ' not supported');
        }

        /* @var $resizeStrategy \Sokil\Image\AbstractResizeStrategy */
        $resizeStrategy = new $resizeStrategyClassName();
        if (!($resizeStrategy instanceof \Sokil\Image\AbstractResizeStrategy)) {
            throw new \Exception('Resize strategy must extend AbstractResizeStrategy');
        }

        return new self($resizeStrategy->resize($this->_resource, $width, $height));
    }

    /**
     * @param string $format
     * @return \Sokil\Image\AbstractWriteStrategy
     */
    public function write($format, $configuratorCallable)
    {
        // save strategy
        foreach (self::$_writeStrategyNamespaces as $namespace) {
            $writeStrategyClassName = $namespace . '\\' . ucfirst(strtolower($format)) . 'WriteStrategy';
            if (!class_exists($writeStrategyClassName)) {
                continue;
            }
        }

        if (!$writeStrategyClassName) {
            throw new \Exception('Format ' . $format . ' not supported');
        }

        $writeStrategy = new $writeStrategyClassName($this->_resource);
        if (!($writeStrategy instanceof \Sokil\Image\AbstractWriteStrategy)) {
            throw new \Exception('Write strategy must extend AbstractWriteStrategy');
        }

        return call_user_func(
                $configuratorCallable, $writeStrategy
        );
    }

    /**
     * Get [R, G, B, Alpha] from '#ARGB'
     * @param string $hexColor
     */
    public static function getRgbFromHex($hexColor)
    {
        $hexArray = str_split(ltrim($hexColor, '#'), 2);
        $decimalArray = array_map('hexdec', $hexArray);

        $chunksNum = count($decimalArray);
        if ($chunksNum < 3 || $chunksNum > 4) {
            throw new \InvalidArgumentException('Wrong hex color specified');
        }

        // no alpha passed
        if (3 == $chunksNum) {
            $decimalArray[] = 0;
        } else {
            $alpha = floor(array_shift($decimalArray) / 2);
            $decimalArray[] = $alpha;
        }

        return $decimalArray;
    }
    
    public static function getRgbFromInt($intColor)
    {
        return [
            ($intColor >> 16) & 0xFF,
            ($intColor >> 8) & 0xFF,
            $intColor & 0xFF,
        ];
    }
    
    /**
     * Get Y from RGB in YIQ color model
     * @link https://en.wikipedia.org/wiki/YIQ
     * 
     * @param array $rgb array [red, green, blue]
     * @return int Y
     */
    public static function yiqGetYFromRgb(array $rgb)
    {
        return floor(($rgb[0] * 0.299) + ($rgb[1] * 0.587) + ($rgb[2] * 0.114));
    }

    /**
     * 
     * @param type $angle
     * @param array $backgroundColor
     */
    public function rotate($angle, $backgroundColor = null)
    {
        // convert color to compartible format
        if (!$backgroundColor) {
            $backgroundColor = [0, 0, 0, 127];
        } elseif (is_string($backgroundColor)) {
            $backgroundColor = $this->getRgbFromHex($backgroundColor);
        } elseif (is_array($backgroundColor)) {
            if (count($backgroundColor) < 3 || count($backgroundColor) > 4) {
                throw new \InvalidArgumentException('Wrong color specified');
            }
            // check is alpha specified
            if (!isset($backgroundColor[4])) {
                $backgroundColor[4] = 127;
            }
        } else {
            throw new \InvalidArgumentException('Wrong color specified');
        }

        // create color
        $backgroundColorId = imageColorAllocateAlpha(
            $this->_resource, 
            $backgroundColor[0], 
            $backgroundColor[1], 
            $backgroundColor[2], 
            $backgroundColor[3]
        );

        // rotate image
        $rotatedImageResource = imagerotate($this->_resource, $angle, $backgroundColorId, true);

        imagealphablending($rotatedImageResource, false);
        imagesavealpha($rotatedImageResource, true);

        return new self($rotatedImageResource);
    }

    public function flipVertical()
    {
        // use native function
        if(version_compare(PHP_VERSION, '5.5', '>=')) {
            return new self(imageflip($this->_resource, IMG_FLIP_VERTICAL));
        }

        return $this->_flipVertical();
    }
    
    private function _flipVertical()
    {
        $flippedImageResource = imagecreatetruecolor($this->_width, $this->_height);
        
        for($x = 0; $x < $this->_width; $x++) {
            for($y = 0; $y < $this->_height; $y++) {
                $color = imagecolorat($this->_resource, $x, $y);
                imagesetpixel(
                    $flippedImageResource, 
                    $x, 
                    $this->_height - 1 - $y, 
                    $color
                );
            }
        }
        
        return new self($flippedImageResource);
    }

    public function flipHorizontal()
    {
        // use native function
        if(version_compare(PHP_VERSION, '5.5', '>=')) {
            return new self(imageflip($this->_resource, IMG_FLIP_HORIZONTAL));
        }

        return $this->_flipHorizontal();
    }
    
    private function _flipHorizontal()
    {
        $flippedImageResource = imagecreatetruecolor($this->_width, $this->_height);
        
        for($x = 0; $x < $this->_width; $x++) {
            for($y = 0; $y < $this->_height; $y++) {
                $color = imagecolorat($this->_resource, $x, $y);
                imagesetpixel(
                    $flippedImageResource, 
                    $this->_width - 1 - $x, 
                    $y, 
                    $color
                );
            }
        }
        
        return new self($flippedImageResource);
    }

    public function flipBoth()
    {
        // use native function
        if(version_compare(PHP_VERSION, '5.5', '>=')) {
            return new self(imageflip($this->_resource, IMG_FLIP_BOTH));
        }

        return $this->_flipBoth();
    }
    
    private function _flipBoth()
    {
        $flippedImageResource = imagecreatetruecolor($this->_width, $this->_height);
        
        for($x = 0; $x < $this->_width; $x++) {
            for($y = 0; $y < $this->_height; $y++) {
                $color = imagecolorat($this->_resource, $x, $y);
                imagesetpixel(
                    $flippedImageResource, 
                    $this->_width - 1 - $x, 
                    $this->_height - 1 - $y, 
                    $color
                );
            }
        }
        
        return new self($flippedImageResource);
    }

    public function greyscale()
    {
        $greyscaleImageResource = imagecreatetruecolor(
            $this->_width, 
            $this->_height
        );

        // prepare greyscale palette
        for ($c = 0; $c < 256; $c++) {
            $palette[$c] = imagecolorallocate($greyscaleImageResource, $c, $c, $c);
        }

        // set pixels
        for ($y = 0; $y < $this->_height; $y++) {
            for ($x = 0; $x < $this->_width; $x++) {
                $rgb = imagecolorat($this->_resource, $x, $y);
                $grey = self::yiqGetYFromRgb(self::getRgbFromInt($rgb));
                imagesetpixel($greyscaleImageResource, $x, $y, $palette[$grey]);
            }
        }

        return new self($greyscaleImageResource);
    }

}
