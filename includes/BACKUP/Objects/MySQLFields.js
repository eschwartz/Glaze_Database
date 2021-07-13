// JavaScript Document
/** 
 * Retrieves field names from a given table
 * and sets them as properties for the
 * MySQLFields Object using appropriate
 * data types.
 *
 * Can be used a quick protype for JS objects
 * modeled after mySQl database tables.
 *
 * REQUIRES JQUERY LIBRARY *
 * REQUIRES JSON (JQUERY) LIBRARY
*/

/**
 * MySQLFields constructor
*/
function MySQLFields(table) {
	var thisObj = this;				// To be used to allow scope of 'this' to enter ajax callback function (!WOW!)
	
		var ajaxFile = "/includes/Objects/MySQLFields.ajax.php";
		$.post(ajaxFile, {'table': table}, function(res) {
			var mySQLFields = $.evalJSON(res);
			for (var field in mySQLFields) {
				var fieldName = mySQLFields[field][0];
				var fieldType = mySQLFields[field][1];
				if 	(fieldType.search(/tinyint/i) >= 0) 	{	thisObj[fieldName] = new Boolean(); }	// Default: false 
				else if (fieldType.search(/int/i) >= 0) 	{	thisObj[fieldName] = new Number();	}	// Default: 0  
				else if (fieldType.search(/float/i) >= 0) 	{ 	thisObj[fieldName] = new Number();	}	// Default: 0  
				else if (fieldType.search(/double/i) >= 0) 	{ 	thisObj[fieldName] = new Number();	}	// Default: 0  
				else if (fieldType.search(/decimal/i) >= 0) { 	thisObj[fieldName] = new Number();	}	// Default: 0 
				else if (fieldType.search(/date/i) >= 0) 	{	thisObj[fieldName] = new Date();	}	// Default: [today's date]
				else										{	thisObj[fieldName] = new String();	}	// Default: "" (or empty?)
			}
		});	// end of ajax callback
}	// end of MySQLFields constructor
