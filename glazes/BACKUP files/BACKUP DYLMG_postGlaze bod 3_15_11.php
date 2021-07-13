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
		
		// Create array of Recipe amts
		$sql =    "SELECT GlazeIngredients.IngredientID, GlazeIngredients.IngredientAmt, Ingredients.IngredientName, Ingredients.IngredientBase \n"
				. "FROM GlazeIngredients \n"
				. "LEFT JOIN Ingredients ON GlazeIngredients.IngredientID = Ingredients.IngredientID \n"
				. "WHERE GlazeID = $glazeID \n"
				. "AND Ingredients.IngredientBase = 1";
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
var varRecipe = new Object();		// varRecipe[ingrID] = amt
var recipeIDName = $jsRecipeIDName;	// recipeIDName[ingrID] = ingrName  : 	Needed for names of pre-existing recipe ingredients
var glazeInfo = $jsGlazeInfo;		// glazeInfo[FieldName] = value

var varInfo = new Object();
varInfo['VarID'] = 'new';			// Sets loaded variation. Default is 'new' -- varID is set with loadVariation('default'/varID);
varInfo['GlazeID'] = glazeInfo['GlazeID'];

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
			addIngr(ingrSelected['ingrID'], ingrSelected['ingrName'], ingrSelected['ingrBase']);
			$('#input_ingrFilter').val("");
			ingrSelected = {};		// empty ingrSelected
			return;					// end event function
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
		var ingrName = targetRow.find('.ingrName').html();
		var ingrBase = targetRow.find('.ingrBase').html();
		ingrSelected['ingrID'] = ingrID;
		ingrSelected['ingrName'] = ingrName;
		ingrSelected['ingrBase'] = ingrBase;
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
	
	
	// Open "New Glaze" Dialog for new glaze
	if (glazeInfo['GlazeID'] == 'new') {
		loadVarDialog('div#dialog_newGlaze', 'newGlaze', true);
	}	else {
		// Remove new glaze dialog div, or it will show up on page.
		$('div#dialog_newGlaze').remove();
	}
	
	// Initiates newVar dialog
	loadVarDialog('div#dialog_newVar', 'newVar', false);
	// EVENT: Loads dialog for new variation
	$('#input_newVar').button().click(function() {
		if (saveGlaze()) { 		// save is succesful
			loadVarDialog('div#dialog_newVar', 'newVar', true)
		}
	});	// end of newVar event definition
	


});	// end of document.ready


// Onload actions
window.onload = function (event) {
	// Shows recipe and images for loaded glazes
	if (assocLength(recipe) > 0) {
		for (var ingrID in recipe) {
			buildRecipeTable(ingrID, recipeIDName[ingrID], 1);	// Sends all existing ingredients to Base table (base = 1); 	// (SQL already filtered for base)
		}
		buildGallery();
	}
	
	// Gets list of Vars, and default var recipe
	if (glazeInfo['GlazeID'] != 'new') {
		loadVariation('list');					// sets varList (varID => FieldName => value)
		loadVariation('default');				// Loads default glaze variation
	}
	
	
};

// Loads jquery ui dialog box for addGlaze/addVar
// Selector = div with dialog selector; type='newGlaze'/'newVar'; isOpen=true/false (autoopen)
function loadVarDialog (selector, type, isOpen) {
	// Sets variables for each input field
	var newVar_name = $(selector).find('#newVar_name'),
		newVar_descr = $(selector).find('#newVar_descr'),
		newVar_color = $(selector).find('#newVar_color'),
		newVar_surface = $(selector).find('#newVar_surface'),
		newVar_default = $(selector).find('#newVar_default'),
		allFields = $( [] ).add(newVar_name).add(newVar_descr).add(newVar_color).add(newVar_surface);
	
	
	// Define buttons functions
	var btn_addNew = function() {
		var glazeID = glazeInfo['GlazeID'];
		var varAuthor = glazeInfo['GlazeAuthor'];
		// Add new var properties
		varInfo = {}; 			//empty variation
		varInfo = {'VarID': 'new', 
					'GlazeID': glazeID, 
					'VarAuthor': varAuthor,
					'VarName': newVar_name.val(),
					'VarDescr': newVar_descr.val(),
					'VarColor': newVar_color.val(),
					'VarSurface': newVar_surface.val(),
					'VarDefault': newVar_default.val(),							// If it is new, it is not default (original)
					};			
		varRecipe = {};											// Empties varRecipe
		saveGlaze();
		$('#table_additional tbody').empty();					// Empties variation recipe table
		$('div.dialog_newInfo').dialog('close');		
	};
	
	// Create buttons object
	var glazeDialogBtns = new Object();
	if (type == 'newGlaze') {
		glazeDialogBtns['Add New Glaze'] = btn_addNew;	
		glazeDialogBtns['Cancel'] = function () { window.location = '/usr/myAccount.php'; };
	} else if (type == 'newVar') {
		glazeDialogBtns['Add New Glaze Variation'] = btn_addNew;
		glazeDialogBtns['Cancel'] = function() { $(selector).dialog('close'); };
	} else { alert('Error: no dialog of that type exists'); }
	

		
	$(selector).dialog({
		open: function(event, ui) { 
			$(".ui-dialog-titlebar-close").hide();
		},
		autoOpen: isOpen,
		height: 500,
		width: 400,
		modal: true,
		buttons: glazeDialogBtns,					// default: use glaze dialog btns as defined above
		// Remove close button in corner "x"
		closeOnEscape: false,
		close: function() { }
	});	// end of dialog definition
	
		
}	// end of loadVarDialog()



function loadVariation(varID) {
// Loads recipe for variation 'varID' (=#, or ='default')
// Or loads list of variations if varID = 'list'
	var ajaxFile = '/includes/AJAX/loadVariation.php';
	var dataJSON = {'varID': varID, 'glazeID': glazeInfo['GlazeID'], 'glazeAuthor': glazeInfo['GlazeAuthor']};
	$.post(ajaxFile, dataJSON , function(res) {
		var resArray = $.evalJSON(res);
		// Build list of variations
		if(varID == 'list') {
			var varList = resArray['varList'];
			if (varList != 'none') {	// If variations exist for this glaze
				buildVarsList(varList);
			}
		}
		
		// Load variation recipe into Additional Ingr table
		else {
			varRecipe = resArray['varRecipe'];
			varInfo = resArray['varInfo'];
			delete varInfo['VarDatePosted'];		// This value should not be adjusted, and will actually mess up sql of saveGlaze.php
			var isVarAuthor = resArray['isVarAuthor'];
			var varIngrIDName = resArray['varIngrIDName'];
			setVarID(resArray['varID']);		// set loaded var ID -> to use for saving
			if (isVarAuthor == true) {
				// Empties additional ingr table
				$('#table_additional').empty();
				$('#table_additional').html("<thead><td width='150px'>Ingredient</td><td width='100px'>Amount</td></thead>");
				
				// Adds each variation ingredient to table
				for (var ingrID in varRecipe) {
					var ingrName = varIngrIDName[ingrID];
					buildRecipeTable(ingrID, ingrName, 0);			// Adds each var ingredients to Additional Ingr table
				}
			}
		}
	}); // End of ajax callback function
} // End of loadVariation

function buildVarsList(varList) {
	// Loads variations into table
	$('#table_vars tbody').empty();
	for (var variation in varList) {
		for (var fieldName in varList[variation]) {
			var varName = varList[variation]['VarName'];
			var varColor = varList[variation]['VarColor']; 
			var varID = varList[variation]['VarID'];
			var cell_varName = "<td><div class='varID' style='visibility:hidden; width:0px; height:0px'>"+varID+"</div>";
			cell_varName += "<a href='#' class='selectVar' >"+varName+"</a></td>";
			var cell_varColor = "<td>"+varColor+"</td>";
			
			// set Default variation as page title
			if (varList[variation]['default'] == 1) {
				$('glazeTitle').html("<h1>"+varList[variation]['VarName']+"</h1>");
			}
		}
		$('#table_vars tbody').append("<tr>"+cell_varName+cell_varColor+"</tr>");
	}
	
	// EVENT: Click->Loads selected variation
	$('a.selectVar').click(function() {
		var varID = $(this).siblings('div.varID').html();
		loadVariation(varID);
	});
}

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
		delete ingrSelected['ingrBase'];	// 1 = base, 0 = additive
		
		// Add each ingredient to table
		if (ingrIDName.length > 0) {
			var i=0;
			for (var i=0; i<ingrIDName.length; i++) {
				var ingrID = ingrIDName[i][0];
				var ingrName = ingrIDName[i][1];
				var ingrBase = ingrIDName[i][2];
				// Divs contain ingr attributes, can be hidden
				$('#tbl_suggest').append("<tr class='ingrRow' id='"+ingrID+ "'><td><div class='ingrBase' style='visibility:hidden; width:0px; height:0px'>"+ingrBase+"</div><div class='ingrName'>"+ingrName+"</div></td></tr>");
				
				// Set first item as selected, if none was selected before
				if (i==0 && ingrSelected['ingrID'] == undefined) {
					$('#'+ingrID).css(suggestHoverCSS);
					ingrSelected['ingrID'] = ingrID;
					ingrSelected['ingrName'] = ingrName;
					ingrSelected['ingrBase'] = ingrBase;
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
			var ingrName = $(this).find('.ingrName').html();
			var ingrBase = $(this).find('.ingrBase').html();
			ingrSelected['ingrID'] = ingrID;
			ingrSelected['ingrName'] = ingrName;
			ingrSelected['ingrBase'] = ingrBase
			// sets hover css on this ingr
			$('.ingrRow').css(sugestNormalCSS);
			$(this).css(suggestHoverCSS);		
		}, function() {
			$(this).css(sugestNormalCSS);
		});
		
		// Bind click event to ingredient list
		$('.ingrRow').click(function() {
			// Add selected ingredient to recipe table
			addIngr(ingrSelected['ingrID'], ingrSelected['ingrName'], ingrSelected['ingrBase']);
		});
		
	});	// end of AJAX callback
	

} // end filterIngrList

function addIngr(ingrID, ingrName, ingrBase) {
// Adds the selected ingredient to the recipe array,
// Then rebuilds the recipe table from the array
	// Check if ingr exists in Recipe, then adds
	if (recipe[ingrID] == undefined && ingrBase == 1) {
		recipe[ingrID] = 0;		// Sets initial amount to 0
		$('#srch_alert').html("");
		buildRecipeTable(ingrID, ingrName, ingrBase);
	}
	// Checks if Additional Ingr exists, then adds
	else if (varRecipe[ingrID] == undefined && ingrBase == 0) {
		varRecipe[ingrID] = 0;
		$('#srch_alert').html("");
		buildRecipeTable(ingrID, ingrName, ingrBase);
	}
	else {
		$('#srch_alert').html("<i>Your glaze already has this ingredient in it!</i>");
	}
	
	$('#saveResults').html("<i>Remember to save!</i>");
}

function buildRecipeTable(ingrID, ingrName, ingrBase) {
// Clears and rebuilds recipe table from recipe array
			
	// Set which table to add the ingredient to
	var targetTable = (ingrBase == 1) ? '#table_recipe' : '#table_additional';
	var ingrAmt = (ingrBase == 1) ? recipe[ingrID] : varRecipe[ingrID];
	
	// Set cell values
	var cell_ingrName = "<td><b>"+ingrName+"</b></td>";
	var cell_ingrAmt = "<td><div class='ingrBase' style='visibility:hidden; width:0px; height:0px'>"+ingrBase+"</div><input type='text' name='" + ingrID + "' value='" + ingrAmt + "' class='ingrAmt' size='5' /></td>";
	var cell_delete = "<td><a href='#' class='deleteIngr'><img src='/images/delete_icon.png' /></a></td>";
	
	// Add cells to table
	$(targetTable).append("<tr>"+cell_ingrName+cell_ingrAmt+cell_delete+"</tr>");
	
	
	
	// EVENT: Change ingredient amount 
	$('input.ingrAmt').keyup(function() {
		var ingrID = $(this).attr('name');
		var ingrAmt = $(this).val();
		var ingrBase = $(this).siblings('div.ingrBase').html();;
		var errorDivID = "ingrAmtError_" + ingrID; // ID of div used for error msg
		
		// Validates amount before adding to recipe array
		if (isNaN(ingrAmt) || ingrAmt < 0) {
			$('#' + errorDivID).html("Please enter a valid amount");
			input_amt.value = "";
		}
		
		// Adds ingredient to recipe/varRecipe
		else {
			$('#' + errorDivID).html("");
			if (ingrBase == 1) { 
				recipe[ingrID] = ingrAmt;
				updateUnity();
			} else {
				varRecipe[ingrID] = ingrAmt;
			}
		}
		
		$('#saveResults').html("<i>Remember to save!</i>");
	});
	
	// Delete => remove row, and update unity
	$('a.deleteIngr').click(function() {
		var input_ingrAmt = $(this).parents('tr').find('.ingrAmt');
		var ingrID = input_ingrAmt.attr('name');
		
		// Removes ingredient from table
		$(this).parents('tr').remove();

		// Deletes ingredient from recipe array
		if (ingrBase) {
			delete recipe[ingrID];
			updateUnity();
		} else {
			delete varRecipe[ingrID];
		}
		
	});
	
	if (ingrBase) {
		updateUnity();
	}
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
		$('#div_unityTable').append("<img id='unityLoading' src='/images/loading.gif' />");	
		$.post("../includes/DYLMG_calculateUnity.php", 'data=' + dataString, function (res) {
			// var unityOxide = $.evalJSON(res); 	// Use when recieving $unityOxide (oxideID => unity amt) array from php
			$('#div_unityTable').html("<h3>Unity Table</h3>");
			$('#div_unityTable').append(res);
			$('#div_unityTable').children('table').addClass('unityTable');
			
			// remove loading image
			$('#div_unityTable img.unityLoading').remove();
		});
	}
}

function saveGlaze() {
	// removes empty values in (var)recipe and glaze/varInfo objects
	var validRecipe = getValidAssoc(recipe);
	
	// Validation rules (there should be more here... ie. all info fields are set with valid info, etc.)
	if (assocLength(validRecipe) < 1) {
		$('#saveResults').html("<b>Glaze must contain at least one ingredient</b>");
		return false;
	}
	
	// Set AJAX params
	$('#saveResults').html("<img src='/images/loading.gif' />");
	var ajaxFile = '/includes/AJAX/saveGlaze.php';
	var recipeString = $.toJSON(validRecipe);
	var glazeInfoString = $.toJSON(glazeInfo);
	// Save Base Glaze and get glazeID
	$.post(ajaxFile, {'recipe': recipeString, 'glazeInfo': glazeInfoString, 'type': 'Glaze'}, function (res) {
		// Set glaze ID, and also varInfo['GlazeID']
		setGlazeID(res);
			$('#alert').html(res);
		
		// Save Variation (after glaze is already saved, so we can get varInfo['GlazeID'])
		
		// Create validVarInfo JSON with updated varInfo['GlazeID']
		var validVarInfo = new Object();
		for (var field in varInfo) {
			if (varInfo[field] != "") {
				validVarInfo[field] = varInfo[field];		// removes empty fields
			}
		}
		var validVarRecipe = getValidAssoc(varRecipe);
		var varRecipeString = $.toJSON(validVarRecipe);
		var varInfoString = $.toJSON(validVarInfo);
		// Save loaded variation							// Need 2 AJAX calls, in order to make sure varInfo['GlazeID'] is set before saving variation
		$.post(ajaxFile, {'recipe': varRecipeString, 'glazeInfo': varInfoString, 'type': 'Var'}, function (res) {
			// Set glaze ID, and also varInfo['GlazeID']
			setVarID(res);
			$('#saveResults').html("Saved!");
			
			// Reloads list, and new variation
			loadVariation('list');
			loadVariation(varInfo['VarID']);
		});
	});	// end of saveglaze.php callback
	return true;	// validation is good
}

function setGlazeID(id) {
	glazeInfo['GlazeID'] = id;
	varInfo['GlazeID'] = id;
}

function setVarID(id) {
	varInfo['VarID'] = id;
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
//$s->addCSSLink('/includes/lightbox/css/jquery.lightbox-0.5.css');				// Style for JS lightbox
//$s->addCSSLink('/includes/CSS/suggestTable.css');								// Style for ingredients suggest dropdown (TO BE REPLACED WITH JQUERY UI)


$s->addJSLink('/includes/jsFunctions.js');

// JQUERY resources
$s->addCSSLink('/includes/jquery_ui/css/start/jquery-ui-1.8.10.custom.css');	// Style for all jquery ui elements
$s->addJSLink('/includes/jquery-1.5.min.js');									// Core jquery file
$s->addJSLink('/includes/jquery_ui/js/jquery-ui-1.8.10.custom.min.js');
$s->addJSLink('/includes/jquery.json-2.2.min.js');								// handles JSON conversion (for AJAX)
$s->addJSLink('/includes/lightbox/js/jquery.lightbox-0.5.min.js');				// jquery lightbox
$s->addJS($js);
$s->buildTop();


$body = <<<HERE
<!-- Header (Glaze Name)-->
<div id='glazeTitle'></div>

<!-- Testing space -->
<div id="alert" style="clear:both; width:100%"></div>
<div id="alert2" style="clear:both; width:100%"></div>
<!--			   -->


<!-- Left Column-->
<div style="float:left">
	<!-- Ingredient Search -->
	<b>Search</b><br />
	<div style="position:relative">
	<input type='text' name='ingrFilter' id='input_ingrFilter' style="width:200" />
	</div>
</div><!-- End Left Column-->

<div style="float:left; margin-left:2%"><!-- Middle Column-->
	
	<!-- Unity Table-->
	<div id='div_unityTable'>
	</div>
	
	<h3>Base Recipe</h3>
	<div id="saveResults"></div>
	<table id="table_recipe" class="recipeTable">
		<thead> 
			<td width='150px'>Ingredient</td>
			<td>Amount</td>
		</thead>
		<tbody>
			<!-- Recipe Ingredients go here -->
		</tbody>
	</table>
	
	<!-- Save Glaze-->
	<input type="button" name="saveForm" value="Save" onclick="saveGlaze()" >

	<div id='div_additions' style="clear:both; margin-top:2%">
	<h3>Additional Ingredient</h3>
	<table id="table_additional" class="recipeTable">
		<thead> 
			<td width='150px'>Ingredient</td>
			<td width='100px'>Amount</td>
		</thead>
		<tbody>
			<!-- Recipe Ingredients go here -->
		</tbody>
	</table>
	</div>
	<button id='input_newVar'>Add New Variation</button>

	<!-- List of glaze variations -->
	<h3>Variations</h3>
	<div id='div_vars' style="margin-top:2%">
	<table id='table_vars'>
		<thead>
			<td width='150px'>Name</td>
			<td width='100px'>Color</td>
		</thead>
		<tbody>
			<!-- List of glaze variations goes here -->
		</tbody>
	</table>
	</div>
	
</div><!-- End Middle Column-->

<div id="rightColumn"  style="float:left; margin-left:2%;"><!-- Right Column-->
	
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

<!-- DIALOG: Add new variation -->
<!-- from: jqueryui.com/demos/dialog/ -->
<div id='dialog_newVar' class='dialog_newInfo' title="Add New Glaze Variation">
	<form><fieldset>
		<label for='newVar_name'>Variation Name</label><br />
		<input type='text' name='newVar_name' id='newVar_name' /><br />
		<label for='newVar_color'>Variation Color</label><br />
		<input type='text' name='newVar_color' id='newVar_color' /><br />
		<label for='newVar_surface'>Variation Surface</label><br />
		<input type='text' name='newVar_surface' id='newVar_surface' /><br />
		<label for='newVar_descr'>Variation Description</label><br />
		<textarea rows='5' cols='35' name='newVar_descr' id='newVar_descr' /></textarea>
		<input type='hidden' id='newVar_default' name='newVar_default' value=0 />
	</fieldset></form>
</div>
<!-- End new Var DIALOG -->

<!-- DIALOG: Add new Glaze -->
<!-- from: jqueryui.com/demos/dialog/ -->
<div id='dialog_newGlaze' class='dialog_newInfo' title="Add New Glaze">
	<form><fieldset>
		<label for='newVar_name'>Glaze Name</label><br />
		<input type='text' name='newVar_name' id='newVar_name' /><br />
		<label for='newVar_color'>Glaze Color</label><br />
		<input type='text' name='newVar_color' id='newVar_color' /><br />
		<label for='newVar_surface'>Glaze Surface</label><br />
		<input type='text' name='newVar_surface' id='newVar_surface' /><br />
		<label for='newVar_descr'>Glaze Description</label><br />
		<textarea rows='5' cols='35' name='newVar_descr' id='newVar_descr' /></textarea>
		<input type='hidden' id='newVar_default' name='newVar_default' value=1 />
	</fieldset></form>
</div>
<!-- End new Var DIALOG -->



HERE;
// end of body HTML

$s->addText($body);

$s->buildBottom();
print $s->getPage();