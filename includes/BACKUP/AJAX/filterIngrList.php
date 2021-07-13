<?php
include('../DYLMG_quickArrays.php');
$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

?>
<?php
/*
filterIngrList.php
Ingredient Name sent from postGlaze.php
Send back filtered array of ingredients, ids
*/

// Check for AJAX Request, or boot user
if(is_ajax()) {	
	$filter = strip_tags($_POST['filter']);
	$res = runSearch($filter);					
	echo json_encode($res);
}
elseif($_GET['test'] == 'yes') {
	$filter = "new";
	echo "<h1>Results for '$filter'</h1>";
	$res = runSearch($filter);	
	printArray($res);
}
else {
	// Kicks out non-AJAX
	ob_start();	// needed for header redirect
	$url = "/usr/myAccount.php";			
	header('Location: ' . $url);		
	ob_flush();
}

function runSearch($filter) {
	global $conn;
	
	// Get matching Ingredient Names
	$sql = "SELECT IngredientID, IngredientName, IngredientBase \n"
			. "FROM Ingredients \n"
			. "WHERE IngredientName LIKE '%".$filter."%' \n"
			. "LIMIT 0, 5";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	$ingrIDName = array();		// ((ID, NAME, BASE),(ID,NAME,BASE),...)
	while ($row = mysql_fetch_array($rs)) {
		$ingrID = $row[IngredientID];
		$ingrName = $row[IngredientName];
		$ingrBase = $row[IngredientBase];
		array_push($ingrIDName,array($ingrID, $ingrName, $ingrBase));
	}
	
	// Get Matching alternative Ingr Names
	$sql = "SELECT IngredientAlt.IngredientID, IngredientAlt.IngredientAltName, Ingredients.IngredientBase \n"
			. "FROM IngredientAlt \n"
			. "RIGHT JOIN Ingredients ON Ingredients.IngredientID = IngredientAlt.IngredientID \n"
			. "WHERE IngredientAlt.IngredientAltName LIKE '%".$filter."%' \n"
			. "LIMIT 0,5";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	while ($row = mysql_fetch_array($rs)) {
		$ingrID = $row[IngredientID];
		$ingrName = $row[IngredientAltName];
		$ingrBase = $row[IngredientBase];
		array_push($ingrIDName,array($ingrID, $ingrName, $ingrBase));
	}

	return $ingrIDName;		// ((ID, NAME, BASE),(ID,NAME,BASE),...)
}

?>