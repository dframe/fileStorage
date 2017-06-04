<?php
namespace Dframe\fileStorage\stylist;

/*
 * Prosty stylista
 * Zwraca obrazek taki jakim jest
 */

class simpleStylist extends \Dframe\fileStorage\stylist {
	
	public function stylize($layer, $stylistParam){
		
	}

	public function identify($stylistParam){
		return 'simpleStylist';
	}

}