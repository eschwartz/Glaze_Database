<?php
// $results = quickQuery($sql);		--- Connects to DYLMG database and returns sql results
// $rsTable = quickQueryTable($sql)  --- returns code for a table w/query results
include('../includes/quickQuery.php');

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
	$rsFluxTable = "<div style='float:left;'>\n<table>\n";
	$i = 0;
	$halfway = mysql_num_rows($rsFlux)/2;
	while ($row = mysql_fetch_assoc($rsFlux)) {
		// Starts a new table at halfway through rows
		if ($i >= $halfway) {
			$rsFluxTable .= "</table>\n</div>\n<div style='float:left; margin-left:2%;'>\n<table>\n";
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
		$rsGlassTable .= "<tr>\n";
		$rsGlassTable .= "		<td>$row[OxideFormula]</td>\n";
		$rsGlassTable .= "		<td><input type='text' name='$row[OxideFormula]' size='4' /></td>\n";
		$rsGlassTable .= "<tr>\n";
	}
	$rsGlassTable .= "</table></div>\n";
	mysql_free_result($rsGlass);
	
	echo "$rsFluxTable";
	echo "$rsFlowTable";
	echo "$rsGlassTable";
	
	mysql_close($conn);	
}



// Creates Assoc array of IngrID => IngrFormula			For quickly adding Oxide IDS to M2M table
include('../includes/DYLMG_createOxideArray.php');

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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DYLMG - Add Ingredient</title>
<link href="../stylesheets/sandbox_doyoulike.css" rel="stylesheet" type="text/css" />
<!--<link href="../includes/web20_table.css" rel="stylesheet" type="text/css" /><!--CSS for pretty tables-->
</head>

<body>
<div class="master-frame">
    <div class="header"><img src="../images/DYLMG_title.png" style="width:35%" /></div>
    <div class="sidebar">
        <?php
        include('../includes/sidebar.php');
        ?>
    </div>
    <div class="content-border">
<!-- __________________________________________ Content Start Here_______________________________________-->

<form action='<?php echo $_SERVER["PHP_SELF"]?>' method="post" >
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

<?php
createOxidesTable();
?>
<div style="clear:both">
<input type='submit' name="submit" value="Submit Ingr" />
</div>
</form>

<!-- __________________________________________ Content Ends Here________________________________________-->

    </div>
</div>
</body>
</html>