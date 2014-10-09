php-image
=========

* [Installation](#installation)
* [Open image](#open-image)
* [Resize image](#resize-image)
* [Rotate image](#rotate-image)
* [Flip image](#flip-image)
* [Filters](#filters)
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
$factory = new \Sokil\Image\Factory;
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
