<?php 
	
$res = json_decode(stripslashes($_POST['data']), true); 	// strpslashes requires to remove \" (magic quotes is on)
$res[php_message] = "I am PHP"; 

echo json_encode($res); 
?>