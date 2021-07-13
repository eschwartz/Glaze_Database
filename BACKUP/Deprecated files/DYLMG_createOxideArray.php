<?php
function createOxideArray() {
// Returns $oxideList[oxID]=oxFormula
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	$sql = "SELECT OxideID, OxideFormula \n"
			. "FROM Oxides \n";
	$rs = mysql_query($sql, $conn);
	while ($row = mysql_fetch_assoc($rs)) {
		$oxideList[$row['OxideFormula']] = $row['OxideID'];
	}
	
	mysql_free_result($rs);
	mysql_close($conn);
	
	return $oxideList;
}

function createOxideWgtArray() {
// Returns $oxideWeight[OxideID] = Oxide Weight	
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	$sql = "SELECT OxideID, OxideWgt\n"
			. "FROM Oxides\n";
	$rs = mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL is: $sql");
	
	while ($row = mysql_fetch_array($rs)) {
		$oxideWeight[$row[OxideID]] = $row[OxideWgt];
	}
	
	mysql_free_result($rs);
	mysql_close($conn);
	
	return $oxideWeight;
}

function getAllMolWeights() {
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	
	$sql 	= "SELECT IngredientID, OxideID, OxideAmt\n"
			. "FROM IngredientOxides";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL is: $sql");
	
	// Create array $IngrOxides[IngredientID][OxideID] = OxideAmt
	$IngrOxide = array();
	while($row = mysql_fetch_array($rs)) {
		$ingredientID = $row[IngredientID];
		$oxideID = $row[OxideID];
		$oxideAmt = $row[OxideAmt];
		$IngrOxide[$ingredientID][$oxideID] = $oxideAmt;
	}

	
	// Returns $oxideWeight[OxideID] = Oxide Weight	
	$oxideWeight = createOxideWgtArray();
	
	// As $IngrWeight[IngrID] = Ingredient Molecular Weight
	$IngrWeight = array();
	foreach ($IngrOxide as $ingrID => $arry) {
		foreach($arry as $oxID => $oxAmt) {
			$IngrWeight[$ingrID] += $oxAmt * $oxideWeight[$oxID];
		}
	}
	
	return $IngrWeight;
}
?>