// JavaScript Document

/** 
 * Glaze Object Class Definition
 * For use with DYLMG Glaze Database
 * By Edan
 * 2011
*/ 

/**
 * REQUIRES:
 *	Jquery
 *	JSON for Jquery
 *	MySQLFields.js
 *	Jquery Ajax Manager plugin
 */
 
/**
 * Create ajax queue
 * with global scope
*/
// Queue ajax calls for loadGlaze -> loadVariation -> loadImage

function Glaze(id) {
	var thisObj = this;			/* To prevent any confusion about what 'this' we are talking about
								 * Necesary for AJAX & anonymous functions
								 * HOWEVER, this will mess up any functions used by Variation prototype
								 * Use 'this' for anything that should be extended to Variation object
								*/
		
	// Set default properties
	// Use mySQL fieldnames as object properties
	thisObj.GlazeID = (!id)?-1: id;			// Default is -1 (new glaze)
	thisObj.GlazeAuthor = 0;
	thisObj.GlazeDatePosted = new Date();	// default: today
	this.recipe = new Array();
	thisObj.variations = new Array();
	thisObj.defaultVar = new Object();		// will be set in Glaze.loadGlaze() (same as Glaze.variations[0])
	thisObj.loadedVar = 0;					// Sets which variation is currently loaded on the page. 
	thisObj.unity = new Object();
	
	this.setID = function(id) {
		this.GlazeID = id;
	}
	
	this.setProp = function(prop, value) {
		// Sets any property for object
		this[prop] = value;
	}
	
	this.getProp = function(prop) {
		// Returns the vaule of the given property
		return this[prop];
	}
	
	this.addIngr = function(ingrID, ingrName, ingrAmt) {
		// Adds a new ingredient to the glaze.recipe object
		// Returns FALSE if ingredient already exists
		
		// Check if Ingredient already exists
		var duplicate = false;
		for (var i in this.recipe) {
			if (this.recipe[i].IngredientID == ingrID) {
				duplicate = true;
			}
		}
		
		// Adds a new ingredient to the Glaze.recipe[i] object
		if (!duplicate) {
			var newIngr = new Object({'IngredientAmt': ingrAmt, 'IngredientID': ingrID, 'IngredientName': ingrName});
			this.recipe.push(newIngr);
			return true;
		}
		else { return false; }
	}
	
	this.removeIngr = function(ingrObj) {
		// Removes specified ingredient object from Glaze
		newRecipe = new Array();											// Empty recipe array
		// Add all ingredients to the new recipe, except deleted ingr
		for (var i in this.recipe) {
			if(this.recipe[i].IngredientID != ingrObj.IngredientID) {
				newRecipe.push(this.recipe[i]);
			}
		}
		
		this.recipe = newRecipe;
		/* BTW: cannnot use 'delete this.recipe
		 * JS seems to only delete recipe[i] properties, leaving an empty object
		 * which messing up SQL statments
		*/
	}
	
	this.lastIngr = function() {
		// Returns the last ingredient in the recipe (object)
		return this.recipe[this.recipe.length-1];
	}
	
	thisObj.loadGlaze = function() {
		/**
		 * Loads properites of glaze with values
		 * from mySQL database. 
		 * Loads a list of glaze variations, and sets 
		 * each as an object in the array thisObj.variations[i].
		*/
		// Glaze ID required to load base recipe
		if (!thisObj.GlazeID)	{
				alert('no glaze id set');
			return false;
		}
		
		var ajaxFile = '/includes/Objects/GlazeObject.ajax.php';
		var data = $.toJSON({action: 'loadGlaze', glazeObj: thisObj});
		$.ajax({
			type: "POST", 
			url:ajaxFile,
			async: false,							// So that Glaze.Variation[i].loadVariation() AJAX won't run until GlazeVar list is loaded.
			datatype: "text",
			data: {'data': data}, 
			success: function(res) {
				var glazeRes = $.evalJSON(res);
				
				// Update Glaze object properties with AJAX results from database
				for (var prop in glazeRes['glazeObj']) {
					thisObj[prop] = glazeRes['glazeObj'][prop];
				}
				
				// Creates Variation objects for each varation in the Glaze
				thisObj.variations = new Array();		// Clears variations array (to prevent stacking)
				for (var i in glazeRes['varList']) {
					var varID = glazeRes['varList'][i];
					var newVariation = new Variation(varID);
					thisObj.variations.push(newVariation);
					thisObj.defaultVar = thisObj.variations[0];		// Default variation is set using ORDER BY in sql
				}
			}	// end of ajax callback
		});	// end of ajax 
	}	// end of loadGlaze
	
	thisObj.loadGlazeVariations = function() {
		/**
		 * Loads info from mySQL database
		 * for all variations of this glaze
		*/
		for(i=0; i<thisObj.variations.length; i++) {
			thisObj.variations[i].loadVariation();
		}
	}	// end of loadVariations
	
	thisObj.saveGlaze = function() {
		/**
		 * Saves entire glaze object to mySQL database
		 * including recipes, variations, images, etc.
		*/
		
		thisObj.saveGlaze.dirty = true;
		
		var ajaxFile = '/includes/Objects/GlazeObject.ajax.php';
		var data = $.toJSON({action: 'saveGlaze', glazeObj: thisObj});
		$.post(ajaxFile, {'data':data}, function(res) {
				console.log(res);
			thisObj.saveGlaze.dirty = false;
		});
	}	// end of saveGlaze
	
	thisObj.getUnity = function () {
		var thisFn = thisObj.getUnity;
		thisFn.dirty = true;										// Signals that AJAX call is in process
		var ajaxFile = '/includes/AJAX/calculateUnity.ajax.php';
		var data = $.toJSON({recipe: thisObj.recipe});
		$.post(ajaxFile, {'data': data}, function(res) {
			thisObj.unity = $.evalJSON(res);
			thisFn.dirty = false;									// Signals that AJAX call is complete
		});
	}
	
	thisObj.getUnityTable = function () {
		// Creates a quick unity table from Glaze.unity
		
		var fluxTable = $('<table></table>'); var flowTable = $('<table></table>'); var glassTable = $('<table></table>');
		for(var i in glaze.unity) {
			// Assign each oxide to a category table
			switch(glaze.unity[i].OxideCat) {
				case 'Flux': var myTable = fluxTable; break;
				case 'Flow': var myTable = flowTable; break;
				case 'Glass': var myTable = glassTable; break;
				default: var mytable = fluxTable; 
			}
			
			// Checks for 'trace' oxides
			if (glaze.unity[i].OxideAmt >= 0.005) {
				var oxideAmt =  new Number(glaze.unity[i].OxideAmt);								// Ensures the oxideAmt is number not a "number"
				oxideAmt = oxideAmt.toFixed(2);
			} else {
				var oxideAmt ="<i>trace</i>";
			}
			var cell_formula = $('<td></td>').html(glaze.unity[i].OxideFormula);					// Create formula cell
			var cell_amt = $('<td></td>').html(oxideAmt);											// Create Amt cell
			myTable.append(
				$('<tr></tr>').append(cell_formula).append(cell_amt)
			);		// Add cells to table row
		}
		
		// add each category table to unity table
		var unityTable = $('<table></table>').addClass('unityTable').append(
			 $('<tr>').append( $('<td>').append(fluxTable)  )
					  .append( $('<td>').append(flowTable)  )
					  .append( $('<td>').append(glassTable) )
		 );

	return unityTable;
	}
}	// end of Glaze 

function Variation(varID) {
	var thisObj = this;
	
	thisObj.VarID = varID;
	thisObj.recipe = new Array();
	thisObj.images = new Array();	// and array of VarImage objects
	
	thisObj.loadVariation = function() {
		/**
		 * Loads Variation info and recipe 
		 * from mySQL database
		 * Loads a list of images, and sets each as
		 * an object in array -> thisObj.images[i]
		*/
		var ajaxFile = '/includes/Objects/GlazeObject.ajax.php';
		var data = $.toJSON({action: 'loadVariation', VarID: thisObj.VarID});
		$.ajax({
			type: "POST", 
			url:ajaxFile,
			async: false,							// So that Variation.VarImage.loadImage() AJAX won't run until GlazeVar list is loaded.
			data: {'data': data}, 
			success: function(res) {
				var varRes = $.evalJSON(res);
				
				// Update Variation properties with AJAX results from database
				for (var prop in varRes['varObj']) {
					thisObj[prop] = varRes['varObj'][prop];
				}
				
				// Creates Image objects for each image associated with the Variation
				thisObj.images = new Array();	// clears image list (to prevent stacking)
				for (var i in varRes['imageList']) {
					var imageID = varRes['imageList'][i];
					var newImage = new VarImage(imageID);
					thisObj.images.push(newImage);
				}
			}	// end of callback
		});
	}	// end of loadVariation
	

	thisObj.loadVarImages = function() {
		/**
		 * Loads all images for this variation
		*/
		for(i=0; i<thisObj.images.length; i++) {
			thisObj.images[i].loadVarImage();
		}
	}
}
// Loads SQL Fields in 'GlazeVars' table
// as properties of the Variation object
Variation.prototype = new MySQLFields('GlazeVars');
Variation.prototype = new Glaze();

function VarImage(imageID) {
	var thisObj = this;
	
	thisObj.ImageID = imageID;
	
	thisObj.loadVarImage = function() {
		/**
		 * Loads image info from mySQL database
		*/
		var ajaxFile = '/includes/Objects/GlazeObject.ajax.php';
		var data = $.toJSON({action:'loadVarImage', ImageID: thisObj.ImageID});
		$.post(ajaxFile, {'data':data}, function(res) {
			var imgRes = $.evalJSON(res);
			
			// Update image with properties from database
			for (var prop in imgRes['imgObj']) {
				thisObj[prop] = imgRes['imgObj'][prop];
			}
		});	// end of ajax callback
	}	// end of loadVarImages
}
VarImage.prototype = new MySQLFields('Images');
