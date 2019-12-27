<?php

use Dframe\FileStorage\Stylist;
use Imagecraft\ImageBuilder;

/*
 * Abstrakcyjna klasa prostokatnego stylisty
 * Wycina prostokat ze srodkowej czesci obrazka
 * Boki prostokata maja dlugosc w pikselach, podane w
 * tablicy $stylistParam jako wpisy o kluczach 'w' i 'h'
 */

/**
 * Class RectStylist
 */
class RectStylist extends Stylist
{
    /**
     * @param resource $originStream
     * @param string   $extension
     * @param bool     $stylistObj
     * @param bool     $stylistParam
     *
     * @return bool|resource
     * @throws Exception
     */
    public function stylize($originStream, $extension, $stylistObj = false, $stylistParam = false)
    {
        $options = ['engine' => 'php_gd', 'locale' => 'pl_PL'];
        $builder = new ImageBuilder($options);

        $layer = $builder->addBackgroundLayer();
        $contents = stream_get_contents($originStream);
        $layer->contents($contents);

        if (isset($stylistParam['size'])) {
            $size = explode('x', $stylistParam['size']);
            $stylistParam['w'] = $size[0];
            $stylistParam['h'] = $size[1];
        }

        $layer->resize($stylistParam['w'], $stylistParam['h'], 'fill_crop');

        fclose($originStream);

        $image = $builder->save();

        $tmpFile = tmpfile();
        if ($image->isValid()) {
            fwrite($tmpFile, $image->getContents());
        } else {
            throw new Exception($image->getMessage()); //echo $image->getMessage().PHP_EOL;
        }

        rewind($tmpFile);
        return $tmpFile;
    }

    /**
     * @param $stylistParam
     *
     * @return string
     */
    public function identify($stylistParam)
    {
        return 'RectStylist-' . $stylistParam['size'];
    }
}
