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

var glaze = new Glaze(1);		// Set glazeID to 1 for TESTING ONLY
glaze.loadGlaze();
glaze.loadGlazeVariations();

$(document).ready(function() {
						   
						   
// TESTING BUTTON
$('button#testBtn').click(function() {
	//console.log(glaze);
	glaze.getUnityTable();
});

/** 
 * BUTTON EVENTS
*/
$('#saveGlaze').click(function() {
	glaze.saveGlaze();
});


// Sets section collapse for highlight-column sections
var collapseBtn = $('div#highlight-column div.section').find('div.sectionHead');		// Section head in highlight-column
collapseBtn.click(function() {
	var sectionContent = $(this).siblings('div.sectionContent');
	sectionContent.animate({
		height: 'toggle'
	}, 250);
});

/** 
 * INGREDIENT AUTOCOMPLETE
 */
var ingrName = new String();								// define here, so 'close' event can access
$('#new_baseIngr').autocomplete({
	source: '/includes/AJAX/ingrAutoComplete.php',
	minLength: 2,											// Number of characters entered before suggestions appear
	autofocus: true,										// starts with focus on first list item
	select: function(event, ui) {
		ingrName = ui.item.label;							// so 'close' event can access
		var ingrID = ui.item.value;
		if (glaze.addIngr(ingrID, ingrName, 0) ) {			// add ingredient to glaze object. Retunrs FALSE if duplicate
			ingrToTable(glaze.lastIngr() );					// add ingredient to recipe table
		} else { return false; }
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

/**
 * Load Base Recipe into table
*/
for(i=0; i<glaze.recipe.length; i++) {
	ingrToTable(glaze.recipe[i]);
}
						   
/**
 * Set glaze details in highlight-column
 */
$('table#glazeDetails div#details_name') .html(glaze.defaultVar.VarName);
$('table#glazeDetails div#details_color') .html(glaze.defaultVar.VarColor);
$('table#glazeDetails div#details_surface') .html(glaze.defaultVar.VarSurface);
$('table#glazeDetails div#details_author') .html(glaze.defaultVar.VarAuthor);
$('table#glazeDetails div#details_datePosted').html(shortDate(glaze.GlazeDatePosted));		// jsFunctions.js => returns formatted date (ie. May 5, 2011)
truncateStr(glaze.defaultVar.VarDescr, 100, $('table#glazeDetails div#details_descr') );	// Show truncated description, if too long:

/**
 * Display list of variations
 */
var varsTable = $('div#div_vars table#table_vars tbody');
for(i=0; i<glaze.variations.length; i++) {
	varsTable.append($('<tr>')
	   .append($('<td>')
			// EVENT: Click on variation --> load variation to page
			.append($("<a href='#'>"+glaze.variations[i].VarColor+"</a>").click(function(index) {
					return function() {					// CLOSURE																							 
						glaze.loadedVar = glaze.variations[index];
						displayVariation(glaze.loadedVar);
					}
				}(i))
			)
		)
	)	
}

});	// end of document.ready function

/**
 * Adds the ingredient to the recipe table
 */
function ingrToTable(ingrObj) {
	var newIngrRow = $('table#table_recipe tbody tr.new_ingrRow');			

	var cell_name = $("<td>"+ingrObj.IngredientName+"</td>");
	// EVENT: Change Ingr Amt --> update glaze Object
	var input_amt = $("<input size=6 value='"+ingrObj.IngredientAmt+"' />").keyup(function() {
		ingrObj.IngredientAmt = $(this).val();
	});
	var cell_amt = $("<td></td>").append(input_amt);
	var ingrRow = $('<tr></tr>').append(cell_name).append(cell_amt);
	ingrRow.insertBefore(newIngrRow);
	
	// Set focus on amount input
	cell_amt.find('input').focus();
}	// end of newIngredient

/**
 * Displays the specified Glaze Variation object
 * on the page.
 */
function displayVariation(varObj) {
}	// end of displayVariation