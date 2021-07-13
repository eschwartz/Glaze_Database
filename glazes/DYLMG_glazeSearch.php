<?php
include('../includes/DYLMG_SuperHTML.php');
?>
<?php
require_once('../includes/phpUserClass/access.class.php');
$user = new flexibleAccess();
$user->reqLogin($_SERVER['PHP_SELF']);

// Javascript starts here
$js = <<<HERE
var filters = new Object();			// filters[FieldName] = value
var pageNumber = 1;
var pageResults = 10;

$(document).ready(function() {
	// Declare filters
	// So that all fields are send to SQL query
	filters = {
		'GlazeVars.VarAuthor': '',
		'GlazeVars.VarDefault': '',
		'GlazeVars.VarName': '',		// Empty property -> AJAX will return this field
		'GlazeVars.VarDescr': '',
		'GlazeVars.VarColor': '',
		'GlazeVars.VarSurface': '',
		'Images.ImageDescr': '',
		'Users.UserLogin': ''
	}
							   
	// Set filter events
	$('.filters').keyup(function() {
		var field = $(this).attr('name');
		var value = $(this).val();
		filters[field] = value;
	});
	
	// Create an unfiltered results table
	runSearch();
});	// end document.ready

function runSearch() {
	var ajaxFile = '/includes/AJAX/runGlazeSearch.php';
	var filtersJSON =  $.toJSON(filters);
	$('#loading').html("<img src='/images/loading.gif' />");
	$.post(ajaxFile, {'filters': filtersJSON, 'pageNumber':  pageNumber, 'pageResults': pageResults}, function (res) {
		$('#loading').html("");
		var searchRes = $.evalJSON(res);
		buildResTable(searchRes);		
	});
}

function buildResTable(searchRes) {
	// Resets gallery
	$('ul.hoverGallery').empty();
	
	for (var record in searchRes) {
		var glaze = searchRes[record];

		// Returns description to put in img alt
		var imgAlt = buildHoverDescr(glaze);
			
		// Sets thumb images, or "no-image.gif" if none exists
		var thumbSrc = new String();
		if (glaze['ImageSrc'] != null) {			// Only add image if it exists
			thumbSrc = glaze['ImageSrc'].replace("images/","images/thumb_");
		}
		else {
			thumbSrc = "/images/no_image.gif";
		}
		
		// Creates gallery list item
		var newLi = "<li><a href='/glazes/DYLMG_glazeDetails.php?glazeID="+glaze['GlazeID']+"' class='link'><img src='"+thumbSrc+"' alt='"+imgAlt+"' /></a></li>";
		$('ul.hoverGallery').append(newLi);
	}
	
	hoverGallery();
}

HERE;
// End of javascript
?>
<?php
$s = new DYLMG_SuperHTML('Glaze Search');
$s->addCSSLink('/includes/jquery_ui/css/start/jquery-ui-1.8.10.custom.css');	// Style for all jquery ui elements
$s->addCSSLink('/includes/hoverGallery/hoverStyle.css');						// Style for hovergallery
$s->addJSLink('/includes/jsFunctions.js');										// DYLMG javascript functions
$s->addJSLink('/includes/jquery-1.5.min.js');									// Core jquery file
$s->addJSLink('/includes/jquery_ui/js/jquery-ui-1.8.10.custom.min.js');			// Jquery UI file
$s->addJSLink('/includes/jquery.json-2.2.min.js');								// handles JSON conversion (for AJAX)
$s->addJSLink('/includes/hoverGallery/hoverGallery.js');						// Hovergallery script 
$s->addJS($js);
$s->buildTop();

$body = <<<HERE
<h1> Glaze Search</h1>
<h4>Glaze Name:</h4>
<input type='text' name='GlazeVars.VarName' class='filters' /><br />
<h4>Glaze Color:</h4>
<input type='text' name='GlazeVars.VarColor' class='filters' /><br />
<input type='button' onclick='runSearch()' value='Search' />

<h3>Results</h3>
<div id="loading"></div>
<ul class='hoverGallery'></ul>
HERE;

$s->addText($body);
$s->buildBottom();
print $s->getPage();
?>