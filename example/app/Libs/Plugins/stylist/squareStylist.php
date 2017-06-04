<?php
namespace Libs\Plugins\stylist;

/*
 * Abstrakcyjna klasa kwadratowego stylisty
 * Wycina kwadrat ze srodkowej czesci obrazka
 * Bok kwadratu ma dlugosc w pikselach, podana w
 * tablicy $stylistParam jako wpis o kluczu 'size'
 */

class squareStylist extends \Dframe\fileStorage\stylist {
	
	public function stylize($layer, $stylistParam){
		if(isset($stylistParam['size'])){
			$size = $stylistParam['size'];
		}
		else{
			$size = '100';
		}

		$layer->resize($size, $size, 'fill_crop');
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