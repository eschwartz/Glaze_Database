<?php
include('../includes/DYLMG_SuperHTML.php');
?>
<?php
function createGlazesTable() {
	$glazesTable = "";
	
	////////////////////////////// SELECT Glaze Info ////////////////////////////////////////
	$sql = "SELECT GlazeID, GlazeName, GlazeTemp, GlazeColor, GlazeSurface, GlazeDatePosted FROM Glazes";	
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");
	$result = mysql_query($sql,$conn);
	//////////////////////////////////////////////////////////////////////////////////
	
	$glazesTable .= "<table>\n
		<colgroup style='font-weight:bold;' width='50%'></colgroup>\n
		<tr>\n
			<th>Glaze Name</th>\n
			<th>Cone Temp</th>\n
			<th>Color</th>\n
			<th>Surface</th>\n
			<th>Date Posted</th>\n
		</tr>\n";
	
	// Field Values
	while ($row = mysql_fetch_assoc($result)) {
		$glazeID = $row['GlazeID'];
		$glazesTable .=  "<tr>\n";
		foreach ($row as $field=>$value) {
			if ($field != 'GlazeID') {					// Do not print out GlazeID column
				if ($field == 'GlazeName') {			// GlazeName column contains link to glaze details
					$glazesTable .= "<td><a href=\"DYLMG_glazeDetails.php?glazeID=$glazeID\">$value</a></td>\n";
				}
				else {
					$glazestable .= "<td>$value</td>\n";
				}
			}
		}
		$glazesTable .=  "<tr>\n";
	}
	
	return $glazesTable;
}
?>

<?php
$s = new DYLMG_SuperHTML('Glaze Search');
$s->buildTop();

$glazesTable = createGlazesTable();
$s->addText($glazesTable);

$s->buildBottom();
print $s->getPage();
?>