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
		$res = loadGlaze($data['glazeObj']);
	} elseif ($data['action'] == 'loadVariation') {
		$res = loadVariation($data['varObj']);
	} elseif ($data['action'] == 'loadVarImage') {
		$res = loadVarImage($data['ImageID']);
	} elseif ($data['action'] == 'saveGlaze') {
		$res = saveGlaze($data['glazeObj']);
	}
	echo json_encode($res);
} elseif ($_GET['test']=='yes') {
	$data = array(
		'action' => 'loadVarImage',
		'ImageID' => 1,
		'glazeObj' => array(
			'GlazeID' => 1,
			'id' => 1,
			'GlazeAuthor' => 1,
			'recipe' => array( array('IngredientAmt'=>10.5, 'IngredientID'=>18, 'IngredientName'=>'Albany Slip'), 
							   array('IngredientAmt'=>52.6, 'IngredientID'=>28, 'IngredientName'=>'Boric Acid'), 
							   array('IngredientAmt'=>19.7, 'IngredientID'=>5, 'IngredientName'=>'Alkatrol') )
			),
		'varObj' => array(
			'VarID' => 1,
			'id' => 1,
			'recipe' => array( array('IngredientAmt'=>2.5, 'IngredientID'=>18, 'IngredientName'=>'Bentonite'), 
				   array('IngredientAmt'=>5.0, 'IngredientID'=>202, 'IngredientName'=>'Rutile') )
			)
		);
	$data[glazeObj][variations] = array($data[varObj]);
	
	if ($data['action'] == 'loadGlaze') {
		$res = loadGlaze($data['glazeObj']);
	} elseif ($data['action'] == 'loadVariation') {
		$res = loadVariation($data['varObj']);
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


function loadGlaze($glazeObj) {
	global $conn;
		
	// Get Glaze info
	$sql = "SELECT * FROM Glazes \n"
    . "WHERE GlazeID = ".$glazeObj[GlazeID];
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	// Check for valid result, or return 0 (new glaze)
	if (mysql_num_rows($rs) < 1) {
		$glazeRes[glazeObj][GlazeID] = 0;
		return $glazeRes;
	}
	
	while($row = mysql_fetch_assoc($rs)) {
		$glazeRes[glazeObj] = $row;				// All $glazeRes[glazeObj] variables will be adopted as properties by the JS Glaze Object
	}
	
	// Get Glaze Base Recipe
	$sql = "SELECT GlazeIngredients.IngredientID, GlazeIngredients.IngredientAmt, Ingredients.IngredientName FROM GlazeIngredients \n"
			."LEFT JOIN Ingredients \n"
			."ON GlazeIngredients.IngredientID = Ingredients.IngredientID \n"
			."WHERE GlazeID = ".$glazeObj[GlazeID];
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$glazeRes[glazeObj][recipe] = array();
	while($row = mysql_fetch_assoc($rs)) {
		array_push($glazeRes[glazeObj][recipe], $row);
	}
	
	// Get List of Glaze Variation (IDs)
	$sql = "SELECT VarID FROM GlazeVars \n"
			."WHERE GlazeID = ".$glazeObj[GlazeID]." \n"
			."ORDER BY VarDefault DESC";	// Default variation will be the first item in array
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$glazeRes[varList] = array();
	while($row = mysql_fetch_assoc($rs)) {
		array_push($glazeRes[varList], $row[VarID]);
	}
	
	return $glazeRes;
}

function loadVariation($varObj) {
	global $conn;
	
	// Get Var Info
	$sql = "SELECT * FROM GlazeVars \n"
			."WHERE VarID = ".$varObj[VarID];
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
			."WHERE VarID = ".$varObj[VarID];
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$varRes[varObj][recipe] = array();
	while($row = mysql_fetch_assoc($rs) ) {
		array_push($varRes[varObj][recipe], $row);
	}
	
	// Get a list of Variation Image (IDs)
	$sql = "SELECT ImageID FROM VarImages \n"
			."WHERE VarID = ".$varObj[VarID];
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
	
	$sql = "SELECT Images.*, Users.UserLogin FROM Images \n"
			."LEFT JOIN Users ON Images.ImageAuthor = Users.UserID \n"
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
	 
	function saveInfo($obj, $isGlaze) {
		global $conn;
		
		/* Update `Glazes` or `GlazeVars` table*/
		$SQLFields = ($isGlaze)? getSQLFields('Glazes'): getSQLFields('GlazeVars');
		$table = ($isGlaze)? 'Glazes': 'GlazeVars';
		$condition = ($isGlaze)? 'WHERE GlazeID = '.$obj[GlazeID]: 'WHERE VarID = '.$obj[VarID];
			
			// Checks that the property is a valid field in the `Glazes` table
			$updateParams = array();
			foreach ($obj as $prop => $val) {
				if (in_array($prop,$SQLFields) ) {					// Only updates from Glaze Object properties that are also fields in `Glazes` table
					if (!is_numeric($val)) { $val = "'$val'"; }		// Put quotes around non-numbers
					array_push($updateParams,$prop."=".$val);
				}
			}
			
			// Update table
			if (count($updateParams) > 0) {
				$sql = "UPDATE ".$table." \n"
						."SET ".implode($updateParams,', ')." \n"
						.$condition;						
				mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
			}
	}	// end of saveInfo
	
	function saveRecipe($obj, $isGlaze) {
		/* Updates glaze or variation recipes*/
		
		// Checks that recipe is not empty
		if(count($obj[recipe]) < 1) {
			return false;
		}
		
		global $conn;
		
		// Set up sql depending on glaze vs. variation
		$table = ($isGlaze)? 'GlazeIngredients': 'VarIngredients';
		$idField = ($isGlaze)? 'GlazeID': 'VarID';

		
	
		// DELETE all glaze ingredients
		$sql = "DELETE FROM ".$table." \n"
		 		."WHERE ".$idField." = ".$obj[id];
		mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
		
		// Create INSERT VALUES list 
		$recipeValues = array();
		foreach($obj[recipe] as $ingredient) {		
			$valuesRow = "(".$obj[id].", ".$ingredient[IngredientID].", ".$ingredient[IngredientAmt].")";
			array_push($recipeValues, $valuesRow);
		}
		
		// Insert all ingredients
		$sql = "INSERT INTO ".$table." (".$idField.", IngredientID, IngredientAmt) \n"
				."VALUES ".implode($recipeValues, ', ');
		mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));

	}	// end of saveIngr
	
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

		// Save info for Glaze object, and each Variation object
		saveInfo($glazeObj, true);
		saveRecipe($glazeObj, true);
		foreach ($glazeObj[variations] as $variation) {
			saveInfo($variation, false);
			saveRecipe($variation, false);
		}
	}
		  
	
	return $glazeObj;
}	// end of saveGlaze
?>