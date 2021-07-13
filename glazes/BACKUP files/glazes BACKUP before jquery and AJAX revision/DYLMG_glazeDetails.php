<?php
include_once('../includes/DYLMG_SuperHTML.php');
?>

<?php
$glazeID = $_GET['glazeID'];
$glazeInfo = getGlazeInfoArry();												// Creates array: $glazeInfo[field] = value
$ingrIDAmt = getRecipe($glazeID);											// Creates array: $ingrIDAmt[ingrID] = ingrAmt
$ingrIDName = createIDValue('IngredientID', 'IngredientName', 'Ingredients');	// Creates array: $ingrIDName[ingrID] = ingrName

function getIngrTable() {
	global $ingrIDAmt;
	global $ingrIDName;
	$ingrTable = "";
	
	// Header row = field names
	$ingrTable .= "<table border=1>\n"
				 . "	<thead>\n"
				 . "		<td>Ingredient</td>\n"
				 . "		<td>Amount</td>\n"
				 . "	</thead>\n";
				 
	
	// Field Values
	foreach ($ingrIDAmt as $ingrID => $ingrAmt) {
		$ingrTable .= 	"	<tr>\n"
						. "		<td>$ingrIDName[$ingrID]</td>\n"
						. "		<td>$ingrAmt</td>\n"
						. "	</tr>\n";
	}
	$ingrTable .= "</table>";
	return $ingrTable;
} // end of getIngrTable()

function getGlazeInfoArry() {
	// Creates array: $glazeInfo[$field] = $value
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");

	////////////////////////////// SELECT Glaze info ////////////////////////////////////////
	global $glazeID;
	$sql = "SELECT GlazeID, GlazeName, GlazeTemp, GlazeColor, GlazeSurface, GlazeDatePosted\n"
							. "FROM Glazes\n"
							. "WHERE GlazeID='$glazeID'";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	
	$glazeInfo = array();
	while ($row = mysql_fetch_assoc($rs)) {
		foreach ($row as $field => $value) {
			$glazeInfo[$field] = $value;
		}
	}
	
	return $glazeInfo;
}// end of getGlazeInfoArry()

function getGlazeInfoTable() {
	global $glazeInfo;
	
	$glazeInfoTable = "<table border=1>\n";
	foreach ($glazeInfo as $field => $value) {
		if ($field != 'GlazeID' && $field != "GlazeName") {
			$glazeInfoTable .= 	  "	<tr>\n"
								. "		<td>$field</td>\n"
								. "		<td>$value</td>\n"
								. "	</tr>\n";
		}
	}
	$glazeInfoTable .= "</table>\n";
	
	return $glazeInfoTable;
} // end of getGlazeInfoTable()
?>

<?php
$glazeName = $glazeInfo['GlazeName'];
$glazeInfoTable = getGlazeInfoTable();
$ingrTable = getIngrTable();
$unityTable = createUnityTable($glazeID);

$s = new DYLMG_SuperHTML('Glaze Details - $glazeName');
$s->buildTop();

$body = <<<HERE
<h1>$glazeName</h1>

<h3>Glaze Info</h3>
$glazeInfoTable

<h3>Ingredient</h3>
$ingrTable

<h3>Unity Formula</h3>
$unityTable
HERE;
// End of $body

$s->addText($body);
$s->buildBottom();
print $s->getPage();

?>
