<?php 
// runSearch.php
// Called via AJAX from glazeSearch.php
// Searches database with filters from javascript
// Returns array of results

include_once('../DYLMG_quickArrays.php'); 
?>
<?php
$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

// Check for AJAX Request, or boot user
if(is_ajax()) {	
	$filters = json_decode(stripslashes($_POST['filters']), true); 	// strpslashes requires to remove \" (magic quotes is on)
	$pageNumber = json_decode(stripslashes($_POST['pageNumber']), false); 
	$pageResults = json_decode(stripslashes($_POST['pageResults']), false); 

	$res = runSearch($filters, $pageNumber, $pageResults);					
	echo json_encode($res);
}
elseif($_GET['test'] == 'yes') {
	$filters = array('GlazeVars.VarAuthor' =>'1',
					'GlazeVars.VarDefault' => '1');
	$pageNumber = 1;
	$pageResults = 10;
	$res = runSearch($filters, $pageNumber, $pageResults);	
	printArray($res);
}
else {
	// Kicks out non-AJAX
	ob_start();	// needed for header redirect
	$url = "/usr/myAccount.php";			
	header('Location: ' . $url);		
	ob_flush();
}


function runSearch($filters, $pageNumber, $pageResults) {
	global $conn;
	
	// Create array of "WHERE FieldName = 'Value'"
	$criteria = array();
	foreach ($filters as $field => $value) {
		// For Strings
		if(!isInt($value) && !isFloat($value)) {
			$line = "$field LIKE '%$value%'";
		}
		// For numbers
		else {
			$line = "$field = $value";
		}
		
		if ($value != "") {
			// Don't add empty criteria
			array_push($criteria, $line);
		}
	}
	
	// Check for empty criteria list
	if (count($criteria) > 0) {
		$criteriaList = "WHERE " . join("\n AND ", $criteria). "\n";
	}
	else {
		$criteriaList = "";
	}
	
	$offset = ($pageNumber-1)*$pageResults;				// Sets which record to start
	$fieldList = join(",",array_keys($filters));
	$sql = 	  "SELECT DISTINCT GlazeVars.GlazeID, GlazeVars.VarID, Images.ImageSrc, Users.UserID, $fieldList  \n"
			. "FROM GlazeVars \n"
			. "LEFT JOIN (VarImages INNER JOIN Images ON VarImages.ImageID = Images.ImageID) \n" 
			. "ON GlazeVars.VarID = VarImages.VarID \n"
			. "LEFT JOIN Users ON GlazeVars.VarAuthor = Users.UserID \n"
			. "$criteriaList"
			. "GROUP BY GlazeVars.VarID \n"					// So it only returns 1 value from Images table
			. "LIMIT $offset, $pageResults";
	$rs = mysql_query($sql,$conn) or die("Could not execute Glazes query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
		//echo "<h3>SQL is </h3>".str_replace("\n","<br>\n",$sql);
	// Returns Search results as array:
	// $searchRes = ((field=value, field=>value,...),(field=>value....)...)
	$searchRes = array();
	while ($row = mysql_fetch_assoc($rs)) {
		foreach($row as $field => $value) {
			$record[$field] = $value;
		}
		array_push($searchRes, $record);
	}
		
	return $searchRes;
}
?>


