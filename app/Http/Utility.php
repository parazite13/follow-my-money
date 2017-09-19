<?php

namespace App\Http;

class Utility
{
	public static function slugify($string){

		// replace non letter or digits by -
		$string = preg_replace('~[^\pL-\d]+~u', '_', $string);

		// transliterate
		$string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);

		// remove unwanted characters
		$string = preg_replace('~[^-\w]+~', '', $string);

		// trim
		$string = trim($string);

		// remove duplicate _
		$string = preg_replace('~_+~', '_', $string);

		// lowercase
		$string = strtolower($string);

		if (empty($string)) {
			return 'n-a';
		}

		return $string;
	}

	public static function deslugify($string, $maj = false, $nbMaj = -1){

		$i = 0;
		$ret = "";
		$elements = explode('_', $string);

		foreach($elements as $element){
			if(($maj && $i < $nbMaj) || $nbMaj == -1){
				$ret .= " " . ucfirst($element);
			}else{
				$ret .= " " . $element;
			}
			$i++;
		}

		return $ret;
	}
}