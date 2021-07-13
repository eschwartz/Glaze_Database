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
$.manageAjax.create('loadObjects', {queue: true});

function Glaze(id) {
	var thisObj = this;			// To prevent any confusion about what 'this' we are talking about
		
	// Set default properties
	// Use mySQL fieldnames as object properties
	thisObj.GlazeID = (!id)?-1: id;			// Default is -1 (new glaze)
	thisObj.GlazeAuthor = 0;
	thisObj.GlazeDatePosted = new Date();	// default: today
	thisObj.recipe = new Array();
	thisObj.variations = new Array();
	thisObj.defaultVar = new Object();		// will be set in Glaze.loadGlaze() (same as Glaze.variations[0])
	thisObj.loadedVar = 0;					// Sets which variation is currently loaded on the page. 
	
	thisObj.setID = function(id) {
		thisObj.GlazeID = id;
	}
	
	thisObj.setProp = function(prop, value) {
		// Sets any property for thisObj
		thisObj[prop] = value;
	}
	
	thisObj.getProp = function(prop) {
		// Returns the vaule of the given property
		return thisObj[prop];
	}
	
	thisObj.addIngr = function(ingrID, ingrName, ingrAmt) {
		// Adds a new ingredient to the glaze.recipe object
		// Returns FALSE if ingredient already exists
		
		// Check if Ingredient already exists
		var duplicate = false;
		for (var i in thisObj.recipe) {
			if (thisObj.recipe[i].IngredientID == ingrID) {
				duplicate = true;
			}
		}
		
		// Adds a new ingredient to the Glaze.recipe[i] object
		if (!duplicate) {
			var newIngr = new Object({'IngredientAmt': ingrAmt, 'IngredientID': ingrID, 'IngredientName': ingrName});
			thisObj.recipe.push(newIngr);
			return true;
		}
		else { return false; }
	}
	
	thisObj.lastIngr = function() {
		// Returns the last ingredient in the recipe (object)
		return thisObj.recipe[thisObj.recipe.length-1];
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
		var data = $.toJSON({action: 'loadGlaze', GlazeID: thisObj.GlazeID});
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
		var ajaxFile = '/includes/Objects/GlazeObject.ajax.php';
		var data = $.toJSON({action: 'saveGlaze', glazeObj: thisObj});
		$.post(ajaxFile, {'data':data}, function(res) {
			 console.log(res);
		});
	}	// end of saveGlaze
	
	thisObj.getUnityTable = function () {
		var ajaxFile = '/includes/AJAX/calculateUnity.ajax.php';
		var data = $.toJSON({recipe: thisObj.recipe});
		$.post(ajaxFile, {'data': data}, function(res) {
			console.log(res);									  
		});
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
