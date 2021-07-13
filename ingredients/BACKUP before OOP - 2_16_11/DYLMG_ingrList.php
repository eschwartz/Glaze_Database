<?php
include('../includes/quickQuery.php');
include('../includes/DYLMG_createOxideArray.php');		// createOxideArray() returns assoc array of IngrID => IngrFormula	
?>

<?php
////////////////////////////// Ingr Details Query /////////////////////////
function createIngrTable() {
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	$sql = 	"SELECT Ingredients.IngredientID, Ingredients.IngredientName, Ingredients.IngredientLOI, Ingredients.IngredientTE, "
			. "Oxides.OxideID, Oxides.OxideFormula, IngredientOxides.OxideAmt \n"
			. "FROM (IngredientOxides RIGHT JOIN Ingredients ON IngredientOxides.IngredientID = Ingredients.IngredientID) "
			. "LEFT JOIN Oxides ON IngredientOxides.OxideID = Oxides.OxideID \n";
	//echo quickQueryTable($sql);
	$rs = mysql_query($sql,$conn) or die("Could not execute query:" . mysql_error());
	
	// Returns $IngrWeight[IngrID] = Ingredient Molecular Weight
	$IngrWeight = getAllMolWeights();
	
	$ingrTable = "<table>\n";
	while ($row = mysql_fetch_assoc($rs)) {
		if ($row[IngredientID] != $priorIngr) {										// We are on a new ingredient - Print Ingr Name, info
			$myIngrWeight = $IngrWeight[$row[IngredientID]];
			$ingrTable .= "</tbody>"
						. "<thead>\n"
						. "	<tr>\n"
						. "		<th onclick=\"showHide('$row[IngredientID]')\">$row[IngredientName]</a></td>\n"
						. "	</tr>\n"
						. "</thead>\n"
						. "<tbody id='$row[IngredientID]' style=\"display:none\">\n"
						. "	<tr>\n"
						. "		<td>Molecular Mass</td>\n"
						. "		<td>$myIngrWeight</td>\n"
						. "	</tr>\n"
						. "	<tr>\n"
						. "		<td>LOI</td>\n"
						. "		<td>$row[IngredientLOI]</td>\n"
						. " <tr>\n"
						. "	<tr>\n"
						. "		<td>Thermal Exp.</td>\n"
						. "		<td>$row[IngredientTE]</td>\n"
						. "	</tr>\n\n";
		}
		$ingrTable .= "	<tr>\n"
					. "		<td>$row[OxideFormula]</td>\n"
					. "		<td>$row[OxideAmt]</td>\n"
					. "	</tr>\n";
		$priorIngr = "$row[IngredientID]";											// Sets priorIngr just before restarting loop.	
	}
	echo $ingrTable;
}
///////////////////////////////////////////////////////////////////////////
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DYLMG - Ingr List</title>
<link href="../stylesheets/sandbox_doyoulike.css" rel="stylesheet" type="text/css" />
<link href="../includes/web20_table.css" rel="stylesheet" type="text/css" /><!--CSS for pretty tables-->

<script type="text/javascript">
// Adapted from http://www.coderanch.com/t/119774/HTML-JavaScript/expand-collapse-multiple-rows-table
// See file in sample code folder
function showHide(id){
	var tbody = document.getElementById(id);
	var old = tbody.style.display;
	tbody.style.display = (old == "none"?"":"none");
}
</script>
<style type="text/css">
	th{
	text-align: left;
	cursor: pointer;
	}
	table tbody tr td{
	padding-left: 15px;
	}
</style>


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
<h1>Ingredient Details</h1>
<?php
createIngrTable();
?>

<!-- __________________________________________ Content Ends Here________________________________________-->

    </div>
</div>
</body>
</html>