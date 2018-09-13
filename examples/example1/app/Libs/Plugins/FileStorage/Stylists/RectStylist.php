<?php
namespace Libs\Plugins\FileStorage\Stylists;

use Imagecraft\ImageBuilder;

/*
 * Abstrakcyjna klasa prostokatnego stylisty
 * Wycina prostokat ze srodkowej czesci obrazka
 * Boki prostokata maja dlugosc w pikselach, podane w
 * tablicy $stylistParam jako wpisy o kluczach 'w' i 'h'
 */

/**
 * Class RectStylist
 *
 * @package Libs\Plugins\FileStorage\Stylists
 */
class RectStylist extends \Dframe\FileStorage\Stylist
{
    /**
     * @param resource $originStream
     * @param string   $extension
     * @param bool     $stylistObj
     * @param bool     $stylistParam
     *
     * @return bool|resource
     * @throws \Exception
     */
    public function stylize($originStream, $extension, $stylistObj = false, $stylistParam = false)
    {
        $options = ['engine' => 'php_gd', 'locale' => 'pl_PL'];
        $builder = new ImageBuilder($options);

        $layer = $builder->addBackgroundLayer();
        $contents = stream_get_contents($originStream);
        $layer->contents($contents);
        $layer->resize($stylistParam['w'], $stylistParam['h'], 'fill_crop');
        
        fclose($originStream);
        
        $image = $builder->save();
        
        $tmpFile = tmpfile();
        if ($image->isValid()) {
            fwrite($tmpFile, $image->getContents());
        } else {
            throw new \Exception($image->getMessage()); //echo $image->getMessage().PHP_EOL;
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
        return 'rectStylist-'.$stylistParam['w'].'-'.$stylistParam['h'];
    }
}
