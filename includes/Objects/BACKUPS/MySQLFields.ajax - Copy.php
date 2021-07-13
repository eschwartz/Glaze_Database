<?php
/** 
 * AJAX File to be used with MySQLFields
 * javascript object definition.
 *
 * Returns an array of field names for 
 * a given table, along with their 
 * respective data types.
 *
*/

include_once('../DYLMG_quickArrays.php');

$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

if(is_ajax()) {	
	$table = strip_tags(stripslashes($_POST['table'])); 	// strpslashes requires to remove \" (magic quotes is on)
	$mySQLFields = json_encode(getSQLFields($table));
	echo $mySQLFields;
} elseif ($_GET['test']=='yes') {
	$table = "Glazes";
	$mySQLFields = json_encode(getSQLFields($table));
	echo "<pre>";
	print_r(getSQLFields($table));
	echo "</pre>";
} else {
	// Kicks out non-AJAX
	ob_start();	// needed for header redirect
	$url = "/";			
	header('Location: ' . $url);		
	ob_flush();
}

function getSQLFields($table) {
	global $conn;
	
	$mySQLFields = array();
	
	$sql = "SHOW columns FROM $table";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	while($row = mysql_fetch_assoc($rs)) {
		array_push($mySQLFields, array($row[Field], $row[Type]) );
	}
	
	return $mySQLFields;	
} // end of getSQLFields

?>