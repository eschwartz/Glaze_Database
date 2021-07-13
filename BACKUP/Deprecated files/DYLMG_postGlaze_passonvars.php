<?php
// Creates JS Assoc Array "ingrArry"
// Creates PHP $postVars array on non-ingredient variables
echo "<script type='text/javascript'>\n";
echo "var ingrArry = {";
$postVars = array();
foreach ($_POST as $key => $value) {
	if (strstr($key, "ingrName") && empty($value) == false) {
		echo "'$value': ";
	}
	else if (strstr($key, "ingrAmt") && empty($value) == false) {
		echo "'$value',";
	}
	else if (empty($value) == false) {
		$postVars[$key] = $value;		// Creates PHP array on non-ingredient variables
	}
}
echo "};\n";
echo "</script>";
	
	// For testing 
	/*
	foreach ($postVars as $key => $value) {
		echo "$key is $value";
	}
	*/
?>

<script type="text/javascript">
/* For testing ingredients array
var teststring = "";
for (ingrName in ingrArry) {
	teststring += "The Amt of " + ingrName + " is " + ingrArry[ingrName] + ". \n";
}
alert(teststring);
*/
</script>
