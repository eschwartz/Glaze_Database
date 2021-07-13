<?php
include('../includes/DYLMG_SuperHTML.php');
?>
<?php
if ($_POST['submit']) {
	if ($_FILES["importFile"]["error"] > 0)
	  {
	  echo "Error: " . $_FILES["importFile"]["error"] . "<br />";
	  }
	else
	  {
	  echo "Upload: " . $_FILES["importFile"]["name"] . "<br />";
	  echo "Type: " . $_FILES["importFile"]["type"] . "<br />";
	  echo "Size: " . ($_FILES["importFile"]["size"] / 1024) . " Kb<br />";
	  echo "Stored in: " . $_FILES["importFile"]["tmp_name"] . "<br />";
	  
	  move_uploaded_file($_FILES["importFile"]["tmp_name"],"files/" . $_FILES["importFile"]["name"]);
      echo "Moved to: " . "files/" . $_FILES["importFile"]["name"] . "<br><br>";
	  
	  $fileLocation = "files/" . $_FILES["importFile"]["name"];
	  createTempTable($fileLocation);
	  }
}

function isEmptyTable($tbl) {
	// Returns TRUE if the table is empty
	$sql = "SELECT COUNT(*) AS RowCount FROM $tbl";
	$rs = quickQuery($sql);
	while ($row = mysql_fetch_array($rs)) {
		if ($row[RowCount] < 1) { 
			return true;
		}
		else { 
			return false;
		}
	}
}
  
function createTempTable($fileLocation) {
	$file = fopen($fileLocation, "r+") or exit("Unable to open file!");		// $file is an array, each element is a line
	
	// Will create a TEMP table to hold entire CSV file
	// Then read from the TEMPT table to separate the date into appropriate tables
	// Can use same code from addIngr.php
	
	//$tempFields = array_shift($file);		// DOESN"T WORK?? Pulls the Header row , to specify sql fields. (Header should match  SQL fields...)
	$i=0;
	$tempFields = "";
	$tempValues = "";
	while (!feof($file)) {
		$line = fgets($file);
		if ($line != "") {
			if ($i == 0) { 
				$tempFields .= "($line)"; 		// First line set as fields
			}
			else {
				// Adds quotes around IngrName for sql to process
				$splitLine = explode(",",$line);
				$splitLine[1] = "\"" . $splitLine[1] . "\""	;		// $splitLine[field]. 0=IngredientImportID, 1=IngrName
				$line = implode(",", $splitLine);
				
				$tempValues .= "($line),";
			}
		}
		$i++;
	}
	$tempValues = substr($tempValues,0,-1);		// Removes last "," from string
	
	///////////////////// Create temp_ingrImport Table ///////////////////
	/// NOTE: This table should be made from the oxidearry() function, so it it consistent....
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	$sql = 	  "CREATE TABLE temp_ingrImport\n"
			. "("
			. "IngredientImportID int(5), IngredientName varchar(55), IngredientMW decimal(10,6), IngredientLOI decimal(10,5),	IngredientTE decimal(10,6),\n"
			. "BaO decimal(10,4), CaO decimal(10,4), Li2O decimal(10,4), MgO decimal(10,4), K2O decimal(10,4), Na2O decimal(10,4), ZnO decimal(10,4), SrO decimal(10,4), F decimal(10,4), P2O5 decimal(10,4), PbO decimal(10,4), TiO2 decimal(10,4), Al2O3 decimal(10,4), B2O3 decimal(10,4), SiO2 decimal(10,4), CoO decimal(10,4), Cr2O3 decimal(10,4), Fe2O3 decimal(10,4), FeO decimal(10,4), MnO decimal(10,4), MnO2 decimal(10,4), NiO decimal(10,4), ZrO2 decimal(10,4)"
			. ")";
	
	mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error());
	/////////////////////////////////////////////////////////////////////////////
	
	///////////////////// SQL to insert CSV data into TEMP table ///////////////////
	$sql = "INSERT INTO temp_ingrImport $tempFields\n"
			. "VALUES $tempValues";
	mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	////////////////////////////////////////////////////////////////////////////////
	
	/////// Delete Duplicate Values @ Ingr Name /////
	
	// Find records with dup. names
	$sql = "SELECT temp_ingrImport.IngredientImportID, temp_ingrImport.IngredientName\n"
			. "FROM temp_ingrImport\n"
			. "INNER JOIN Ingredients\n"
			. "ON temp_ingrImport.IngredientName = Ingredients.IngredientName";
	$rs = mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	$sql_deleteDups = "";
	while($row = mysql_fetch_assoc($rs)) {
		$sql_deleteDups .= "IngredientName = \"$row[IngredientName]\" OR ";
	}
	$sql_deleteDups = substr($sql_deleteDups,0,-4);	 // Removes final " OR "

	if(trim($sql_deleteDups) != "") {
		// Delete rows with matching IngrName value
		$sql = "DELETE FROM temp_ingrImport\n"
				. "WHERE $sql_deleteDups";						//IngredientName = xxx OR IngredientName = ....
	}
			
	mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");	
	
	// Need to end the script if no more records
	if (isEmptyTable("temp_ingrImport")) {
		exit("All rows are duplicates. Unable to import any ingredients");
		dropTempTbl();
	}
	
	transferTempData();
}



function transferTempData() {
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	
	/////////////////////////// Read Ingr Info from TEMP table /////////////////////////////
	// first we will read ingr info from temp table
	$sql = "SELECT IngredientImportID, IngredientName, IngredientMW, IngredientTE, IngredientLOI\n"
			. "FROM temp_ingrImport";
	$rs = mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	
	
	/////////////////////////// Insert Ingr info into Ingredients table /////////////////////////////
	$conn_insertIngr = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");		// need new conn for mysql_affected_rows
	mysql_select_db("earthcr1_DYLMG");

	$sql_insertIngr = "";
	while ($row = mysql_fetch_array($rs)) {
		$sql_insertIngr .= "($row[IngredientImportID],\"$row[IngredientName]\",$row[IngredientMW],$row[IngredientTE],$row[IngredientLOI]),";
	}
	$sql_insertIngr = substr($sql_insertIngr,0,-1);		// Removes last "," from string

	$sql = "INSERT into Ingredients (IngredientImportID, IngredientName, IngredientMW, IngredientTE, IngredientLOI)\n"
			. "VALUES $sql_insertIngr";
	mysql_query($sql, $conn_insertIngr) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	
	/////////////////////////// Query to retrieve $IngrID_new /////////////////////////////
	$newIngrCount = mysql_affected_rows($conn_insertIngr);
	$sql = 	"SELECT IngredientImportID, IngredientID \n"
			. "FROM Ingredients \n"
			. "ORDER BY IngredientID DESC LIMIT $newIngrCount";		// Selects the last IngredientID
	$rs = mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL is: $sql");
	
	// Creates Array convert the importID to the read IngrID
	// $getIngrID[IngredientImportID] = IngredientID
	$getIngrID = array();
	while ($row = mysql_fetch_array($rs)) { 
		$getIngrID[$row[IngredientImportID]] = $row[IngredientID]; 
	}
	mysql_free_result($rs);


	////////////////////////// Select data in Oxide fields from TEMP table ///////////////////////////////
	$oxideArray = createOxideArray();
	$sql_oxideFields = "";
	foreach ($oxideArray as $formula => $id) {
		$sql_oxideFields .= "$formula,";
	}
	$sql_oxideFields = substr($sql_oxideFields,0,-1);

	$sql = "SELECT IngredientImportID, $sql_oxideFields\n"
			. "FROM temp_ingrImport";
	$rs = mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL is: $sql");
	
	// Create a Multi-Dim Arry:
	// As $IngredientOxide[$IngredientID][$OxideID] = OxideAmt;
	$oxideArray = createIDValue();
	$IngredientOxide = array();
	while ($row = mysql_fetch_assoc($rs)) {
		$IngredientImportID = $row[IngredientImportID];
		$IngredientID = $getIngrID[$IngredientImportID];		// Returns the actual Ingredient ID for the Ingredient Row
		foreach($row as $formula=>$amt) {
			if ($amt != 0 && $amt != "" && array_key_exists($formula,$oxideArray)) {
				$OxideID = $oxideArray[$formula];					// Returns OxideID corresponding to formula heading
				$IngredientOxide[$IngredientID][$OxideID] = $amt;
			}
		}
	}
	

	////// Creating INSERT VALUES sql /////////
	////// For IngrOx. Table //////////////////
	
	// Cycles through multi-dim. table
	$sql_IngrOxValues = "";
	foreach ($IngredientOxide as $ingrID => $arry) {
		foreach ($arry as $oxID => $amt) {
			$sql_IngrOxValues .= "($ingrID, $oxID, $amt),";
		}
	}
	$sql_IngrOxValues = substr($sql_IngrOxValues,0,-1);
	
	$sql = "INSERT INTO IngredientOxides (IngredientID, OxideID, OxideAmt)\n"
			. "VALUES $sql_IngrOxValues";
	mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL is: $sql");

	dropTempTbl();
}
function dropTempTbl() {
	///////// DROP temp table ///////////
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	$sql = "DROP TABLE temp_ingrImport";
	mysql_query($sql, $conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL is: $sql");
}

	

?>
<?php
$s = new DYLMG_SuperHTML('Import Batch Ingredients');
$s->buildTop();

$s->h1("Import Ingredients");
$s->h3("Need security on this page before going live!");

$s->startForm($_SERVER[PHP_SELF], 'post', 'multipart/form-data');	// startForm($action, $method, $enctype='application/x-www-form-urlencoded', $parm="") 
$s->h4('File:');
$s->addText("<input type='file' name='importFile'  /><br />");
$s->addText("<input type='submit' name='submit' />");
$s->endForm();

$s->buildBottom();

print $s->getPage();
?>