<?php

/**
 * Dframe/FileStorage
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/fileStorage/blob/master/LICENSE (MIT)
 */

namespace Dframe\FileStorage;

/**
 * Abstract stylist class.
 *
 * A stylist is an object that processes an image according to a certain scheme.
 * (e.g., trims, grays out, shrinks, etc.).
 *
 * Each stylist should have a stylize($image) method that takes the original.
 * in the form of a byte string and returns it after processing, also in the form of a byte string.
 *
 * Subclasses of stylists should be located in the stylists folder.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
abstract class Stylist
{
    /**
     * Takes the original and returns in processed form. The input is the image resource for the library.
     * PHP GD library, and the output is a resource with the processed image.
     *
     * @param $readStream resource
     * @param $extension    string
     * @param $stylistObj   object
     * @param $stylistParam array
     */
    abstract public function stylize($readStream, $extension, $stylistObj, $stylistParam);

    /**
     * Returns the unique name of the stylist, also depending on the parameters
     *
     * @param array
     *
     * @return string
     */
    abstract public function identify($stylistParam);
}
