<?php
function getSidebar() {
	$sidebar = <<<HERE
		<h3>Some stuff</h3>
		<a href='http://DYLMG.earthcreaturespottery.com'><b>List of DYLMG Files</b></a><br />
		Ingredients
		<ul>
			<li><a href="/ingredients/DYLMG_addIngr.php">Add ingredient</a></li>
			<li><a href="/ingredients/DYLMG_ingrList.php">List of ingredients</a></li>
			<li><a href="/ingredients/DYLMG_importIngr.php">Import ingredients</a></li>
		</ul>
		Glazes
		<ul>
			<li><a href="/glazes/DYLMG_postGlaze.php">Add a Glaze</a></li>
			<li><a href="/glazes/DYLMG_glazeSearch.php">List of Glazes</a></li>
		</ul>
		Users
		<ul>
			<li><a href="/usr/createUser.php">Create User</a></li>
			<li><a href="/usr/login.php">Login</a></li>
		</ul>
HERE;

	return $sidebar;
}
?>