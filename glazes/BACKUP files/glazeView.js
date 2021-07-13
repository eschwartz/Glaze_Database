/**
 * Procedural code for 
 * /glazes/glazeView.php
 *
 * Requires:
 *	GlazeObject.js
 *	jquery
 *	json for jquery
 *	autosuggest
 *	hoverGallery
*/

/** 
 * Instantiate Glaze Object
 */
// FROM PHP:
// GET_glazeID = glazeID in query string. Validated as number, or set to 0 (new)

var glaze = new Glaze(GET_glazeID);		// GET_glazeID is a gift from glazeView.php using $_GET['glazeID']


$(document).ready(function() {
		

	
	/** 
	 * NEW GLAZE DIALOG 
	*/
	
	// Create a copy of the newVariation dialog, but with "Glaze" labels
	var dialog_newGlaze = $('#dialog_newVariation').clone();
	// Replace "Variation" with "Glaze"
	dialog_newGlaze.find('label').each(function() {
		$(this).text( $(this).text().replace(/variation/i, "Glaze") );										
	});
	dialog_newGlaze.attr('title', "Create a Glaze").attr('id', 'dialog_newGlaze');			// Must have new id, to prevent interference with new Vartn dialog
	
	// Define dialog
	dialog_newGlaze.dialog( {
		autoOpen: false,
		height: 350,
		width: 500,
		modal: true,
		open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },				// Removes the 'x' close button
		buttons: {
			"Create Glaze": function() {
				// Create a new Variation object with values from form
				glaze.GlazeAuthor = 1;					// I assume this should be a prop from a User object at some point, with loadedUser coming from PHP
				glaze.GlazeDatePosted = new Date();		
				
				// Creates the default variation for the new glaze
				glaze.defaultVar = new Variation();
				glaze.defaultVar.VarDatePosted = new Date();
				glaze.defaultVar.VarDefault = 1;
				glaze.defaultVar.VarAuthor = 1;			// TO BE THE LOADED USER ID
				
				// Sets properites for the default Variation object from Create Glaze form
				$(this).find('input,textarea,select').each(function() {
					glaze.defaultVar[$(this).attr('id')] = $(this).val();				// Property name ~ element id
				});
				
				glaze.variations[0] = glaze.defaultVar;
				
				// Save new glaze
				asyncMsg(glaze.saveGlaze, "Saving...");
				
				// Show the glaze on the page
				displayGlaze();
				
				$(this).dialog('close');
				
			},
			"Cancel": function() {
				window.location = '/usr/myAccount.php';							// return to user account
			}
		},
		// Prevent user from closing dialog (besides pressing cancel -> redirect)
		closeOnEscape: false,
		close: function() {	
			$(this).find('input,textarea,select').val("");						// Clears text boxes
		}
	});	// end of new glaze dialog
	
	/**
	 * NEW VARIATION DIALOG 
	 * btw, it seems like this has to be after new glaze dialog, or dialogs will confuse each other (???)
	*/
	$('#dialog_newVariation').dialog( {
		autoOpen: false,
		height: 350,
		width:500,
		open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },				// Removes the 'x' close button
		buttons: {
			"Create Variation": function(){
				var newVariation = new Variation();
				newVariation.VarDatePosted = new Date();
				newVariation.VarDefault = 0;
				newVariation.VarAuthor = 1;													/** TO BE THE LOADED USER ID **/
				
				// Sets properites for the default Variation object from Create Glaze form
				$(this).find('input,textarea,select').each(function() {
					newVariation[$(this).attr('id')] = $(this).val();						// newVariation[FieldName] = value from form
				});
				
				glaze.variations.push(newVariation);
					
				// Save new glaze
				asyncMsg(glaze.saveGlaze, "Saving...");
				
				// Show the glaze on the page
				displayVariationsList();
				$('div#div_vars table#table_vars tbody').find('tr:last').addClass('ui-state-highlight');			// Highlight variation
				displayVariation(glaze.lastVariation() );
				
				$(this).dialog('close');

			},	// end of create variation btn
			"Cancel": function() {
				$(this).dialog('close');
			}	
		},		// end of buttons
		closeOnEscape: false,
		close: function() {	
			$(this).find('input,textarea,select').val("");						// Clears text boxes
		}

	});	// end of new variation dialog

  
	
	/** 
	 * CHECK FOR NEW GLAZE
	 * Open new glaze dialog
	 * OR Load glaze to page
	*/
	glaze.loadGlaze();					// Loads glaze Object from database
	if (!glaze.id) { 
		dialog_newGlaze.dialog('open');
	}
	else {
		// Loads glaze info from database using AJAX with ({async: false})
		// so page will wait to load until AJAX is complete.
		// Alternatively, could use the asyncLoad() function
		glaze.loadGlazeVariations();		// Loads all glaze variations from database
		displayGlaze();
	}

							   
	// TESTING BUTTON
	$('button#testBtn').click(function() {
		console.log(glaze);
	});
	
	/** 
	 * BUTTON EVENTS
	*/
	
		/* ADD NEW VARIATION */
	$('#btn_addVariation, .input_newVar').click(function() {
		$('#dialog_newVariation').dialog('open');
	});
	
		/* Calculate Percentage Weight */
	$('#input_batchAmt').keyup(function() {
		glaze.calcPW($(this).val() );
	
		// Empty recipe tables
		$('tr.ingrRow').remove();
		
		// Reload tables from objects
		for(i=0; i<glaze.recipe.length; i++) {
			ingrToTable(glaze.recipe[i], true);
		}
		for(i=0; i<glaze.loadedVar.recipe.length; i++) {
			ingrToTable(glaze.loadedVar.recipe[i], false);
		}
	});
	
		/* SAVE GLAZE */
	$('#saveGlaze').click(function() {
		asyncMsg(glaze.saveGlaze, "Saving...");
	});
	
	
		/* COLLAPSE HIGHLIGHT SECTIONS */
	var collapseBtn = $('div#highlight-column div.section').find('div.sectionHead');		// Section head in highlight-column
	collapseBtn.click(function() {
		var sectionContent = $(this).siblings('div.sectionContent');
		sectionContent.animate({
			height: 'toggle'
		}, 250);
	});
	
		/* IMAGE UPLOAD (DIALOG)*/
	$('#btn_imgUpload').button().click(function() { 
		$('#dialog_imgUpload').dialog('open');
	});
	$('#dialog_imgUpload').dialog({
		autoOpen: false,
		height: 350,
		width: 500,
		modal: true,
		buttons: {
			"Upload Glaze Image": function() {
				// Set imgUpload form hidden inputs
				$('input[name=imgUpload_userID]').val(glaze.loadedVar.VarAuthor);
				$('input[name=imgUpload_varID]').val(glaze.loadedVar.VarID);
				
				// Show 'uploading' animation
				$(this).find(".progressbar").html("Uploading file....<br /><img src='/images/loading.gif' />");
				
				// Create iframe with imgUpload script (faux-AJAX)
				/** Loading glazeView.php with iframe causes slow loading, multiple document.ready calls */
				$('form#form_imgUpload').append("<iframe id='imgUpload' name='imgUpload' src='#' style='width:0;height:0;border:0px solid #fff;'></iframe>");
				
				document.forms['form_imgUpload'].submit();						// Submits form (with iframe target)
			},
			"Cancel": function() {
				$(this).dialog('close');
			}
		},	// end of buttons
		close: function () {
			$(this).find('input').val("");										// empties all inputs
			$(this).find('textarea').html("").val("");
		}
	});	// end of image upload dialog
	
	/** 
	 * AUTOCOMPLETE INGREDIENT SELECT 
	 */
	var ingrName = new String();													// define here, so 'close' event can access
	$('.ingrSuggest').autocomplete({
		source: '/includes/AJAX/ingrAutoComplete.php',
		minLength: 2,																// Number of characters entered before suggestions appear
		delay: 300,																	// millisecond waits after a keystroke to activate .
		selectFirst: true,															// starts with focus on first list item (doesn't seem to work...)
		mustMatch: true,															// Only allows selecting from list (only sort-of works)
		search: function() {
			// Triggered when search is entered (>= minLength),
			// but before suggestions table appears
			$(this).addClass('loading');
		},
		open: function() {
			// Triggered when the suggestions table appears
			$(this).removeClass('loading');
		},
		select: function(event, ui) {
			ingrName = ui.item.label;												// so 'close' event can access
			var ingrID = ui.item.value;
			
			// Determine if base ingredient
			var base = ($(this).parents('table').attr('id') == 'table_glaze');		// returns true for base table input
			
			// Add to appropriate table and object
			if (base) {
				if (glaze.addIngr(ingrID, ingrName, 0) ) {							// add ingredient to glaze object. Retunrs FALSE if duplicate
					ingrToTable(glaze.lastIngr(), base);							// add ingredient to recipe table
					$('#table_glaze tbody tr.ingrRow:last').find('input').focus();	// Set focus to ingredient amount
				} else { return false; }
			} else {
				if (glaze.loadedVar.addIngr(ingrID, ingrName, 0) ) {				// add ingredient to glaze object. Retunrs FALSE if duplicate
					ingrToTable(glaze.loadedVar.lastIngr(), base);					// add ingredient to recipe table
					$('#table_variation tbody tr.ingrRow:last').find('input').focus();	// Set focus to ingredient amount		
				} else { return false; }
			}
	
		},
		focus: function(event, ui) {
			// Prevents from showing item.value (IngrID) on focus
			return false;
		},
		close: function(event, ui) {
			// Instead of putting item.value (IngrID) in input
			// puts item.label (IngrName)
			$(this).val("");									// Overwrite default (that input shows item.value = ingrID)
		}	
	});
	// Remove loading gif on losing focus
	$('.ingrSuggest').blur(function() { $(this).removeClass('loading'); } );
	// Prevent [TAB] (ingr select) from skipping over new ingr amt
	$('.ingrSuggest').keydown(function (event) {
		console.log(event.which);
		if (event.which == '9') {
     		event.preventDefault();
			return false;
		}
   });
	
	
});	// end of document.ready function

/**
 * DISPLAY GLAZE ON PAGE
 * Check first that glaze exists
 * Or allow user to create a new glaze before running
*/
function displayGlaze() {
	// Load base recipe
	for(i=0; i<glaze.recipe.length; i++) {
		ingrToTable(glaze.recipe[i], true);
	}
		   
	// Set page title
	$('div#glazeTitle').html(glaze.defaultVar.VarName);
	
	// Load variations list
	displayVariationsList()
	
	// Display default variation
	displayVariation(glaze.defaultVar);
	$('div#div_vars table#table_vars tbody').find('tr:first').addClass('ui-state-highlight');			// highlight default variation row
	
	// Update unity
	updateUnity();

}	// end of displayGlaze()


/**
 * ADD INGREDIENT TO TABLE
 * (and ingredient events)
 * base = true/false
*/
function ingrToTable(ingrObj, base) {
	var ingrTable = (base)? $('table#table_glaze') : $('table#table_variation');
	var newIngrRow = ingrTable.find('tbody tr.new_ingrRow');			

	var cell_name = $("<td>"+ingrObj.IngredientName+"</td>");
	// EVENT: CHANGE INGREDIENT AMT 
	var input_amt = $("<input size=6 value='"+ingrObj.IngredientAmt+"' style='text-align:right' />")
					.keyup(function() {
						ingrObj.IngredientAmt = $(this).val();						// Update Ingredient Amt	
						if (base) { updateUnity(); }								// Update unity table
						$('#input_batchAmt').val(glaze.sumOfBase() );				// Update sum of base
					});
	var cell_amt = $("<td>").append(input_amt);
	var cell_delete = $('<td>').append(
		$('<img>').attr('src','/images/delete_icon.png')
					.click(function() {
						// EVENT: DELETE INGREDIENT
						if (base) { glaze.removeIngr(ingrObj);	}
						else { glaze.loadedVar.removeIngr(ingrObj); }		// need something like: glaze.loadedVar.removeIngr(ingrObj)
						$(this).parents('tr').remove();						// Removes ingredient from table
						if (base) {updateUnity(); }							// Update unity table
					})
	);
	var ingrRow = $('<tr></tr>').addClass('ingrRow').append(cell_name).append(cell_amt).append(cell_delete);
	ingrRow.insertBefore(newIngrRow);
	
}	// end of newIngredient

/**
 * DISPLAY VARIATIONS LIST
*/
function displayVariationsList() {
	var varsTable = $('div#div_vars table#table_vars tbody').empty();
	for(i=0; i<glaze.variations.length; i++) {
		varsTable.append($('<tr>')
		   .append($('<td>')
				.append($("<a href='#'>"+glaze.variations[i].VarColor+"</a>")
					// EVENT: Click on variation --> load variation to page
					.click(function(index) {
						return function() {					// CLOSURE to pass on index of variation
							$(this).parents('tr').addClass('ui-state-highlight').siblings().removeClass('ui-state-highlight');		// highlight selected row
							displayVariation(glaze.variations[index] );
						}
					}(i))
				)
			)
		)	
	}
}	// end of displayVariationsList

/**
 * DISPLAY VARIATION ON PAGE
*/
function displayVariation(varObj) {
	// Empty variations recipe table
	$('#table_variation tr.ingrRow').remove();
	
	// Set loaded Variation
	glaze.loadedVar = varObj;
	
	// Add variation ingredients to table
	for (var i in varObj.recipe) {
		ingrToTable(varObj.recipe[i], false);
	}
	
	// Show gallery
	$('ul#thumbGallery').empty();
	$('div#loadedImg').empty()
	if (varObj.images.length > 0) {
		// Load hoverGallery
		loadHoverGallery();
	} else {
		$('div#loadedImg').append('<img src=/images/no_image.gif>');
	}
	
	//	Show variation details
	$('table#glazeDetails div#details_color') .html(varObj.VarColor);
	$('table#glazeDetails div#details_surface') .html(varObj.VarSurface);
	$('table#glazeDetails div#details_author') .html(varObj.VarAuthor);
	$('table#glazeDetails div#details_datePosted').html(shortDate(varObj.GlazeDatePosted));		// jsFunctions.js => returns formatted date (ie. May 5, 2011)
	truncateStr(glaze.defaultVar.VarDescr, 100, $('table#glazeDetails div#details_descr') );	// Show truncated description, if too long:

}	// end of displayVariation

/**
 * LOAD HOVER GALLERY
 */
function loadHoverGallery(i) {
	var i = (i==null)? 0 : i;
	/** Loads image data via AJAX (async)
	 * Starts with first image, then increases image index when complete
	 * and calls itself with the next image to load.
	 *
	 * Allows 'queued' calling of ajax, without running {async:false}
	 *
	 * NOTE: trying to put this async ajax in a regular loop does not work
	 * NOTE: for some reason, this whole function (and the displayVariation function)
	 * run twice on load (but not on click)
	*/
	//$('div#loadedImg').attr('min-width', '280').attr('min-height','280');
	asyncLoading(glaze.loadedVar.images[i].loadVarImage, function() {
		//$('ul#thumbGallery').append(glaze.loadedVar.images[i].getHoverLi() );
		i++;
		if (i <= glaze.loadedVar.images.length-1) {
			// Call this function again
			loadHoverGallery(i);
		} else {
			// Loop is finished
			$('div#loadedImg').append( $('<img>').attr('src', glaze.loadedVar.images[0].ImageSrc) ).hide().fadeIn(1000); 		// show loaded Image
			
			// Load all images
			for(var j in glaze.loadedVar.images) {
				$('ul#thumbGallery').append(glaze.loadedVar.images[j].getHoverLi() ).hide().fadeIn(1000);
			}
			hoverGallery();																									// Activate hovergallery
		}
	}, $('div#loadedImg'), 50, 10000);
}

/**
 * UPDATE UNITY RECIPE
*/
function updateUnity() {
	if (glaze.recipe.length < 1) {
		return false;
	}
	asyncLoading(glaze.getUnity, function() {
		// Callback Function (when ajax is complete)
		$('#div_unityTable').html(glaze.getUnityTable() );
	},$('#div_unityTable'), 1200);
}

/**
 * IMAGE UPLOAD CALLBACK
*/
function imgUploadCallback(success, imageID, imageSrc, msg) {
// Adds new image to gallery, and closes dialog
	// Remove iframe with image upload script (faux-AJAX)
	$('iframe#imgUpload').remove();				/** Loading glazeView.php with iframe causes slow loading, multiple document.ready calls */
	if (success) {
		// Create new Image object
		var newImage = new VarImage(imageID);
		newImage.ImageDescr = $('#form_imgUpload textarea[name=imgDescr]').val();
		newImage.ImageAuthor = 1;										/** NEED REAL CURRENT LOADED USER ID HERE  **/
		newImage.ImageDatePosted = new Date();							// today's date
		newImage.ImageSrc = imageSrc;									// Returned from php
		glaze.loadedVar.addImage(newImage);
		
		// Add new image to hoverGallery
		$('ul#thumbGallery').append(newImage.getHoverLi() );
		hoverGallery();
		
		// Set image to loaded image
		$('div#loadedImg img').attr('src', newImage.ImageSrc);
		
		// Close the upload dialog
		$('#dialog_imgUpload').find('.progressbar').empty();
		$('#dialog_imgUpload').dialog('close');
	} else {
		$('#img_loading').html(msg);	// display error message
	}
}	// end of imgUploadCallback

