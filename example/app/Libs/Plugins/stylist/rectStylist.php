<?php
namespace Libs\Plugins\stylist;

/*
 * Abstrakcyjna klasa prostokatnego stylisty
 * Wycina prostokat ze srodkowej czesci obrazka
 * Boki prostokata maja dlugosc w pikselach, podane w
 * tablicy $stylistParam jako wpisy o kluczach 'w' i 'h'
 */

class rectStylist extends \Dframe\fileStorage\stylist {
	
	public function stylize($layer, $stylistParam){
		$layer->resize($stylistParam['w'], $stylistParam['h'], 'fill_crop');
	}

	public function identify($stylistParam){

		return 'rectStylist-'.$stylistParam['w'].'-'.$stylistParam['h'];
	}



}