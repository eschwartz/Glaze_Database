<?php
include('../includes/DYLMG_SuperHTML.php');
?>
<?php
////////////////////////////// Ingr Details Query /////////////////////////
function createIngrTable() {
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	$sql = 	"SELECT Ingredients.IngredientID, Ingredients.IngredientName, Ingredients.IngredientMW, "
			. "Oxides.OxideID, Oxides.OxideFormula, IngredientOxides.OxideAmt \n"
			. "FROM (IngredientOxides RIGHT JOIN Ingredients ON IngredientOxides.IngredientID = Ingredients.IngredientID) "
			. "LEFT JOIN Oxides ON IngredientOxides.OxideID = Oxides.OxideID \n";
	//echo quickQueryTable($sql);
	$rs = mysql_query($sql,$conn) or die("Could not execute query:" . mysql_error());
	
	
	$ingrTable = "<table>\n";
	while ($row = mysql_fetch_assoc($rs)) {
		if ($row[IngredientID] != $priorIngr) {										// We are on a new ingredient - Print Ingr Name, info
			$ingrTable .= "</tbody>"
						. "<thead>\n"
						. "	<tr>\n"
						. "		<th onclick=\"showHide('$row[IngredientID]')\">$row[IngredientName]</a></td>\n"
						. "	</tr>\n"
						. "</thead>\n"
						. "<tbody id='$row[IngredientID]' style=\"display:none\">\n"
						. "	<tr>\n"
						. "		<td>Molecular Mass</td>\n"
						. "		<td>$row[IngredientMW]</td>\n"
						. "	</tr>\n";
		}
		$ingrTable .= "	<tr>\n"
					. "		<td>$row[OxideFormula]</td>\n"
					. "		<td>$row[OxideAmt]</td>\n"
					. "	</tr>\n";
		$priorIngr = "$row[IngredientID]";											// Sets priorIngr just before restarting loop.	
	}
	return $ingrTable;
}
///////////////////////////////////////////////////////////////////////////
?>
<?php
$s = new DYLMG_SuperHTML('Ingredient List');
$s->addJS(<<<HERE
// Adapted from http://www.coderanch.com/t/119774/HTML-JavaScript/expand-collapse-multiple-rows-table
// See file in sample code folder
function showHide(id){
	var tbody = document.getElementById(id);
	var old = tbody.style.display;
	tbody.style.display = (old == "none"?"":"none");
}
HERE
);	// end of addJS

$s->addHead(<<<HERE
<style type="text/css">
	th{
	text-align: left;
	cursor: pointer;
	}
	table tbody tr td{
	padding-left: 15px;
	}
</style>
HERE
); // end of addHead

$s->buildTop();
$s->h1('Ingredient Details');
$s->addText(createIngrTable());
$s->buildBottom();

print $s->getPage();
?>