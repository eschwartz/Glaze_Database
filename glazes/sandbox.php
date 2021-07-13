<?php
$res = json_decode($_REQUEST['test'], true);
$res["php_message"] = "I am always PHP";
echo json_encode($res);
?>