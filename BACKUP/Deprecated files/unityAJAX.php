<?php
// Glaze ingredient and oxide info from database
// Sends to CalculateUnity.js for processing

$ingrIDString = $_REQUEST['ingrIDString'];
//$ingrIDList = explode(",",ingrIDString);

$unityMaster = array();		// Contains all arrays to send back to CalculateUnity.js
$unityMaster[ingrIDMW] = getIngrMW($ingrIDString);
$test['a'] = "json test a";
echo json_encode($test);











// Summary of functions
// Function:				Param:				Return:										Description:
// getIngrMW				none				$ingrIDME	(ingrid => ingrMW)					Creates array of Ingredient MW's
// getIngrOxideAmt			none				$ingrOxideAmt (ingrID=>oxideID=>oxideAmt)	Returns array of oxide amounts in ingr formulas (For all ingredients)

function getIngrMW($ingrIDString) {
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	$sql = 	  "SELECT IngredientID, IngredientMW \n"
			. "FROM Ingredients \n"
			. "WHERE IngredientID IN ($ingrIDString)";
	$rs = mysql_query($sql,$conn) or die("Could not execute getIngrMW query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	$ingrIDMW = array();
	foreach ($row as mysql_fetch_array($rs)) {
		$ingrID = $row[IngredientID];
		$ingrMW = $row[IngredientMW];
		$ingrIDMW[$ingrID] = $ingrMW;
	}
	
	return $ingrIDMW;

}

function getIngrOxideAmt() {
// Returns array of $ingrOxideAmt[ingrID][oxideID] = amount of oxide in Ingredient formula
// For all ingredients in IngredientOxides
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	// Returns fields: IngredientID, OxideID, OxideAmt
	$sql = 	  "SELECT Ingredients.IngredientID, IngredientOxides.OxideID, IngredientOxides.OxideAmt \n"
			. "FROM Ingredients LEFT JOIN IngredientOxides \n"		// Joining tables, so I can make sure ingr exists in Ingredients table (ie, not a straggler)
			. "ON Ingredients.IngredientID = IngredientOxides.IngredientID";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	$ingrOxideAmt = array();
	while ($row = mysql_fetch_array($rs)) {
		$ingrID = $row[IngredientID];
		$oxideID = $row[OxideID];
		$oxideAmt = $row[OxideAmt];
		
		$ingrOxideAmt[$ingrID][$oxideID] = $oxideAmt;
	}
	
	return $ingrOxideAmt;
}

?>