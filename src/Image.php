<?php

namespace Sokil\Image;

use Sokil\Image\AbstractFilterStrategy;
use Sokil\Image\AbstractResizeStrategy;
use Sokil\Image\AbstractWriteStrategy;
use Sokil\Image\ColorModel\Rgb;
use Sokil\Image\AbstractElement;
use Sokil\Image\Exception\ImageException;

class Image
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

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

    /**
     * Create empty image with defined width and height
     *
     * @param int $width
     * @param int $height
     * @return Image
     */
    public function create($width, $height)
    {
        return $this->loadResource(imagecreatetruecolor($width, $height));
    }

    /**
     * Create image from file
     *
     * @param $filename
     * @return $this
     * @throws \Exception
     */
    public function loadFile($filename)
    {
        if (!file_exists($filename)) {
            throw new ImageException('File ' . $filename . ' not found');
        }

        if (!is_readable($filename)) {
            throw new ImageException('File ' . $filename . ' not readable');
        }

        $imageInfo = @getimagesize($filename);
        if (!$imageInfo) {
            throw new ImageException('Wrong image format');
        }

        if (!in_array($imageInfo[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))) {
            throw new \Exception('Only image of JPEG, PNG and GIF formats supported');
        }

        $this->width = $imageInfo[0];

        $this->height = $imageInfo[1];

        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $this->resource = @imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_PNG:
                $this->resource = @imagecreatefrompng($filename);
                break;
            case IMAGETYPE_GIF:
                $this->resource = @imagecreatefromgif($filename);
                break;
        }

        return $this;
    }

    /**
     * Create file from resource
     *
     * @param resource $resource
     * @return $this
     *
     * @throws \Exception
     */
    public function loadResource($resource)
    {
        if (!(is_resource($resource) && 'gd' === get_resource_type($resource))) {
            throw new \Exception('Must be resource of type "gd", ' . gettype($resource) . ' given');
        }

        $this->resource = $resource;
        $this->width = imagesx($resource);
        $this->height = imagesy($resource);

        return $this;
    }

    /**
     * Get image resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Fill image with color
     *
     * @param int|string|array|Rgb $color
     * @param int $x
     * @param int $y
     * @return $this
     */
    public function fill($color, $x = 0, $y = 0)
    {
        $color = Rgb::normalize($color);
        
        imagefill(
            $this->resource,
            $x, 
            $y, 
            imagecolorallocatealpha(
                $this->resource,
                $color->getRed(), 
                $color->getGreen(), 
                $color->getBlue(), 
                $color->getAlpha()
            )
        );
        
        return $this;
    }

    /**
     * Resize image
     *
     * @param AbstractResizeStrategy $resizeStrategy
     * @param int $width
     * @param int $height
     *
     * @return $this
     */
    public function resize(
        AbstractResizeStrategy $resizeStrategy,
        $width,
        $height
    ) {
        return $this->loadResource(
            $resizeStrategy->resize(
                $this->resource,
                $width,
                $height
            )
        );
    }

    /**
     * Write image to file
     *
     * @param AbstractWriteStrategy $writeStrategy
     *
     * @return AbstractWriteStrategy
     */
    public function write(AbstractWriteStrategy $writeStrategy)
    {
        $writeStrategy->write($this->resource);
        return $this;
    }

    /**
     * Rotate image
     *
     * @param float $angle
     * @param int|string|array|Rgb $backgroundColor
     *
     * @return $this
     */
    public function rotate(
        $angle,
        $backgroundColor = null
    ) {
        $backgroundColor = $backgroundColor 
            ? Rgb::normalize($backgroundColor)
            : new Rgb(0, 0, 0, 127);

        // create color
        $backgroundColorId = imageColorAllocateAlpha(
            $this->resource,
            $backgroundColor->getRed(), 
            $backgroundColor->getGreen(), 
            $backgroundColor->getBlue(), 
            $backgroundColor->getAlpha()
        );

        // rotate image
        $rotatedImageResource = imagerotate($this->resource, $angle, $backgroundColorId, true);

        imagealphablending($rotatedImageResource, false);
        imagesavealpha($rotatedImageResource, true);

        return $this->loadResource($rotatedImageResource);
    }

    /**
     * Flip image
     *
     * @return $this
     */
    public function flipVertical()
    {
        // use native function
        if(function_exists('imageflip')) {
            $resource = imageflip($this->resource, IMG_FLIP_VERTICAL);
        } else {
            $resource = imagecreatetruecolor($this->width, $this->height);
            for($x = 0; $x < $this->width; $x++) {
                for($y = 0; $y < $this->height; $y++) {
                    $color = imagecolorat($this->resource, $x, $y);
                    imagesetpixel(
                        $resource,
                        $x,
                        $this->height - 1 - $y,
                        $color
                    );
                }
            }
        }
        
        return $this->loadResource($resource);
    }

    public function flipHorizontal()
    {
        // use native function
        if(function_exists('imageflip')) {
            $resource = imageflip($this->resource, IMG_FLIP_HORIZONTAL);
        } else {
            $resource = imagecreatetruecolor($this->width, $this->height);
            for($x = 0; $x < $this->width; $x++) {
                for($y = 0; $y < $this->height; $y++) {
                    $color = imagecolorat($this->resource, $x, $y);
                    imagesetpixel(
                        $resource,
                        $this->width - 1 - $x,
                        $y,
                        $color
                    );
                }
            }
        }
        
        return $this->loadResource($resource);
    }

    public function flipBoth()
    {
        // use native function
        if(function_exists('imageflip')) {
            $resource = imageflip($this->resource, IMG_FLIP_BOTH);
        } else {
            $resource = imagecreatetruecolor($this->width, $this->height);
            for($x = 0; $x < $this->width; $x++) {
                for($y = 0; $y < $this->height; $y++) {
                    $color = imagecolorat($this->resource, $x, $y);
                    imagesetpixel(
                        $resource,
                        $this->width - 1 - $x,
                        $this->height - 1 - $y,
                        $color
                    );
                }
            }
        }
        
        return $this->loadResource($resource);
    }

    /**
     * Apply filter to image
     *
     * @param AbstractFilterStrategy $filterStrategy
     *
     * @return $this
     */
    public function filter(AbstractFilterStrategy $filterStrategy)
    {
        $this->loadResource($filterStrategy->filter($this->resource));
        
        return $this;
    }

    /**
     * Crop image
     *
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     *
     * @return $this
     */
    public function crop($x, $y, $width, $height)
    {
        $croppedImageResource = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $croppedImageResource, 
            $this->resource,
            0, 
            0, 
            $x, 
            $y, 
            $width, 
            $height, 
            $width, 
            $height
        );
        
        $this->loadResource($croppedImageResource);
    }

    /**
     * Add element to specified position
     *
     * @param AbstractElement $element
     * @param int $x
     * @param int $y
     *
     * @return $this
     */
    public function appendElementAtPosition(AbstractElement $element, $x, $y)
    {
        $element->draw($this->resource, $x, $y);
        return $this;
    }
}
