<?php
include('../includes/DYLMG_SuperHTML.php');
?>
<?php
// Checks of ingr is submitted, then runs function to INSERT
if ($_POST['submit']) {
	submitIngr();
}


function createOxidesTable() {
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	$sqlFlux = "SELECT Oxides.OxideID, Oxides.OxideFormula\n"
    		. "FROM Oxides \n"
			. "WHERE (Oxides.OxideCat=\"Flux\")";
	$rsFlux = mysql_query($sqlFlux,$conn);
	
	$sqlFlow = "SELECT Oxides.OxideID, Oxides.OxideFormula\n"
    		. "FROM Oxides \n"
			. "WHERE (Oxides.OxideCat=\"Flow\")";
	$rsFlow = mysql_query($sqlFlow,$conn);
	
	$sqlGlass = "SELECT Oxides.OxideID, Oxides.OxideFormula\n"
    		. "FROM Oxides \n"
			. "WHERE (Oxides.OxideCat=\"Glass\")";
	$rsGlass = mysql_query($sqlGlass,$conn);


	// Fluxes table
	$rsFluxTable = "<table>\n";
	$i = 0;
	$halfway = mysql_num_rows($rsFlux)/2;
	while ($row = mysql_fetch_assoc($rsFlux)) {
		// Starts a new table at halfway through rows
		if ($i >= $halfway) {
			$rsFluxTable .=   "</table>\n</td>\n"		
							. "<td>\n<table>\n";		// Starts a 2nd table of fluxes in oxides (master) table
			$i=0;
		}
		$i++;
		$rsFluxTable .= "<tr>\n";
		$rsFluxTable .= "		<td>$row[OxideFormula]</td>\n";
		$rsFluxTable .= "		<td><input type='text' name='$row[OxideFormula]' size='4' /></td>\n";
		$rsFluxTable .= "<tr>\n";
	}
	$rsFluxTable .= "</table></div>\n";
	mysql_free_result($rsFlux);
	
	// Flow table
	$rsFlowTable = "<div style='float:left; padding-left:3%;'>\n<table>\n";
	while ($row = mysql_fetch_assoc($rsFlow)) {
		// Starts a new table at halfway through rows
		$i++;
		$rsFlowTable .= "<tr>\n";
		$rsFlowTable .= "		<td>$row[OxideFormula]</td>\n";
		$rsFlowTable .= "		<td><input type='text' name='$row[OxideFormula]' size='4' /></td>\n";
		$rsFlowTable .= "<tr>\n";
	}
	$rsFlowTable .= "</table></div>\n";
	mysql_free_result($rsFlow);
	
	// Glass table
	$rsGlassTable = "<div style='float:left; padding-left:3%;'>\n<table>\n";
	while ($row = mysql_fetch_assoc($rsGlass)) {
		// Starts a new table at halfway through rows
		$i++;
		$rsGlassTable .=  "<tr>\n"
						. "		<td>$row[OxideFormula]</td>\n"
						. "		<td><input type='text' name='$row[OxideFormula]' size='4' /></td>\n"
						. "<tr>\n";
	}
	$rsGlassTable .= "</table></div>\n";
	mysql_free_result($rsGlass);
	
	// Creates oxides table
	// flux,flow,glass tables are each a column in a single row
	$oxidesTable = new SuperHTML();
	$oxidesTable->startTable(1);
	$oxidesTable->tRow(array($rsFluxTable, $rsFlowTable, $rsGlassTable));
	$oxidesTable->endTable();
		
	return $oxidesTable->getPage();
}



function submitIngr() {
	/////////////////////////// Query to insert new Ingr /////////////////////////////
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	$sql = "INSERT INTO Ingredients (IngredientName, IngredientLOI, IngredientTE)\n"
		. "VALUES ('$_POST[ingrName]', $_POST[LOI], $_POST[thermalExp]) ";
	mysql_query($sql,$conn);
	
	/////////////////////////// Query to retrieve $IngrID_new /////////////////////////////
	
	// Gets ID for new ingredient submission as $IngrID_new		(to be used in M2M table)
	$sql = 	"SELECT IngredientID \n"
			. "FROM Ingredients \n"
			. "ORDER BY IngredientID DESC LIMIT 1";		// Selects the last IngredientID
	$rs = mysql_query($sql, $conn);
	if (!$rs) {
		die('Query error: ' . mysql_error());
	}
	while ($row = mysql_fetch_array($rs)) { $IngrID_new = $row['IngredientID']; }
	mysql_free_result($rs);
	///////////////////////////////////////////////////////////////////////////////////////
	
	
	// Loops through oxide inputs
	// Creates INSERT VALUES.... string
	$oxideList = createOxideArray();													// Creates array of $oxideList['Formula'] => [oxideID]
	$sqlInsertValues = "";
	foreach ($_POST as $formula => $oxideAmt) {
		if(!empty($oxideAmt) && array_key_exists($formula,$oxideList)) {				// Verifies as oxide input, and not empty id
			$sqlInsertValues .= "($IngrID_new, $oxideList[$formula], $oxideAmt),";  
		}
	}
	$sqlInsertValues = substr($sqlInsertValues,0,-1);									// Removes the last "," from the string.
	
	/////////////////////////// Query to Insert IngredientOxides (M2M) //////////////////////////
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");

	$sql = "INSERT INTO IngredientOxides (IngredientID, OxideID, OxideAmt)\n"
			. "VALUES $sqlInsertValues";
	if (!mysql_query($sql, $conn)) {
		die('Could not execute query:' . mysql_error());
	}
	/////////////////////////////////////////////////////////////////////////////////////////////
}
?>
<?php
$s = new DYLMG_SuperHTML('Submit an Ingredient');
$s->buildTop();

$oxidesTable = createOxidesTable();
$body = <<<HERE
<form action=$_SERVER[PHP_SELF] method='post' >
<table id="table_ingrDetails">
	<tr>
    	<td>Ingr Name</td>
        <td><input type="text" name="ingrName" /></td>
    </tr>
    <tr>
    	<td>Thermal Exp.</td>
        <td><input type="text" name="thermalExp" /></td>
    </tr>
    <tr>
    	<td>LOI</td>
        <td><input type="text" name="LOI" /></td>
    </tr>
</table>

$oxidesTable
<div style="clear:both">
<input type='submit' name="submit" value="Submit Ingr" />
</div>
</form>
HERE;
// end of $body

$s->addText($body);
$s->buildBottom();
print $s->getPage();
?>