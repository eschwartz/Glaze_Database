<?php
include('../includes/DYLMG_quickArrays.php');
?>

<?php
$glazeID = $_GET['glazeID'];
$glazeInfo = createGlazeInfoArry();												// Creates array: $glazeInfo[field] = value
$ingrIDAmt = getRecipe($glazeID);											// Creates array: $ingrIDAmt[ingrID] = ingrAmt
$ingrIDName = createIDValue('IngredientID', 'IngredientName', 'Ingredients');	// Creates array: $ingrIDName[ingrID] = ingrName

function ingrTable() {
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
}

function createGlazeInfoArry() {
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
}

function glazeInfoTable() {
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
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DYLMG - Glaze Details</title>
<link href="../stylesheets/sandbox_doyoulike.css" rel="stylesheet" type="text/css" />
<link href="../includes/web20_table.css" rel="stylesheet" type="text/css" />
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
<h1><?=$glazeInfo['GlazeName']?></h1>

<!-- __________________________________________ Content Ends Here________________________________________-->
<h3>Glaze Info</h3>
<?=glazeInfoTable()?>

<h3>Ingredient</h3>
<?=ingrTable()?>

<h3>Unity Formula</h3>
<?=createUnityTable($glazeID)?>



<!-- __________________________________________ Content Ends Here________________________________________-->

    </div>
</div>
</body>
</html>