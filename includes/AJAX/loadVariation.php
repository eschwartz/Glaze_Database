<?php 
// loadVariation.php
// Called via AJAX from postGlaze.php
/*
Returns:
$varRecipe (ingrID => ingrAmt)				// For specified $_POST[varID], or default for varID = 'default'
$isVarAuthor (true/false)					// Is the specified GlazeAuthor also the author of the var
$varInfo (varID => FieldName => value)		// List of all vars for a specified glazeID, if varID = 'list'
*/
include_once('../DYLMG_quickArrays.php'); 
?>
<?php
$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

if(is_ajax()) {	
	$varID = strip_tags($_POST['varID']); 	// strpslashes requires to remove \" (magic quotes is on)
	$glazeID = strip_tags($_POST['glazeID']); 
	$glazeAuthor = strip_tags($_POST['glazeAuthor']); 
	
	// Checks for empty glazeID
	if ($glazeID == 'new' || $glazeID == "") {
		echo "Error: GlazeID is new or empty";
	}

	if ($varID == 'list') {
		$res[varList] = getVarList($glazeID);
	} else {
		if ($varID == 'default') {
			$varID = getDefaultVar($glazeID);
		}
		$res[varInfo] = getVarInfo($varID);
		$res[varRecipe] = getVarRecipe($varID);
		$res[isVarAuthor] = getIsVarAuthor($varID, $glazeAuthor);
		$res[varIngrIDName] = getVarIngrIDName($varID);
		$res[varImages] = getVarImages($varID);
		$res[varID] = $varID;		
	}
	
	echo json_encode($res);
}
else if($_GET['test'] == 'yes') {
	$varID = 'default';
	$glazeID = 1; 
	$glazeAuthor = 1;
	echo "<h1>loadVariation.php Test</h1>";

	// Checks for empty glazeID
	if ($glazeID == 'new' || $glazeID == "") {
		echo "Error: GlazeID is new or empty";
	}

	if ($varID == 'list') {
		$res[varList] = getVarList($glazeID);
	} else {
		if ($varID == 'default') {
			$varID = getDefaultVar($glazeID);
		}
		$res[varInfo] = getVarInfo($varID);
		$res[varRecipe] = getVarRecipe($varID);
		$res[isVarAuthor] = getIsVarAuthor($varID, $glazeAuthor);
		$res[varIngrIDName] = getVarIngrIDName($varID);
		$res[varImages] = getVarImages($varID);
		$res[varID] = $varID;		
	}
	
	printArray($res);
}
else {
	// Kicks out non-AJAX
	ob_start();	// needed for header redirect
	$url = "/";			
	header('Location: ' . $url);		
	ob_flush();
}

function getDefaultVar($glazeID) {
// Returns default varID for a given glazeID
	global $conn;
	
	$sql = "SELECT VarID \n"
			."FROM GlazeVars \n"
			."WHERE GlazeID = $glazeID \n"
			."AND VarDefault = 1 ";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	if(!$rs) {
		die("Unable to find default glaze variation");
	} else {
		$varID = mysql_result($rs, 0, 'VarID');
	}
	
	return $varID;
}

function getVarList($glazeID) {
	global $conn;
	
	$sql = "SELECT * \n"
			."FROM GlazeVars \n"
			."INNER JOIN Glazes ON Glazes.GlazeID = GlazeVars.GlazeID \n"
			."WHERE Glazes.GlazeID = $glazeID \n";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));

	$varList = array();
	while ($row = mysql_fetch_assoc($rs)) {
		$varID = $row[VarID];
		foreach ($row as $field => $value) {
			$varList[$varID][$field] = $value;
		}
	}
	
	// If there are no variations, postGlaze.php will not try to build a list of them.
	if (mysql_num_rows($rs) < 1) {
		return 'none';
	}
	
	return $varList;

} // end of getVarInfo

function getVarInfo ($varID) {
	global $conn;
	
	$sql = "SELECT * \n"
			."FROM GlazeVars \n"
			."WHERE VarID = $varID";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	$varInfo = array();
	while ($row=mysql_fetch_assoc($rs)) {
		foreach ($row as $field => $value) {
			$varInfo[$field] = $value;
		}
	}
	
	// Get the username of the author
	$sql = "SELECT UserLogin \n"
			."FROM Users \n"
			."WHERE UserID = ".$varInfo[VarAuthor]." \n";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	while ($row=mysql_fetch_array($rs)) {
		$varInfo[UserLogin] = $row[UserLogin];
	}
	
	return $varInfo;

}// end of getVarInfo

function getVarRecipe($varID) {
	global $conn;
		
	$sql = "SELECT IngredientID, IngredientAmt, VarID \n"
			."FROM VarIngredients \n"
			."WHERE VarID = $varID";		
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	$varRecipe = array();
	while($row=mysql_fetch_array($rs)) {
		$ingrID = $row[IngredientID];
		$ingrAmt = $row[IngredientAmt];
		$varRecipe[$ingrID] = $ingrAmt;
	}
	
	return $varRecipe;
}

function getVarIngrIDName($varID) {
// Gets names for all ingrs in variation
// To use in recipe table in postGlaze.php
	global $conn;
	$sql = "SELECT Ingredients.IngredientID, Ingredients.IngredientName \n"
			."FROM Ingredients \n"
			."RIGHT JOIN VarIngredients \n"
			."ON VarIngredients.IngredientID = Ingredients.IngredientID \n"
			."WHERE VarIngredients.VarID = $varID";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	$varIngrIDName = array();
	while($row = mysql_fetch_array($rs)) {
		$ingrID = $row[IngredientID];
		$ingrName = $row[IngredientName];
		$varIngrIDName[$ingrID] = $ingrName;
	}
	
	return $varIngrIDName;
}

function getIsVarAuthor($varID, $glazeAuthor) {
// Returns true if the var author is the same as the glaze author
	global $conn;
	
	$sql = "SELECT VarAuthor \n"
			."FROM GlazeVars \n"
			."WHERE VarID = $varID";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	while($row=mysql_fetch_array($rs)) {
		if ($glazeAuthor == $row[VarAuthor]) {
			$isVarAuthor = true;
		}
		else {
			$isVarAuthor = false;
		}
	}

	return $isVarAuthor;		
}

function getVarImages($varID) {
// Returns $varImages = ((src, descr), (...),...)
	global $conn;
	
	$sql = "SELECT Images.ImageSrc, Images.ImageDescr, Users.UserID, Users.UserLogin \n"
			."FROM Images \n"
			."RIGHT JOIN (VarImages LEFT JOIN Users ON VarImages.ImageAuthor = Users.UserID)\n"
			."ON Images.ImageID = VarImages.ImageID \n"
			."WHERE VarImages.VarID = $varID";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	$varImages = array();
	while($row = mysql_fetch_array($rs)) {
		array_push($varImages, array($row[ImageSrc], $row[ImageDescr], $row[UserID], $row[UserLogin]));
	}
	
	return $varImages;
}
?>