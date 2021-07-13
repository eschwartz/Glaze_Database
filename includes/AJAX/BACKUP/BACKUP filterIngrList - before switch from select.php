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
	$filter = "clay";
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
	
	$sql = "SELECT IngredientID, IngredientName \n"
			. "FROM Ingredients \n"
			. "WHERE IngredientName LIKE '%".$filter."%'";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	$ingrIDName = array();
	while ($row = mysql_fetch_array($rs)) {
		$ingrID = $row[IngredientID];
		$ingrName = $row[IngredientName];
		$ingrIDName[$ingrID] = $ingrName;
	}
	
	return $ingrIDName;
}

?>