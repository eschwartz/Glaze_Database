<?php
include('../includes/DYLMG_SuperHTML.php');
?>

<?php
$s = new DYLMG_SuperHTML('Ingredient Details');
$s->buildTop();

$s->h3('What goes on this page?');
$s->buildList(array("What will we do when we have a huge list of ingredients?",
			 		"Also, think for the addIngr.php: can't really use a drop-down for 1000+ items"));

$s->buildBottom();

print $s->getPage();

?>