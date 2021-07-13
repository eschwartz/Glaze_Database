<?php
// * DYLMG_quickArrays.phh
// * A series of PHP functions that
// * are useful in the DYLMG database

// * Summary of Functions
// Function:				Params:									Description:
// getRecipe 				$glazeID								Returns array of $ingr[id] => amt for a given glazeID
// createIDValue 			$field_ID, $field_value, $table			Returns array of $whatever[field1] = field2 from a single table
// createOxideArray			none									Returns array of $oxide[id] = formula	(DEPRECATED: use createIDValue)
// createOxideWgtArray		none									Returns array of $oxide[id] = weight 	(DEPRECATED: use createIDValue)
// is_ajax					none									Returns true if there is an active AJAX request
// ecryptPass				$pass, $login							Encrypt a password using crypt and md5
// isInt					$n										Checks if $n is an integer. Returns true/false.
// isFloat					$n										Checks if $n is a floating number. Returns true/false.


function getRecipe ($glazeID) {
	////////////////////////////// Select Glaze Ingredients ////////////////////////////////////////
	$sql = "SELECT GlazeIngredients.IngredientID, GlazeIngredients.IngredientAmt\n"
		. "FROM (GlazeIngredients INNER JOIN Glazes ON GlazeIngredients.GlazeID = Glazes.GlazeID)"
		. "WHERE Glazes.GlazeID='$glazeID'";
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	$ingrResult = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	//////////////////////////////////////////////////////////////////////////////////
	
	$ingrIDAmt = array();
	while ($row = mysql_fetch_array($ingrResult)) {
		$ingrID = $row['IngredientID'];
		$ingrAmt = $row['IngredientAmt'];
		$ingrIDAmt[$ingrID] = $ingrAmt;
	}

	return $ingrIDAmt;
}

// For any table, gets a field value associated with an id
// Create array $IDValue[id] = name
function createIDValue($field_ID, $field_value, $table) {
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	$sql = "SELECT $field_ID, $field_value\n"
			. "FROM $table\n";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	
	$IDName = array();
	while ($row = mysql_fetch_array($rs)) {
		$id = $row[$field_ID];
		$value = $row[$field_value];
		$IDValue[$id] = $value;
	}
	
	return $IDValue;
}

function createOxideArray() {
// Returns $oxideList[oxID]=oxFormula
// Can you createIDValue instead;
	$oxideIDName = createIDValue('OxideID', 'OxideFormula', 'Oxides');
	return $oxideIDName;
}

function createOxideWgtArray() {
// Returns $oxideWeight[OxideID] = Oxide Weight	
// Can use createIDValue() instead...
	$oxideWeight = createIDValue('OxideID','OxideWgt','Oxides');
	return $oxideWeight;
}

	
function printArray($array) {
	echo "<pre>\n";
	print_r($array);
	echo "</pre>\n";
}

function is_ajax() {
// Returns true if there is an active AJAX request
// Adapted from: http://snipplr.com/view/12114/detect-if-ajax-or-json-request/
	$is_ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND 
				strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	return $is_ajax;
}

function ecryptPass	($pass, $login) {
// Encrypt a password using crypt and md5
// use this function for consistent encryption method
	$encryptPass = crypt(md5($pass), md5($login));
	
	return $encryptPass;
// From http://www.ibm.com/developerworks/opensource/library/os-php-encrypt/
} // end encryptPass

function isInt($n){
return ( $n == strval(intval($n)) )? true : false;
}

function isFloat($n){
    return ( $n == strval(floatval($n)) )? true : false;
}
?>