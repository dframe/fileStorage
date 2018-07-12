Dframe/FileStorage
===================

[![Build Status](https://travis-ci.org/dframe/fileStorage.svg?branch=master)](https://travis-ci.org/dframe/fileStorage) [![Latest Stable Version](https://poser.pugx.org/dframe/fileStorage/v/stable)](https://packagist.org/packages/dframe/fileStorage) [![Total Downloads](https://poser.pugx.org/dframe/fileStorage/downloads)](https://packagist.org/packages/dframe/fileStorage) [![Latest Unstable Version](https://poser.pugx.org/dframe/fileStorage/v/unstable)](https://packagist.org/packages/dframe/fileStorage) [![License](https://poser.pugx.org/dframe/fileStorage/license)](https://packagist.org/packages/dframe/fileStorage)

File system which allow you to store and style images

## Preview
[![License](https://github.com/dframe/fileStorage/blob/master/preview.jpg)](https://github.com/dframe/fileStorage/blob/master/preview.jpg)



#### Installation

    composer require dframe/filestorage

----------

Simple usage
```php
    $Storage->put('local', $_FILES['file']['tmp_name'], 'upload/picture1.jpg');
    $Storage->image('picture1.jpg')->stylist('Square')->size('250')->get();
    $Storage->image('picture1.jpg')->stylist('Rect')->size('250x550')->get();
    $Storage->image('fileNotExist.jpg', 'noImage.png')->stylist('Rect')->size('50x50')->get();
    $Storage->drop('local', 'upload/picture1.jpg');
```

**Documentation available at** https://dframeframework.com/en/docs/fileStorage/master/configuration

## What's included?
 * Image stylist
 * Storage files and information

# Feature
 * Text on images

## Examples

For DframeFramework [Example #1](https://github.com/dframe/fileStorage/tree/master/examples/example1) 

Stalone Image Stylist Code PHP [Example #2](https://github.com/dframe/fileStorage/tree/master/examples/example2) 

