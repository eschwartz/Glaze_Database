<?php
include_once('../includes/DYLMG_SuperHTML.php');
include_once('../includes/SuperJS.php');
?>
<?php

$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");




// Checks if new or existing glaze 
// $_GET['glazeID'] = '#' , 'new', or not set (same as 'new')
// 		If new: 		Require Login
// 						set author to loaded user
// 						JSONs are all empty (except 'author')
// 		If existing: 	Require Login by glaze author ONLY 
//					 	populate 'recipe' and 'glazeInfo' JSONs w/info from database

// Recipe and glaze info arrays will be populated with info from database.
// Then given to javascript as JSON objects
$recipe = array();
$glazeInfo = array();
$glazeImages = array();

require_once ('../includes/phpUserClass/access.class.php');
$user = new flexibleAccess($conn);

if (!isset($_GET['glazeID']) || $_GET['glazeID'] == 'new') {								// If new glaze
	global $recipe;
	global $glazeInfo;
	
	// Require login, or kick user
	$user->reqLogin($_SERVER['PHP_SELF']);
	
	// Set author to loaded user
	$glazeInfo[GlazeAuthor] = $user->getUserID();
	
	// Sets glazeID to new --> AJAX "saveGlaze.php" will INSERTS, instead of UPDATE
	$glazeInfo[GlazeID] = 'new';
}
elseif (is_numeric($_GET[glazeID]) === FALSE || (int)$_GET[glazeID] != $_GET[glazeID] ) {	// Invalid glazeID (not an integer)
	$user->kickUsr( NULL, '/usr/myAccount.php');											
}
else {																						// Check for existing glaze
	global $conn;
	global $recipe;
	global $glazeInfo;
	global $glazeImages;
	$glazeID = $_GET[glazeID];
	
	// Get glaze info from Glazes Table
	// and put in $glazeInfo[field name]
	$sql =    "SELECT * \n"
			. "FROM Glazes \n"
			. "WHERE GlazeID = $glazeID";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	if (!$rs || (mysql_numrows($rs) < 1)) {				// No matching glaze
		$user->kickUsr(NULL, '/usr/myAccount.php');		// kick user to myAccount.php
	}
	else {												// Matching glaze -> get data into arrays
		// Create array of glaze Info
		while ($row = mysql_fetch_assoc($rs)) {
			foreach ($row as $field => $value) {
				$glazeInfo[$field] = $value;			
			}											
		}
		
		// Create array of glaze images
		// $glazeImages (imageID => FieldName => Value)
		$sql = 	  "SELECT Images.ImageID, Images.ImageSrc, Images.ImageDescr, Images.ImageAuthor, Images.ImageDatePosted \n"
				. "FROM Images \n"
				. "RIGHT JOIN GlazeImages ON GlazeImages.ImageID = Images.ImageID \n"
				. "WHERE GlazeImages.GlazeID = $glazeID";
		$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
		while($row = mysql_fetch_assoc($rs)) {
			$imageID = $row['ImageID'];
			foreach ($row as $field => $value) {
				$glazeImages[$imageID][$field] = $value;
			}
		}
		
		// Create array of recipe amts
		$sql =    "SELECT GlazeIngredients.IngredientID, GlazeIngredients.IngredientAmt, Ingredients.IngredientName \n"
				. "FROM GlazeIngredients \n"
				. "LEFT JOIN Ingredients ON GlazeIngredients.IngredientID = Ingredients.IngredientID \n"
				. "WHERE GlazeID = $glazeID";
		$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
		while ($row = mysql_fetch_array($rs)) {
			$ingrID = $row[IngredientID];
			$ingrAmt = $row[IngredientAmt];
			$recipe[$ingrID] = $ingrAmt;
			$recipeIDName[$ingrID] = $row[IngredientName];
		}
	}

	// Checks that current user is the glaze author
	$user->reqUsrLogin($glazeInfo[GlazeAuthor], '/glazes/DYLMG_glazeDetails.php?glazeID=' . $glazeID);		// Kicks user if not author. 
	
} // end of (if existing glaze)


$sjs = new SuperJS();

// Creates js code for php arrays
$jsRecipe = $sjs->getPHPAssoc($recipe);
$jsRecipeIDName = $sjs->getPHPAssoc($recipeIDName);
$jsGlazeInfo = $sjs->getPHPAssoc($glazeInfo);
$jsGlazeImages = $sjs->get3dAssoc($glazeImages, 'glazeImages');


///////// END OF PHP //////////
///////// START OF JS /////////



$js = <<<HERE
// $(document).ready(function() {		// Waits to load javascript until HTML has loaded

// Get arrays from php
var recipe = $jsRecipe; 			// recipe[ingrID] = amt 
var recipeIDName = $jsRecipeIDName;	// recipeIDName[ingrID] = ingrName  : 	Needed for names of pre-existing recipe ingredients
var glazeInfo = $jsGlazeInfo;
$jsGlazeImages						// Declares 3d array: glazeImages (imageID => FieldName => value);

var ingrSelected = new Object;		// ingrSelected['ingrID'] = selected ingrID, ['ingrName'] = selected ingrName

// Sets CSS styles for suggest table
var sugestNormalCSS = {'background-color':'#EFEFEF',
				'font-weight':'normal',
				'color':'#880000',
				'cursor':'auto'}; 

var suggestHoverCSS = {'background-color':'#FFFF99',
				'font-weight':'bold',
				'color':'black',
				'cursor':'pointer', 'cursor':'hand'};

$(document).ready(function() {
	// Bind up/down/enter for search box
	$('#input_ingrFilter').keyup(function(evt) {
		var pressed = ((evt.which)||(evt.keyCode));
		
		if (pressed == '13') {	// Enter
			addIngr(ingrSelected['ingrID'], ingrSelected['ingrName']);
		}
		else if (pressed == '38') {	// Up
			targetRow = $('#'+ingrSelected['ingrID']).prev();
		}
		else if (pressed == '40') {	// Down
			targetRow = $('#'+ingrSelected['ingrID']).next();
		}
		else if ($(this).val() == "") {
			$('#tbl_suggest').remove();
			delete ingrSelected['ingrID'];
			delete ingrSelected['ingrName'];
		}
		else {
			filterIngrList();
			return;		// end function
		}
		var ingrID = targetRow.attr('id');
		var ingrName = targetRow.find('#ingrName').html();
		ingrSelected['ingrID'] = ingrID;
		ingrSelected['ingrName'] = ingrName;
		// sets hover css on this ingr (only)
		$('.ingrRow').css(sugestNormalCSS);
		targetRow.css(suggestHoverCSS);	
		evt.preventDefault();
		return false;
	});
	
	// Removes suggest table when out of search
	$('#input_ingrFilter').focusout(function() {
			$('#tbl_suggest').remove();
	});
});


// Onload actions
window.onload = function () {
	// Shows recipe and images for loaded glazes
	if (assocLength(recipe) > 0) {
		for (var ingrID in recipe) {
			buildRecipeTable(ingrID, recipeIDName[ingrID]);
		}
		buildGallery();
	}
};

function buildGallery() {
// Creates table of glaze-image thumbnails
	var tbl_gallery = document.getElementById('tbl_gallery');
	tbl_gallery.innerHTML = "";
	
	var i=0;
	var maxCols = 3;									// sets number of columns in the table
	var thisRow = tbl_gallery.insertRow(-1);
	var firstImg = true;
	for (var imageID in glazeImages) {
		if (i >= 3 ) {
			thisRow = tbl_gallery.insertRow(-1);		// Inserts new row at end of table
			i = 0;
		}
		// Adds cell with thumbnail to row
		var src = glazeImages[imageID]['ImageSrc'];
		var thumbSrc = src.replace("images/", "images/thumb_");
		var descr = glazeImages[imageID]['ImageDescr'];
		thisRow.insertCell(-1).innerHTML = "<a href='" + src + "' class='gallery'><img src='" + thumbSrc + "' width='100px' alt='" + descr + "' /></a>";
		
		// sets first image as default loaded img
		if (firstImg == true) {
			$('#loadedImage').attr('src',src);
			firstImg = false;
		}
		i++;
	}
	
	// Sets 'click' evt for gallery thumbs
	$('a.gallery').click(function(evt) {
		//var el_img = $(this).children('img');							  
		var src = $(this).attr('href');
		$('#loadedImage').attr('src', src);
		
		// Sets lightbox for loaded image
		var imageLink = $('#loadedImage').parent('a')
		imageLink.attr('href', src);
		loadLightBox(imageLink);
		
		// Prevent <a> from acting as link
		evt.preventDefault();
		return false;
	});
}

function filterIngrList() {
// Filters ingr ListBox options by search text
	// Sends search to AJAX, to query ingredients table
	var ingrFilter = $('#input_ingrFilter').val();
	var ajaxFile = '/includes/AJAX/filterIngrList.php';
	
	
	// Creates drop-down table (if it doesn't exist already)
	if ($('#tbl_suggest').length == 0) {
		$("<table id='tbl_suggest'></table>").insertAfter('#input_ingrFilter');
		// Set width of table to same as input
		var inputWidth = parseFloat($('#input_ingrFilter').css('width'));
		$('#tbl_suggest').width(inputWidth+6);
		
		// Animate table like drop-down
		$('#tbl_suggest').addClass('suggest')
		.animate({
				'line-height':'20px',
		}, 300);
	}

	// Show loading row/image
	$('#tbl_suggest').append("<tr id=suggest_loading><td><img src='/images/loading.gif' /></td></tr>");
	
	// Call to AJAX and callback function
	$.post(ajaxFile, "filter="+ingrFilter, function(res) {
		var ingrIDName = $.evalJSON(res);		// ingrIDName = array((ID, NAME),(ID,NAME),....)
		
		// Reset results table
		$('#tbl_suggest').empty();
		$('#suggest_loading').remove();
		delete ingrSelected['ingrID'];
		delete ingrSelected['ingrName'];
		
		// Add each ingredient to table
		if (ingrIDName.length > 0) {
			var i=0;
			for (var i=0; i<ingrIDName.length; i++) {
				var ingrID = ingrIDName[i][0];
				var ingrName = ingrIDName[i][1];
				$('#tbl_suggest').append("<tr class='ingrRow' id='"+ingrID+ "'><td><div id='ingrName'>"+ingrName+"</div></td></tr>");
				
				// Set first item as selected, if none was selected before
				if (i==0 && ingrSelected['ingrID'] == undefined) {
					$('#'+ingrID).css(suggestHoverCSS);
					ingrSelected['ingrID'] = ingrID;
					ingrSelected['ingrName'] = ingrName;
				}
				i++;
			}
		}
		else {
			$('#tbl_suggest').append("<tr><td><i>No ingredients found</i></td></tr>");
		}

		// Bind hover and events to ingredient list
		$('.ingrRow').hover(function() {
			var ingrID = $(this).attr('id');
			var ingrName = $(this).find('#ingrName').html();
			ingrSelected['ingrID'] = ingrID;
			ingrSelected['ingrName'] = ingrName;
			// sets hover css on this ingr
			$('.ingrRow').css(sugestNormalCSS);
			$(this).css(suggestHoverCSS);		
		}, function() {
			$(this).css(sugestNormalCSS);
		});
		
		// Bind click event to ingredient list
		$('.ingrRow').click(function() {
			// Add selected ingredient to recipe table
			addIngr(ingrSelected['ingrID'], ingrSelected['ingrName']);
		});
		
	});	// end of AJAX callback
	

} // end filterIngrList

function addIngr(ingrID, ingrName) {
// Adds the selected ingredient to the recipe array,
// Then rebuilds the recipe table from the array

	// Check if ingr exists, then adds
	if (recipe[ingrID] == undefined) {
		recipe[ingrID] = 0;		// Sets initial amount to 0
		$('#srch_loading').html("");
		buildRecipeTable(ingrID, ingrName);
	}
	else {
		$('#srch_loading').html("<i>Your glaze already has this ingredient in it!</i>");
	}
	
	$('#saveResults').html("<i>Remember to save!</i>");
}

function buildRecipeTable(ingrID, ingrName) {
// Clears and rebuilds recipe table from recipe array
	var cell_ingrName = "<td><b>"+ingrName+"</b></td>";
	var cell_ingrAmt = "<td><input type='text' name='" + ingrID + "' value='" + recipe[ingrID] + "' class='ingrAmt' /></td>";
	var cell_delete = "<td><a href='#' class='deleteIngr'><img src='/images/delete_icon.png' /></a></td>";
	$('#table_recipe').append("<tr>"+cell_ingrName+cell_ingrAmt+cell_delete+"</tr>");
	
	// Change Ingr Amt => update recipe, and reload unity
	$('input.ingrAmt').keyup(function() {
		var ingrID = $(this).attr('name');
		var ingrAmt = $(this).val();
		var errorDivID = "ingrAmtError_" + ingrID; // ID of div used for error msg
		// Validates amount before adding to recipe array
		if (isNaN(ingrAmt) || ingrAmt < 0) {
			$('#' + errorDivID).html("Please enter a valid amount");
			input_amt.value = "";
		}
		else {
			$('#' + errorDivID).html("");
			recipe[ingrID] = ingrAmt;
			updateUnity();
		}
		
		$('#saveResults').html("<i>Remember to save!</i>");
	});
	
	// Delete => remove row, and update unity
	$('a.deleteIngr').click(function() {
		var input_ingrAmt = $(this).parents('tr').find('.ingrAmt');
		var ingrID = input_ingrAmt.attr('name');
		delete recipe[ingrID];
		$(this).parents('tr').remove();
		updateUnity();
	});
	
	updateUnity();
}

// updates glazeInfo obj. w/ info from input 
function updateGlazeInfo(input) {
	var field = input.name;
	var value = input.value;
	
	glazeInfo[field] = value;
	
	$('#saveResults').html("<i>Remember to save!</i>");
}

function getValidAssoc (assoc) {
// Only send valid (value>0) to php (prevents errors and frees server memory)
	// Create a validRecipe array
	var validAssoc = new Object();
	for (var prop in assoc) {
		var value = assoc[prop];
		if (value > 0 && !isNaN(value) && value != "") {
			validAssoc[prop] = value;
		}
	}
	
	return validAssoc;
}

function updateUnity() {
// sends recipe to calculateUnity.php via AJAX
// returns unity
	// Only send valid >0 ingrAmts to php (prevents errors and frees server memory)
	// Create a validRecipe array
	var validRecipe = getValidAssoc(recipe);
	
	// Only runs AJAX if validRecipe is not empty
	if (assocLength(validRecipe) > 0) {
		var ajaxFile = "/includes/DYLMG_calculateUnity.php";
		var dataString = $.toJSON(validRecipe);
		$('#div_unityTable').html("<img src='/images/loading.gif' />");	
		$.post("../includes/DYLMG_calculateUnity.php", 'data=' + dataString, function (res) {
			// var unityOxide = $.evalJSON(res); 	// Use when recieving $unityOxide (oxideID => unity amt) array from php
			$('#div_unityTable').html("<h3>Unity Table</h3>");
			$('#div_unityTable').append(res);
			$('#div_unityTable').children('table').addClass('unityTable');
		});
	}
}

function saveGlaze() {
	// removes empty values in recipe and glazeInfo objects
	var validRecipe = getValidAssoc(recipe);
	var validGlazeInfo = new Object();
	for (var field in glazeInfo) {
		if (glazeInfo[field] != "") {
			validGlazeInfo[field] = glazeInfo[field];
		}
	}
	
	// Validation rules (there should be more here... ie. all info fields are set with valid info, etc.)
	if (assocLength(validRecipe) < 1) {
		$('#saveResults').html("<b>Glaze must contain at least one ingredient</b>");
	}
	else if (!('GlazeName' in validGlazeInfo) || validGlazeInfo['GlazeName'] == "") {
		$('#saveResults').html("<b>Please enter a glaze name before saving</b>");
	}
	else {
		$('#saveResults').html("<img src='/images/loading.gif' />");
		var ajaxFile = '/includes/AJAX/saveGlaze.php';
		var recipeString = $.toJSON(validRecipe);
		var glazeInfoString = $.toJSON(validGlazeInfo);
		$.post(ajaxFile, {recipe: recipeString, glazeInfo: glazeInfoString}, function (res) {
			setGlazeID(res);
			$('#saveResults').html("Saved!");	
		});
	}
}

function setGlazeID(id) {
	glazeInfo['GlazeID'] = id;
}

function startImgUpload(evt) {	
	// NEED VALIDATION. For: image is selected, what else?

	// If no glazeID, need to save first to create one
	if (glazeInfo['GlazeID'] == "new") {
		$('#img_loading').html("Please save your glaze before uploading an image");
		evt.preventDefault();
		return false;
	}
	else{
		// Set imgUpload form hidden inputs
		$('input[name=imgUpload_userID]').val(glazeInfo['GlazeAuthor']);
		$('input[name=imgUpload_glazeID]').val(glazeInfo['GlazeID']);
	
		// Sets "Loading..." div
		$('#img_loading').html("<img src='/images/loading.gif' /> Uploading Image....");
	}
}

function imgUploadCallback(success, src, newImageID, msg) {
	if (success) {
		$('#img_loading').html("Image uploaded.");
		glazeImages[newImageID] = new Array();
		glazeImages[newImageID]['ImageSrc'] = src;
		buildGallery();
	}
	else {
		$('#img_loading').html("Error: " + msg);
	}
}


// }); // end of $(document).ready
HERE;
// End $js

$s = new DYLMG_SuperHTML('Submit a Glaze');
$s->addCSSLink('/includes/lightbox/css/jquery.lightbox-0.5.css');
$s->addCSSLink('/includes/CSS/suggestTable.css');
$s->addJSLink('/includes/jsFunctions.js');
$s->addJSLink('/includes/jquery-1.5.min.js');
$s->addJSLink('/includes/jquery.json-2.2.min.js');
$s->addJSLink('/includes/lightbox/js/jquery.lightbox-0.5.min.js');
$s->addJS($js);
$s->buildTop();


$body = <<<HERE
<div style="float:left">

<input type="button" name="saveForm" value="Save" onclick="saveGlaze()" >
<div id="saveResults"></div>

<h4>Glaze Name</h4>
<input type='text' name='GlazeName' value='$glazeInfo[GlazeName]' id='input_glazeName' onkeyup='updateGlazeInfo(this)' /><br>
<b>Search</b><br />
<div style="position:relative">
<input type='text' name='ingrFilter' id='input_ingrFilter' style="width:200" />
</div>
</div>
<!-- End Search Div-->

<div id="alert"></div><!-- Testing space -->

<div style="float:left; margin-left:2%">
<h3>Base Recipe</h3>
<table id="table_recipe" class="recipeTable">
	<thead> 
		<td width='150px'>Ingredient</td>
		<td width='100px'>Amount</td>
	</thead>
</table>

<div id='div_additions' style="clear:both; margin-top:2%">
<h3>Additional Ingredient</h3>
<table id="table_additional" class="recipeTable">
	<thead> 
		<td width='150px'>Ingredient</td>
		<td width='100px'>Amount</td>
	</thead>
</table>
</div>

<div id='div_unityTable'>
</div>

</div><!-- End Recipe Div-->

<div id="rightColumn"  style="float:left; margin-left:2%;">

<a href="#" class="lightbox"><img id="loadedImage" width='300' /></a>
<table id="tbl_gallery"></table>
<div id="imgUploadForm">
<h3>Add Image</h3>
<div id='img_loading'></div>
<div id='img_results'></div>
<form action='/includes/imgUpload.php' method='post' enctype="multipart/form-data" target="imgUpload" onSubmit="startImgUpload(evt);" >


<iframe id='imgUpload' name="imgUpload" src="#" style="width:0;height:0;border:0px solid #fff;"><!-- Proceses image upload--></iframe>
<input name="imgFile" type="file" value="Browse..." /><br />
<b>Description:</b>
<input name="imgDescr" type="text" /><br />
<input type="submit" name="submitBtn" value="Upload" />
<input type="hidden" name="imgUpload_userID" />
<input type="hidden" name="imgUpload_glazeID" />
</form>
</div><!--End imgUploadForm Div-->

</div><!-- End rightColumn -->



HERE;
// end of body HTML

$s->addText($body);

$s->buildBottom();
print $s->getPage();