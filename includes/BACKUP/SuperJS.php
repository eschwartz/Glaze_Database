<?php
// SuperJS class definition
// Used to quickly create JS code from php

class SuperJS {
	
// Function	:				Params:							Description:
// getPHPAssoc				$phpArray						Converts a PHP assoc array  to javascript, returning JS code {$key: value};
// addPHPAssoc				$phpArray, $jsArrayName			Converts a PHP assoc array to javascript, and adds to page 
// get3dArray				$phpArray						Returns JS code to converts a 3d array from PHP to javascript.
// addText					$text							Add any text/code to the page
// getPage						none						Returns entire page as a string
// getAssocAlert			$jsAssoc						Returns code for alert msg of "key is value" for a js Assoc Array
// addAssocAlert			$jsAssoc						Adds code for alert msg of "key is value" for a js Assoc Array

	var $jsPage;
	
	function __construct() {
		$jsPage = "";
	}
	
	function getPHPAssoc($phpArray) {
	// Returns code to define JS array: "{key: value, key2: value2, ...}"
		if (count($phpArray) < 1) {	
			return "{}";				// if array is empty..
		}
		else {
			$js = 	 "{\n";
			foreach ($phpArray as $key => $value) {
				if(is_string($key)) { $key = "'$key'"; }				// Puts quotes around strings
				if(is_string($value)) { $value = "'$value'"; }
				$js .= "\t \t \t $key: $value,\n";
			}
			$js = substr($js,0,-2);		// Removes final ",\n" from array
			$js .= "}";
			
			return $js;
		}
	} // end of convertPHPAssoc
	
	function addPHPAssoc($phpArray, $jsArrayName) {
		$temp = "var $jsArrayName = " . $getPHPAssoc($phpArray);
		addText($temp);
	} // end of addPHPAssoc
	
	function get3dAssoc($phpArray, $jsArrayName) {
	// Returns JS code to converts a 3d array from PHP to javascript.
	// Will actually create js array with name $jsArrayName (unlike getPHPAssoc which returns [a:b, c:d, etc...}
			
		// start with PHP:
		// $array[key1][key2] = value
		
		$jsCode = "$jsArrayName = new Array(); \n";
		// Populate array
		foreach ($phpArray as $key1 => $array) {
			$jsCode .= $jsArrayName . "[" . $key1 . "] = new Array(); \n"; 	// Can't write "$jsArrayName[$key1]" or it will try to write a php array
			$jsCode .= $jsArrayName . "[" . $key1 . "] = ";
			$jsCode .= $this->getPHPAssoc($array) . "; \n";		// Returns "{key:value}" for $array[key2] = value
		}
		
		return $jsCode;
	} // end of get3dAssoc

	function addText($text) {
		$this->jsPage .= $text . "\n";
	} // end of addText
	
	function getPage() {
		return $this->jsPage;
	} // end of getPage
}
?>