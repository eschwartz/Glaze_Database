<?php
/** 
 * AJAX File to be used with MySQLFields
 * javascript object definition.
 *
 * Returns an array of field names for 
 * a given table, along with their 
 * respective data types.
 *
*/


$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

// Checks for AJAX
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {	
	$data = json_decode(strip_tags(stripslashes($_POST['data'])), true); 	// strpslashes requires to remove \" (magic quotes is on)
	if ($data['action'] == 'loadGlaze') {
		$res = loadGlaze($data['GlazeID']);
	} elseif ($data['action'] == 'loadVariation') {
		$res = loadVariation($data['VarID']);
	} elseif ($data['action'] == 'loadVarImage') {
		$res = loadVarImage($data['ImageID']);
	} elseif ($data['action'] == 'saveGlaze') {
		$res = saveGlaze($data['glazeObj']);
	}
	echo json_encode($res);
} elseif ($_GET['test']=='yes') {
	$data = array(
		'action' => 'saveGlaze',
		'VarID' => 1,
		'ImageID' => 1,
		'glazeObj' => array(
			'GlazeID' => 1,
			'GlazeAuthor' => 1
			),
		);
	
	if ($data['action'] == 'loadGlaze') {
		$res = loadGlaze($data['GlazeID']);
	} elseif ($data['action'] == 'loadVariation') {
		$res = loadVariation($data['VarID']);
	} elseif ($data['action'] == 'loadVarImage') {
		$res = loadVarImage($data['ImageID']);
	} elseif ($data['action'] == 'saveGlaze') {
		$res = saveGlaze($data['glazeObj']);
	}
	
	echo "<pre>";
	print_r($res);
	echo "</pre>";

} else {
	// Kicks out non-AJAX
	ob_start();	// needed for header redirect
	$url = "/";			
	header('Location: ' . $url);		
	ob_flush();
}


function loadGlaze($glazeID) {
	global $conn;
	
	// Get Glaze info
	$sql = "SELECT * FROM Glazes \n"
    . "WHERE GlazeID = $glazeID";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	while($row = mysql_fetch_assoc($rs)) {
		$glazeRes[glazeObj] = $row;				// All $glazeRes[glazeObj] variables will be adopted as properties by the JS Glaze Object
	}
	
	// Get Glaze Base Recipe
	$sql = "SELECT GlazeIngredients.IngredientID, GlazeIngredients.IngredientAmt, Ingredients.IngredientName FROM GlazeIngredients \n"
			."LEFT JOIN Ingredients \n"
			."ON GlazeIngredients.IngredientID = Ingredients.IngredientID \n"
			."WHERE GlazeID = $glazeID";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$glazeRes[glazeObj][recipe] = array();
	while($row = mysql_fetch_assoc($rs)) {
		array_push($glazeRes[glazeObj][recipe], $row);
	}
	
	// Get List of Glaze Variation (IDs)
	$sql = "SELECT VarID FROM GlazeVars \n"
			."WHERE GlazeID = $glazeID \n"
			."ORDER BY VarDefault DESC";	// Default variation will be the first item in array
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$glazeRes[varList] = array();
	while($row = mysql_fetch_assoc($rs)) {
		array_push($glazeRes[varList], $row[VarID]);
	}
	
	return $glazeRes;
}

function loadVariation($varID) {
	global $conn;
	
	// Get Var Info
	$sql = "SELECT * FROM GlazeVars \n"
			."WHERE VarID = $varID";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$varRes[varObj] = array();
	while($row = mysql_fetch_assoc($rs)) {
		$varRes[varObj] = $row;
	}
	
	// Get Variation recipe
	$sql = "SELECT VarIngredients.IngredientID, VarIngredients.IngredientAmt, Ingredients.IngredientName \n"
			."FROM VarIngredients \n"
			."LEFT JOIN Ingredients \n"
			."ON VarIngredients.IngredientID = Ingredients.IngredientID \n"
			."WHERE VarID = $varID";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$varRes[varObj][recipe] = array();
	while($row = mysql_fetch_assoc($rs) ) {
		array_push($varRes[varObj][recipe], $row);
	}
	
	// Get a list of Variation Image (IDs)
	$sql = "SELECT ImageID FROM VarImages \n"
			."WHERE VarID = $varID";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$varRes[imageList] = array();
	while($row = mysql_fetch_assoc($rs) ) {
		array_push($varRes[imageList], $row[ImageID]);
	}
	
	return $varRes;
}	// end loadVariation

function loadVarImage($imageID) {
	/**
	 * Load info for the specified image
	 * from the mySQL database
	*/
	
	global $conn;
	
	$sql = "SELECT * FROM Images \n"
			."WHERE ImageID = $imageID";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$imgRes[imgObj] = array();
	while($row = mysql_fetch_assoc($rs)) {
		$imgRes[imgObj] = $row;
	}
	
	return $imgRes;
}	// end loadVarImage

function getSQLFields($table) {
	/**
	 * Returns an array containing the names
	 * of the mySQL fields in a given table
	*/
	global $conn;
	
	$mySQLFields = array();
	
	$sql = "SHOW columns FROM $table";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	while($row = mysql_fetch_assoc($rs)) {
		array_push($mySQLFields, $row[Field]);
	}
	
	return $mySQLFields;	
} // end of getSQLFields

function saveGlaze($glazeObj) {
	/**
	 * Saves entire Glaze object (from JS
	 * to database
	*/
	
	global $conn;
	
	// Creates array of field names in `Glazes` table
	$glazesSQLFields = getSQLFields('Glazes');
	
	if (!$glazeObj['GlazeID']) {
		/**
		 * NEW GLAZE
		 * Insert into `Glazes`, then get auto-ID number
		 * before updated related tables
		*/
	} else {
		/**
		 * EXISTING GLAZE
		 */
		 
		 /* Update `Glazes` table*/
		 // Checks that the property is a valid field in the `Glazes` table
		 $updateParams = array();
		 foreach ($glazeObj as $prop => $val) {
		 	if (in_array($prop,$glazesSQLFields) ) {				
				array_push($updateParams,$prop."=".$val);
			}
		 }
		 
		 if (count($updateParams) > 0) {
			$sql = "UPDATE Glazes \n"
					."SET ".implode($updateParams,', ')." \n"
					."WHERE GlazeID = ".$glazeObj[GlazeID];
			mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
		 }
	}
	
	return $glazeObj;
}	// end of saveGlaze
?>