<?php
function quickQuery($sql) {
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");

	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	return $rs;
}

function quickQueryTable($sql) {
	$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
	mysql_select_db("earthcr1_DYLMG");

	$result = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>SQL is: $sql");
	
	$rsTable = "";

	// Header row = field names
	$rsTable .= "<table border=1>";
	$rsTable .= "<tr>\n";
	while ($field = mysql_fetch_field($result)) {
		$rsTable .= "		<th>$field->name</th>\n";
	}
	$rsTable .= "</tr>\n\n";
	
	// Field Values
	while ($row = mysql_fetch_assoc($result)) {
		$rsTable .= "<tr>\n";
		foreach ($row as $col=>$val) {
			$rsTable .= "		<td>$val</td>\n";
		}
		$rsTable .= "<tr>\n";
	}
	
	return $rsTable;
}

?>