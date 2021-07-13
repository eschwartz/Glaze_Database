<?php
/** ingrAutoComplete.php
 * Response script for jquery autocomplete widget
 * Used for selecting ingredients
 *
 * See tutorial: http://www.giveupalready.com/content.php?187-jQuery-Autocomplete-Tutorial
*/

$filter = $_GET['term'];

$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

$sql = "SELECT DISTINCT IngredientID, IngredientName\n"
    . "FROM Ingredients\n"
    . "WHERE IngredientName LIKE '%".$filter."%' \n"
    . "UNION\n"
    . "SELECT DISTINCT IngredientID, IngredientAltName AS IngredientName\n"
    . "FROM IngredientAlt\n"
    . "WHERE IngredientAltName LIKE '%".$filter."%' \n"
    . "ORDER BY IngredientName ASC ";
$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
$poorMatch = array();
$matchStart = array();		// Separate out results that match the first letter of the filter
while($row = mysql_fetch_assoc($rs) ) {
	$item = array('label' => $row[IngredientName], 'value' => $row[IngredientID]);
	// Separate out ingredients that start the same as the filter
	if (strripos($row[IngredientName],$filter) == 0) {
		array_push($matchStart, $item);
	} else {
		array_push($poorMatch, $item);
	}
}
$ingrList = array_slice(array_merge($matchStart, $poorMatch),0,6);			// Limit to 6, and put best matches at start of array

echo json_encode($ingrList);

/*
	echo "<pre>";
	echo "<h1>ingrList</h1>";
	print_r($ingrList);
	echo "</pre>";
*/		

?>