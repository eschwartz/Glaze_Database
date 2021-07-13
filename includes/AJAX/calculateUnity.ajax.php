<?php 
include_once('../DYLMG_quickArrays.php'); 
include_once('../DYLMG_SuperHTML.php');	// for creating unity table
?>
<?php
// * DYLMG_calculateUnity.php

// * Calculates unity formula for a given glaze recipe
// * Can be used with AJAX and JSON recipe {ingrID : ingrAmt}
// * Also, can send $glazeID, and will get existing recipe from database.

// * Summary of Functions
// Function:			Param:								Return:									Description:
// getRecipe			none								$recipe	(ingrID => ingrAmt)				Gets recipe (ONLY for php/form requests.)
// calculateUnity		$recipe	(From JS Glaze Object)		$unityOxide (oxideID => oxideAmt)		MASTER FUNCTION.
// getUnityTable		$unityOxide							$unityHTMLTable	(HTML string)			Creates a unity table with 3 subtables (Flux, Flow, Glass)
//
// UNITY STEPS:
// getRecipePW			$recipe 							$recipePW (ingrID=>ingrAmt)	 			1. Returns percentage amt of each base ingredient.		
// getIngrIDME			$recipePW							$ingrIDME (ingrID=>ingrME) 				2. Returns Mol. Equiv. of each ingredient
// getOxideIDSum		$ingrIDME							$oxideIDSum (oxideID=>sum)				3. Calculates the sum of each oxide in the glaze
// getUnityOxide		$oxideIDSum							$unityOxide (oxideID => oxideAmt)		4. Calculates unity amount for each oxide 

// createUnityTable			$glazeID								Returns HTML for a Unity table, with sub-tables for flux, flow, glass oxides

// For Ajax requests: calculate unity from recipe, echo JSON
// For form request: first, get recipe from glaze ID. Then return $unityOxide
$ingrList = "";		// list of ingrIDs, for easy SQL criteria

// Declare some global variables
// So we can reduce number of SQL queries in this script
	/* This could all be gotten in one simple SELECT query
	 * Then put into a $oxide(ID=>id, Name=>name) array */
$oxideIDFormula = createIDValue('OxideID', 'OxideFormula', 'Oxides');	// Gets formula name for each oxideID
$oxideIDName = createIDValue('OxideID', 'OxideName', 'Oxides');			// Gets full name of each oxide
$oxideIDCat = createIDValue('OxideID', 'OxideCat', 'Oxides'); 			// Gets category for each oxideID

// Detect data type
// And send back 
if(is_ajax()) {	
	$data = json_decode(strip_tags( stripslashes($_POST['data']) ), true); 	// strpslashes requires to remove \" (magic quotes is on)
	
	/* Convert recipe object to old format (for now...)
	 * From: $data[recipe][i] = ('IngredientID' => ingrID, 'IngredientAmt' => ingrAmt, 'IngredientName' => ingrName);
	 * To: $recipe[ingrID] = ingrAmt
	*/
	$recipe = array();
	foreach($data[recipe] as $ingr) {
		$ingrID = $ingr[IngredientID];
		$ingrAmt = $ingr[IngredientAmt];
		$recipe[$ingrID] = $ingrAmt;
	}
	
	$unityOxide = calculateUnity($recipe);
	/* Convert unity oxide to new JS/OOP format
	 * FROM: $unityOxide[oxideID] = oxideAmt
	 * TO: $unity = ('OxideID' => oxideID, 'OxideFormula' = oxideForm, 'OxideName' => oxideName, 'OxideCat' => oxideCata, 'OxideAmt' => oxideAmt)
	*/
	$unityObj = array();
	foreach($unityOxide as $oxideID => $oxideAmt) {
		$oxideFormula =  $oxideIDFormula[$oxideID];
		$oxideName = $oxideIDName[$oxideID];
		$oxideCat = $oxideIDCat[$oxideID];
		array_push($unityObj, array('OxideID'=>$oxideID, 'OxideFormula'=>$oxideFormula, 'OxideName'=>$oxideName, 'OxideCat'=>$oxideCat, 'OxideAmt'=>$oxideAmt) );	
	}
	
	//$unityHTMLTable = getUnityTable($unityOxide);
	echo json_encode($unityObj); 	// Use to return an array of $unityOxide (oxideID => unity amt)
	//echo $unityHTMLTable;				// Use to return an HTML table of the unity formula
}
elseif ($_REQUEST['glazeID']) {
	$glazeID = strip_tags($_REQUEST['glazeID']);
	$recipe = getRecipe($glazeID);						// From quickArrays.php
	$unityOxide = calculateUnity($recipe);
	return $unityOxide;
}


function getUnityTable($unityOxide) {
// Creates a unity table with 3 subtables (Flux, Flow, Glass)

	$s = new SuperHTML();
	
	$oxideIDFormula = $GLOBALS['oxideIDFormula'];	// Gets formula name for each oxideID
	$oxideIDCat = $GLOBALS['oxideIDCat'];			// Gets category for each oxideID
	
	// Makes sure a value is set for Si02, Al2O3 -- or sets to 0
	if (!$unityOxide[15]) {
		$unityOxide[15] = 0;
	}
	if (!$unityOxide[13]) {
		$unityOxide[13] = 0;
	}

	
	// Create array of (formula, oxide) for each category
	// (e.g.): $fluxArray = (('CaO', .567), ('BaO', .021). ....). 
	// Each ('RO', .123) will become a line in SuperHTML table
	$fluxArray = array();
	$flowArray = array();
	$glassArray = array();
	foreach ($unityOxide as $oxideID => $unityAmt) {
		// Formate formula with subscript numbers (how?)
		$formula = $oxideIDFormula[$oxideID];
		
		$unityAmt = number_format($unityAmt, 3);
		switch ($oxideIDCat[$oxideID]) {
		case 'Flux':
			array_push($fluxArray, array($formula, $unityAmt));
			break;
		case 'Flow':
			array_push($flowArray, array($formula, $unityAmt));
			break;
		case 'Glass':
			array_push($glassArray, array($formula, $unityAmt));
			break;
		}
	}
	
	// get code for the three tables (without adding to superHTML object text)
	$fluxTable = $s->gBuildTable($fluxArray);
	$flowTable = $s->gBuildTable($flowArray);
	$glassTable = $s->gBuildTable($glassArray);	
	
	// Creates unity table
	// Where each column (in a single row) is a category table
	$unityHTMLTable = $s->gBuildTable(array(array($fluxTable, $flowTable, $glassTable)));

	return $unityHTMLTable;
} // end of getUnityTable

function calculateUnity($recipe) {
// Master Function
// Calls each component function, and passes info on to the next one.
	// Creates list of ingredientIDs for easy SQL criteria
	$ingrArray = array();
	foreach ($recipe as $ingrID => $amt) {
		array_push($ingrArray, $ingrID);
	}
	$GLOBALS['ingrList'] = join(",", $ingrArray);
	
	// Step by step Unity Calculation
	$recipePW = getRecipePW($recipe);			// 1. Returns percentage amt of each base ingredient.
	$ingrIDME = getIngrIDME($recipePW);			// 2. Returns Mol. Equiv. of each ingredient
	$oxideIDSum = getOxideIDSum($ingrIDME);		// 3. Calculates the sum of each oxide in the glaze
	$unityOxide = getUnityOxide($oxideIDSum); 	// 4. Calculates unity amount for each oxide  
	
	return $unityOxide;
} // end of calculateUnity

function getRecipePW($recipe) {
// 	1. Returns percentage amt of each base ingredient.		
	$recipeSum = 0;
	$recipePW = array();
	
	// Adds each amt to $recipeSum
	foreach ($recipe as $ingrID => $amt) {
		$recipeSum += $amt;
	}
	
	// Divides each amt by total
	foreach ($recipe as $ingrID => $amt) {
		$recipePW[$ingrID] = ($amt / $recipeSum) * 100;
	}
	
	return $recipePW;	
}// end of getRecipePW

function getIngrIDME($recipePW) {
// 2. Returns Mol. Equiv. of each ingredient. 
// ME = PW/ingrMW

	// First get Mol. Wgt of each ingredient
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	$ingrList = $GLOBALS['ingrList'];
	$sql = 	  "SELECT IngredientID, IngredientMW \n"
			. "FROM Ingredients \n"
			. "WHERE IngredientID IN ($ingrList)";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	while ($row = mysql_fetch_array($rs)) {
		$ingrID = $row[IngredientID];
		$ingrMW = $row[IngredientMW];
		$ingrIDMW[$ingrID] = $ingrMW;
	}
	
	// calculates ME
	$ingrIDME = array();
	foreach ($recipePW as $ingrID => $ingrPW) {
		$ingrMW = $ingrIDMW[$ingrID];
		$ingrIDME[$ingrID] = $ingrPW / $ingrMW;
	}
		
	
	return $ingrIDME;
	
} // end of getIngrIDME

function getOxideIDSum($ingrIDME) {
// 3. Calculates the sum of each oxide in the glaze

	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	// Get amount of each oxide in each ingredient formula
	// $ingrOxideAmt[ingrID][oxideID] = oxideAmt in ingredient formula;
	$ingrList = $GLOBALS['ingrList'];	// list of ingredientIDs in recipe
	$sql =    "SELECT IngredientID, OxideID, OxideAmt \n"
			. "FROM IngredientOxides \n"
			. "WHERE IngredientID IN ($ingrList)";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	$ingrOxideAmt = array();
	while ($row = mysql_fetch_array($rs)) {
		$ingrID = $row[IngredientID];
		$oxideID = $row[OxideID];
		$oxideAmt = $row[OxideAmt];
		
		$ingrOxideAmt[$ingrID][$oxideID] = $oxideAmt;
	}
	
	// OxideME in an ingr = (oxide amt in ingr formula) * (Ingredient ME)
	// Get sum of OxideME
	$oxideIDSum = array();
	foreach ($ingrIDME as $ingrID => $ingrME) {			// $ingrIDME is already filtered for ingr in recipe
		// Loop through oxides in this ingredient
		foreach ($ingrOxideAmt[$ingrID] as $oxideID => $oxideAmt) {
			$oxideME = $oxideAmt * $ingrME;
			$oxideIDSum[$oxideID] += $oxideME;		// Add oxideME for each ingredient to the total oxide ME
		}
	}
	
	return $oxideIDSum;	
} // end of getOxideIDSum

function getUnityOxide($oxideIDSum){
// 4. Calculates unity amount for each oxide 

	// get OxideIDCat, to sort oxides and get sum of fluxes
	$oxideIDCat = $GLOBALS['oxideIDCat']; 
	
	// Calculates sum of fluxes
	$fluxSum = 0;
	foreach ($oxideIDSum as $oxideID => $oxideSum) {
		if ($oxideIDCat[$oxideID] == "Flux") {
			$fluxSum += $oxideSum;
		}
	}
	
	// Divides each oxide by sum of flux
	$unityOxide = array();	// $unityOxide (oxideID => oxide unity amt)
	foreach ($oxideIDSum as $oxideID => $oxideSum) {
		$unityOxide[$oxideID] = $oxideSum / $fluxSum;		// Unity amount for each oxide = (sum of oxide ME) / (sum of flux oxides)
	}
	
	return $unityOxide;
	
} // end of getUnityOxide
?>