<?php
include_once('../includes/DYLMG_SuperHTML.php');
include_once('../includes/DYLMG_quickArrays.php');
?>
<?php
$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

$s = new DYLMG_SuperHTML('My Account');

require_once ('../includes/phpUserClass/access.class.php');
$user = new flexibleAccess();

$user->reqLogin($_SERVER['PHP_SELF']);		// sends the user back to login page if not logged in already
$userID = $user->getUserID();



?>
<?php
// JAVASCRIPT
$js = <<<HERE

// Define global variables
var loadedUsr = $userID;				// from php user class

$(document).ready(function() {

	// Load image gallery of user glazes
	loadUsrGlazes();	
	
	// EVENT: new glaze button
	$('#btn_newGlaze').button().click(function() {
		window.location = '/glazes/DYLMG_postGlaze.php?glazeID=new';
	});
	
});	// end of document.ready

function loadUsrGlazes() {
// AJAX to load hover gallery of user glazes
	var filters = $.toJSON({
		'GlazeVars.VarAuthor': loadedUsr,
		'GlazeVars.VarDefault': 1,
		'GlazeVars.VarName': '',		// Empty property -> AJAX will return this field
		'GlazeVars.VarDescr': '',
		'GlazeVars.VarColor': '',
		'GlazeVars.VarSurface': '',
		'Images.ImageDescr': '',
		'Users.UserLogin': ''
	});
	var ajaxFile = '/includes/AJAX/runGlazeSearch.php';
	$.post(ajaxFile, {'filters': filters, 'pageNumber':1, 'pageResults':20}, function(res) {
		var usrGlazes = $.evalJSON(res);
		
		// Empty gallery
		$('#usrGlazeGallery').empty();
		
		// Add each record to user gallery
		for (var record in usrGlazes) {
			var glaze = usrGlazes[record];

			// Sets "No Image"
			var thumbSrc = new String();
			if (glaze['ImageSrc'] != null) {			// Only add image if it exists
				thumbSrc = glaze['ImageSrc'].replace("images/","images/thumb_");
			}
			else {
				thumbSrc = "/images/no_image.gif";
			}

			
			// Sets description
			var descr = buildHoverDescr(glaze);
			
			var li = "<li><a href='/glazes/DYLMG_postGlaze.php?glazeID="+glaze['GlazeID']+"' class='link'><img src='"+thumbSrc+"' alt='"+descr+"' /></a></li>";
			$('#usrGlazeGallery').append(li);
		}
		hoverGallery();
	});	// end of AJAX callback
	
}	// end of loadUsrGlaze

HERE;
// END OF JAVASCRIPT
?>
<?php
$s->addJSLink('/includes/jsFunctions.js');

// JQUERY resources
$s->addCSSLink('/includes/jquery_ui/css/start/jquery-ui-1.8.10.custom.css');	// Style for all jquery ui elements
$s->addCSSLink('/includes/hoverGallery/hoverStyle.css');						// Style for hovergallery
$s->addJSLink('/includes/jquery-1.5.min.js');									// Core jquery file
$s->addJSLink('/includes/jquery_ui/js/jquery-ui-1.8.10.custom.min.js');			// Jquery UI file
$s->addJSLink('/includes/jquery.json-2.2.min.js');								// handles JSON conversion (for AJAX)
$s->addJSLink('/includes/hoverGallery/hoverGallery.js');						// Hovergallery script 
$s->addJS($js);

$s->buildTop();


// HTML
$body = <<<HERE

<h1>My Account</h1>
<a href='/usr/login.php?logout=1'>Logout</a><br />
<h3>Your Glazes</h3>
<div id='usrGlazes'>
<ul class='hoverGallery' id='usrGlazeGallery' style='width:360px'>
</ul>
</div>

<div style='clear:both;'>
<button id="btn_newGlaze"> Create a new glaze </button>
</div>
HERE;
// END OF HTML

$s->addText($body);
$s->buildBottom();
print $s->getPage();
?>