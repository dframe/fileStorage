<?php
namespace Libs\Plugins\stylist;
use Imagecraft\ImageBuilder;

/*
 * Abstrakcyjna klasa kwadratowego stylisty
 * Wycina kwadrat ze srodkowej czesci obrazka
 * Bok kwadratu ma dlugosc w pikselach, podana w
 * tablicy $stylistParam jako wpis o kluczu 'size'
 */

class squareStylist extends \Dframe\fileStorage\stylist {

    public function stylize($originStream, $extension, $stylistObj = false, $stylistParam = false){

        $options = ['engine' => 'php_gd', 'locale' => 'pl_PL'];
        $builder = new ImageBuilder($options);

        $layer = $builder->addBackgroundLayer();
        $contents = stream_get_contents($originStream);
        $layer->contents($contents);
        
        
		if(isset($stylistParam['size'])){
			$size = $stylistParam['size'];
		}
		else{
			$size = '100';
		}
        
        $layer->resize($size, $size, 'fill_crop');
        
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

	public function identify($stylistParam){
		if(isset($stylistParam['size'])){
			$size = $stylistParam['size'];
		}
		else{
			$size = '100';
		}

		return 'squareStylist-'.$size;
	}



}