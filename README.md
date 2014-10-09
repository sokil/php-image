php-image
=========

[![Build Status](https://travis-ci.org/sokil/php-image.png?branch=master&1)](https://travis-ci.org/sokil/php-mongo)
[![Latest Stable Version](https://poser.pugx.org/sokil/php-image/v/stable.png)](https://packagist.org/packages/sokil/php-image)
[![Coverage Status](https://coveralls.io/repos/sokil/php-image/badge.png?branch=master)](https://coveralls.io/r/sokil/php-image?branch=master)

* [Installation](#installation)
* [Open image](#open-image)
* [Resize image](#resize-image)
* [Rotate image](#rotate-image)
* [Flip image](#flip-image)
* [Filters](#filters)
* [Adding elements to image](#adding-elements-to-image)
  * [Writing text](#writing-text)
* [Save image](#save-image)

Installation
------------

You may install library through composer:
```json
{
    "require": {
        "sokil/php-image": "dev-master"
    }
}
```

Open image
----------

Opening from filename:

```php
$image = new \Sokil\Image;
$image->loadFile('/path/to/image.jpeg');
```

Opening from GD resource:

```php
$image = new \Sokil\Image;
$image->loadResource($imageResource);
```

Passing to constructor of filename or image resource:

```php
$image = new \Sokil\Image('/path/to/image.jpeg');
$image = new \Sokil\Image($imageResource);
```

There is factory with helps you to create or open images:
```
$factory = new \Sokil\ImageFactory;
$image = $factory->createImage(300, 200);
$image = $factory->openImage('/path/to/file.png');
```

Resize image
------------

There is four resize modes: 'scale', 'fit', 'crop' and 'cache'.

```php
$newImage = $image->resize($mode, $width, $height);
```

If you want to register own resize strategy, extend class from \Sokil\Image\AbstractResizeStrategy and add namespase:
```php
\Sokil\Image::addWriteStrategyNamespace('\Vendor\ResizeStrategy')
```
Classes searches in priority of adding.

Rotate image
------------

Rotating is counter clockwise;

Rotate on 90 degrees:
```php
$newImage = $image->rotate(90);
```

Rotate on 45 degrees, and fill empty field with black color:
```php
$newImage = $image->rotate(45, '#000000');
```

Rotate on 45 degrees, and fill empty field with transparent green color:
```php
$newImage = $image->rotate(45, '#8000FF00');
```

Flip image
----------

Flip in vertical direction:
```php
$newImage = $image->flipVertical();
```

Flip in horisontal direction
```php
$newImage = $image->flipHorisontal();
```

Flip in both directions
```php
$newImage = $image->flipBoth();
```

Filters
-------

Greyscale image:
```php
$newImage = $image->filter('greyscale');
```

If you want to register own filter strategy to support new filters, extend class from \Sokil\Image\AbstractFilterStrategy and add namespase:
```php
\Sokil\Image::addFilterStrategyNamespace('\Vendor\FilterStrategy')
```
Classes searches in priority of adding.

Adding elements to image
------------------------

Element is everything that can me append to image: text, shape, other image. First we need to create element instabce and configure it:
```php
$someElement = $factory->createSelemet('someElement')->setParam1('someValue');
```

Than element placed to image to some coordinates:
```php
$image->appendElementAtPosition($someElement, 30, 30);
```

You can create your own elements thet inherits \Sokil\Image\AbstractElement class, and register namespace:
```php
namespace Vendor\Elements;

class Circle extends \Sokil\Image\AbstractElement
{
    public function setRadius($r) { // code to set radius }
    
    public function draw($resource, $x, $y) 
    {
        // code to draw circle on image $resouce at coordinates ($x, $y)
    }
}

\Sokil\ImageFactory::addElementNamespace('\Vendor\Elements');
```

Now you can draw your own circles:
```php
$circle = $factory->createElement('circle')->setRadiud(100);
$image->appendElementAtPosition($circle, 100, 100);
```

### Writing text

First we need to configure text element:
```php
$factory = new ImageFactory();
        
// text element
$element = $factory
    ->createTextElement()
    ->setText('hello world')
    ->setAngle(20)
    ->setSize(40)
    ->setColor('#ababab')
    ->setFont(__DIR__ . '/FreeSerif.ttf');
```

Now we need to place element in image at some coordinates:
```php
$image->appendElementAtPosition($element, 50, 150);
```

Save image
----------

Library supports three formats of image: 'jpeg', 'png' and 'gif'. 

To write image to disk you must define format of image and configure write strategy:
```php
$image->write('jpeg', function(\Sokil\Image\WriteStrategy\JpegWriteStrategy $strategy) {
    $strategy->setQuality(98)->toDisk('/path/to/file.jpg');
});
```

To send image to STDOUT you must define format of image and configure write strategy:
```php
$image->write('jpeg', function(\Sokil\Image\WriteStrategy\JpegWriteStrategy $strategy) {
    $strategy->setQuality(98)->toStdout();
});
```

If you want to register own write strategy to support new image format, extend class from \Sokil\Image\AbstractWriteStrategy and add namespase:
```php
\Sokil\Image::addWriteStrategyNamespace('\Vendor\WriteStrategy')
```
Classes searches in priority of adding.
