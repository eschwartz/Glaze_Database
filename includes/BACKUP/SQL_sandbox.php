<?php
// Creates JS Assoc Array "ingrArry"
// Creates PHP $postVars array on non-ingredient variables
include('DYLMG_postGlaze_passonvars.php');
?>

<?php
// Establish connection to mySQL database
$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

$sql = "SELECT * FROM Glazes";
$result = mysql_query($sql,$conn);

print "<table border=1>"
print "<tr>\n";
while ($field = mysql_fetch_field($result)) {
	print "		<th>$field->name</th>\n";
}
print "</tr>\n\n";
?>