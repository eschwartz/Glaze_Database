<?php 
// saveGlaze.php
// Called via AJAX from postGlaze.php
// Updates glaze info in database
// Or inserts new glaze
include_once('../DYLMG_quickArrays.php'); 
?>
<?php
$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

if(is_ajax()) {	
	$recipe = json_decode(strip_tags(stripslashes($_POST['recipe'])), true); 	// strpslashes requires to remove \" (magic quotes is on)
	$glazeInfo = json_decode(strip_tags(stripslashes($_POST['glazeInfo'])), true);
	$type = strip_tags($_POST['type']);
	$glazeID = saveGlaze($recipe, $glazeInfo, $type);	
	//$varID = saveGlaze($varRecipe, $varInfo, 'Var');
	echo $glazeID;
} elseif ($_GET['test']=='yes') {
	$recipe[2] = 22.22;
	$recipe[245] = 5;
	$recipe[36] = 99;
	//$glazeInfo[VarID] = 'new';
	$glazeInfo[GlazeID] = 3;
	$glazeInfo[GlazeAuthor] = 1;
	$type = 'Glaze';
	$glazeID = saveGlaze($recipe, $glazeInfo, $type);	
	echo "<br>$glazeID";
} else {
	// Kicks out non-AJAX
	ob_start();	// needed for header redirect
	$url = "/";			
	header('Location: ' . $url);		
	ob_flush();
}

function saveGlaze($recipe, $glazeInfo, $type) {
	global $conn;
	
	// Sets sql tables
	if ($type == 'Glaze') {
		$infoTable = 'Glazes';
		$ingrTable = 'GlazeIngredients';
	} elseif ($type == 'Var') {
		$infoTable = 'GlazeVars';
		$ingrTable = 'VarIngredients';
	} else { return "Invalid Type"; }
	
	// Remove GlazeID from array, to prevent sql from inserting auto-incr
	$glazeID = $glazeInfo[$type.'ID'];
	unset($glazeInfo[$type.'ID']);	
	
	
	// NEW GLAZE -> INSERT
	if ($glazeID == 'new') {
		// Set Date Posted to current date
		$glazeInfo[$type.'DatePosted'] = 'CURDATE()';

		// Create list of Fields
		$infoFieldsString = join(",", array_keys($glazeInfo));
		
		// Create list of values
		$infoValues = array();
		foreach ($glazeInfo as $field => $value) {
			$value = trim($value);
			if (!is_numeric($value) || (int)$value != $value) {		
				if ($value != 'CURDATE()') {								// And can't be CURDATE() either...
					$value = "'".mysql_real_escape_string($value)."'";		// Escape and put quotes around strings						
				}								
			}
			array_push($infoValues, $value);
		}
		$infoValuesString = join(",",$infoValues);
		
		// INSERTS glazeInfo INTO Glazes
		$sqlGlazes =  "INSERT INTO $infoTable ($infoFieldsString) \n"
					. "VALUES ($infoValuesString) \n";
		mysql_query($sqlGlazes,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sqlGlazes));
			//echo "<br><br>SQL (insert into $infoTable): ". str_replace("\n","<br>\n",$sqlGlazes); 
		
		$glazeID = mysql_insert_id(); 
		
		// INSERT GLAZE INGREDIENTS
		
		if (count($recipe) > 0) {					// Empty recipe means this is a new variation with no ingredients. Save variation info, but not recipe
			// Creates array of GlazeIngredient Values
			$recipeValues = array();							
			foreach ($recipe as $ingrID => $ingrAmt) {
				$rowArray = '('.$glazeID.','.$ingrID.','.$ingrAmt.')';		
				array_push($recipeValues, $rowArray);
			}
			$recipeValues = join(",", $recipeValues);	
			
			// Inserts Ingredients for glaze
			$sqlIngr = 	  "INSERT INTO $ingrTable (".$type."ID, IngredientID, IngredientAmt) \n "
						. "VALUES $recipeValues";
			mysql_query($sqlIngr,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sqlIngr));
				//echo "<br><br>SQL (insert into $ingrTable): ". str_replace("\n","<br>\n",$sqlIngr); 
		}
		
		return $glazeID;
	}
	
	// EXISTING GLAZE -> UPDATE
	else {	// If glaze exists -> update
		$glazeInfo[$type.'DatePosted'] = 'CURDATE()';

		// Creates string of "field = value" for SQL UPDATE
		$infoArray = array();
		foreach ($glazeInfo as $field => $value) {
			// Put quotes around strings
			if (!is_numeric($value) || (int)$value != $value) {	
				if ($value != 'CURDATE()') {	// And can't be CURDATE() either...
					$value = trim($value);
					$value = "'".mysql_real_escape_string($value)."'";	
				}
			}
			$setItem = $field . "=" . $value;
			array_push($infoArray, $setItem);
		}
		$infoString = join(",", $infoArray);
		
		$sqlGlazes = "UPDATE $infoTable \n"
					 . "SET $infoString \n"				// SET field = value, field2 = value2
					 . "WHERE ".$type."ID = $glazeID";
		mysql_query($sqlGlazes,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sqlGlazes));
			//echo "<br><br>SQL (update $infoTable): ". str_replace("\n","<br>\n",$sqlGlazes); 
		
		
		
		// UPDATE/INSERT GlazeIngredient
		if (count($recipe) > 0) {	
			// Remove all glaze ingredients
			// And replace with new values
			$sql = "DELETE FROM $ingrTable \n"
					."WHERE ".$type."ID = $glazeID";
			mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
				//echo "<br><br>SQL (delete $ingrTable): ". str_replace("\n","<br>\n",$sql); 
				
			// Format ingredient insert for sql
			$recipeValues = array();							
			foreach ($recipe as $ingrID => $ingrAmt) {
				$rowArray = '('.$glazeID.','.$ingrID.','.$ingrAmt.')';		
				array_push($recipeValues, $rowArray);
			}
			$recipeValues = join(",", $recipeValues);	
			
			// Insert all ingredient
			$sql = "INSERT INTO $ingrTable (".$type."ID, IngredientID, IngredientAmt) \n "
					."VALUES $recipeValues";
			mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
		}
		return $glazeID;
	}
}
?>
