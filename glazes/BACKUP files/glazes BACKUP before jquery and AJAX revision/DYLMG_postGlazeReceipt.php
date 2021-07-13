<?php
ob_start();	// needed for header redirect
include('../includes/quickQuery.php');
include('../includes/DYLMG_createOxideArray.php');
?>

<?php
insertGlaze();
function insertGlaze() {
	$glazeName = $_POST['glaze_name'];
	
	// Creates ingrIDAmt[ID] = amt
	// from POST string lists
	$ingrIDList = explode(",",$_POST['ingrIDList']);
	$ingrAmtList = explode(",",$_POST['ingrAmtList']);
	$index = 0;		// So amounts can follow along with IDs in foreach loop
	$ingrIDAmt = array();
	foreach ($ingrIDList as $ingrID) {
		$ingrIDAmt[$ingrID] = $ingrAmtList[$index];
		$index++;
	}
	
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	// Inserts Glaze into Glazes table
	$sql = "INSERT INTO Glazes (GlazeName)\n"
			. "VALUES ('$glazeName')";
	mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	
	$glazeID = mysql_insert_id(); 	// Retreives the auto ID for the inserted glaze (Cool!)
	
	$sql_glazeIngredients = "";
	foreach ($ingrIDAmt as $ingrID => $ingrAmt) {
		$sql_glazeIngredients .= "($glazeID, $ingrID, $ingrAmt),";
	}
	$sql_glazeIngredients = substr($sql_glazeIngredients,0,-1);		// Removes last "," from string
	
	$sql = "INSERT INTO GlazeIngredients (GlazeID, IngredientID, IngredientAmt)\n"
			. "VALUES $sql_glazeIngredients";
			
	mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	
	$url_glazeID = urlencode($glazeID);
	header('Location: DYLMG_glazeDetails.php?glazeID=' . $url_glazeID ) ;

}
ob_flush();	// needed for header redirect
?>
