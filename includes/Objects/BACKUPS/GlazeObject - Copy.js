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
 *	ajaxQueue
 */

function Glaze(id) {
	var thisObj = this;			// To prevent any confusion about what 'this' we are talking about
		
	// Set default properties
	// Use mySQL fieldnames as object properties
	thisObj.GlazeID = (!id)?-1: id;			// Default is -1 (new glaze)
	thisObj.GlazeAuthor = 0;
	thisObj.GlazeDatePosted = new Date();	// default: today
	thisObj.recipe = new Array();
	thisObj.variations = new Array();
	
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
		$.post(ajaxFile, {'data': data}, function(res) {
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
		});	// end of ajax callback
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
			var saveRes = $.evalJSON(res);
			alertAssoc(saveRes);
		});
	}	// end of saveGlaze
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
		$.post(ajaxFile, {'data': data}, function(res) {
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
