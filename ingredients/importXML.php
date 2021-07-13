<?php
include_once('../includes/DYLMG_quickArrays.php');

// Code to convert TEMPLATE.XML to ingredients table
// Will add code to empty (Ingredientes, IngredientOxides) table
$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

// Define oxideIDs by formula
$oxideSymbID = createIDValue('OxideFormula', 'OxideID', 'Oxides');


// Define ingredient types
$typeIDValue = array('1' => 'Additive',
					 '2' => 'Aggregate',
					 '3' => 'Alumina',
					 '4' => 'AlumSilicate',
					 '5' => 'Ball Clay',
					 '6' => 'Clay',
					 '7' => 'Colorant',
					 '8' => 'Electrolyte',
					 '9' => 'Elemental',
					 '10' => 'Eutectic',
					 '11' => 'Feldspar',
					 '12' => 'Fireclay',
					 '13' => 'Flux Source',
					 '14' => 'Frit',
					 '15' => 'Kaolin',
					 '16' => 'Opacifier',
					 '17' => 'Other',
					 '18' => 'Raw Mineral',
					 '19' => 'Refractory',
					 '20' => 'Silica'
					 );

$xml = simplexml_load_file('../files/template.xml');
$ingrXML = $xml->materials->material; 	// XML node containing all ingredients

// Loop through ingredients
foreach ($ingrXML as $attr) {	
	$ingrName = mysql_real_escape_string($attr[name]);
	$ingrMW = $attr[weight];
	$ingrTypeID = $attr[type];
	$altNames = explode(", ",$attr[searchkey]);
	$ingrType = $typeIDValue[trim($ingrTypeID)];
	
	// Insert Ingredients
	$sql = 	  "INSERT INTO Ingredients (IngredientName, IngredientMW, IngredientCat) \n"
			. "VALUES ('$ingrName', $ingrMW, '$ingrType')";
	mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	$ingrID = mysql_insert_id();
	
	// Insert Alt Names
	$sql_altNames = array();
	foreach ($altNames as $name) {
		if(strlen($name) > 0) {
			array_push($sql_altNames,"($ingrID, '".mysql_real_escape_string($name)."')");
		}
	}
	$sql = "INSERT INTO IngredientAlt (IngredientID, IngredientAltName) \n"
			. "VALUES " . implode(",",$sql_altNames);
	if (count($sql_altNames) > 0) {	// only run sql if alt names exist
		mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	}
	
	
	// Loop through oxides for each ingredient
	// And adds to $sql_ingrOxide array
	$sql_ingrOxide = array();
	if($ingrOxides = $attr->chemistry->oxide) {								// Check that ingredient has oxides defined
		foreach ($ingrOxides as $oxide) {
			$symb = trim($oxide[symbol]);
			$oxideID = $oxideSymbID[$symb];
			$oxideAmt = $oxide[formula];
			array_push($sql_ingrOxide,"($ingrID, $oxideID, $oxideAmt)");
		}
		
		$sql = "INSERT INTO IngredientOxides (IngredientID, OxideID, OxideAmt) \n"
				. "VALUES ". implode($sql_ingrOxide,",");
		mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	}
}

echo "Success!!";

?>
<html>
<h1>Load all ingredients</h1>
</html>