php-image
=========

* [Installation](#installation)
* [Open image](#open-image)
* [Resize image](#resize-image)
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

Resize image
------------

There is four resize modes: 'scale', 'fit', 'crop' and 'cache'.

```php
$newImage = $image->resize($mode, $width, $height);
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