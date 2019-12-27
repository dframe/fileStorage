<?php

use Dframe\FileStorage\Storage;

error_reporting(E_ALL);          # Debug settings
ini_set("display_errors", "off"); # Debug settings

define("APP_DIR", dirname(__FILE__) . '/');

include_once '../../vendor/autoload.php';

include "Stylists/OriginalStylist.php";
include "Stylists/RectStylist.php";
include "Stylists/SquareStylist.php";

$Storage = new Storage();
$Storage->settings(
    [
        'stylists' => [
            'Original' => OriginalStylist::class,
            'Rect' => RectStylist::class,
            'Square' => SquareStylist::class
        ]
    ]
);

$images = [];
$images[] = ['size' => 'Rect 50x50', 'img' => $Storage->image('picture1.jpg')->stylist('Rect')->size('50x50')->get()];
$images[] = [
    'size' => 'Rect 50x50 Custom Image if file not exist',
    'img' => $Storage->image('fileNotExist.jpg', 'noImage.png')->stylist('Rect')->size('50x50')->get()
];
$images[] = [
    'size' => 'Rect 250x100',
    'img' => $Storage->image('picture1.jpg')->stylist('Rect')->size('250x100')->get()
];
$images[] = [
    'size' => 'Square 250 width',
    'img' => $Storage->image('picture1.jpg')->stylist('Square')->size('250')->get()
];
$images[] = [
    'size' => 'Rect 250x550',
    'img' => $Storage->image('picture1.jpg')->stylist('Rect')->size('250x550')->get()
];
$images[] = ['size' => 'Original', 'img' => $Storage->image('picture1.jpg')->stylist('Orginal')->get()];

?>

<?php foreach ($images as $key => $image) {
    ?>
    Size: <?php echo $image['size']; ?>;<br>
    <img alt="" src="cache/<?php echo $image['img']['cache']; ?>">
    <hr>
    <?php
} ?>