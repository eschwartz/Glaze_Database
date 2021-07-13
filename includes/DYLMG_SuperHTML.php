<?php
include('SuperHTML.php');
include('sidebar.php');

// createOxideArrays is included in quickArrays
include_once('DYLMG_quickArrays.php');
include_once('quickQuery.php');

// Get user info for use in header (login, etc.)
require_once ('phpUserClass/access.class.php');
$user = new flexibleAccess($conn);/*
if $user->is_loaded() {
	// For logged in user: show user info in header
}
else {
	// For non-loaded user: show login in header
}*/

// DYLMG_SuperHTML class definition
class DYLMG_SuperHTML extends SuperHTML {

	var $usrLinks; 		// Header links for "Login/Register" or "Logout/My Account"
	
	function __construct($tTitle){
		//constructor
		
		
		// sets title
		$title = 'DYLMG - ' . $tTitle;
		$this->setTitle($title);
		
		// sets default css code
		$this->addCSSLink('/includes/CSS/DYLMG.css');
		$this->addCSSLink('/includes/CSS/web20_table.css');
		$this->addCSSLink('/includes/CSS/unityTable.css');
	} // end constructor

	function buildTop(){
		$tempHTML = <<<HERE
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
$this->head
<meta http-equiv="content-type" content="text/xml; charset=utf-8" />
<title>$this->title</title>
</head>
<body $this->bodyParams>

	<!-- Banner Header -->
	<div id="loginContainer">
		<div id="quickLogin">
			<label for="quickLogin">Username:</label><br>
			<input type="text" name="quickLogin"/><br>
			<label for="quickLogin">Password:</label><br>
			<input type="text" name="quickPass" />
		</div>
	</div>

    <div id="header">
    	<img id='banner' src="../images/header_splatter.png">
		<img id='logo' src="../images/DYLMG_title.png" >
        
    </div><!-- end header div-->
	
	<div id='navbar'>
		<button onclick="window.location='/usr/myAccount.php'" class='navButton'>My Account</button>
		<button onclick="window.location='/glazes/DYLMG_glazeSearch.php'" class='navButton'>Glaze Search</button>
		<button onclick="window.location='/glazes/glazeView.php'" class='navButton' >Submit Glaze</button>
		<button onclick="window.location='/glazes/glazeView.php?glazeID=1'" class='navButton' >Sample Glaze</button>
    </div>

<div id='master-container'>

<!-- __________________________________________ Content Start Here_______________________________________-->
HERE;

		$this->addText($tempHTML);
	} // end buildTop;
    




	function buildBottom(){
		//builds the bottom of a generic web page
		$temp = <<<HERE
<!-- __________________________________________ Content Ends Here________________________________________-->
	</div><!-- End master-container div -->
</body>
</html>
HERE;
		$this->addText($temp);
	} // end buildBottom;

}	// end DYLMG_SuperHTML class definition
