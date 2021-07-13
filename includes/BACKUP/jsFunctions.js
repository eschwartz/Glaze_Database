/** Useful Javascript functions

 Function	:				Params:									Description:
 * alertAssoc				assoc									Alerts values an assoc array as "key: value"
 * addSelectOption			selectObj, text, value, isSelected		Adds an <option> element to a specified List box
 * filterSelectOptions		selectObj, filter, filterby				Removes <option> elements that do not match a filter. filterby="value" or "text" (default)
 * parseFloatAssoc			assoc									Converts array of strings to array of float/number. Useful for converting form inputs.
 * assocLength				assoc									Checks the "length" of an assoc. array (actually, # of properties to the object)
 * loadLightBox				jquery 'a' selector						Sets lightbox for a selector link.
 * buildHoverDescr			title, descr, color, surface, author	Returns a string: HTML for hoverGallery description (to put in img alt="...)
 * setParamDefault			param, defaultValue						Used in a function to set the default value of a parameter
 * shortDate				dateObj									Returns a short date string. eg April 4, 2011
 * roundDecimal				number, X								rounds number to X decimal places, defaults to 2
 * truncateStr				str, maxLen, el							Truncates sting to maxLen, and creates "see more/less" buttons that toggle length (REQ JQUERY)
 * asyncLoading             asyncFn, callbackFn, el_loading, int, failInt		Handles aysc calls (eg. call to ajax functions in Glaze Object
 * $_GET					param, [string]							Returns $_GET variable from URL location (or from a given URL string) using window.location
 * strToSub				str										Formats all numbers in a string as subscripts. Useful for chemical formulas
*/

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

function updateUnity(recipe, tbl_selector) {
// sends recipe to calculateUnity.php via AJAX
// returns unity

	// Only send valid >0 ingrAmts to php (prevents errors and frees server memory)
	// Create a validRecipe array
	var validRecipe = getValidAssoc(recipe);
	
	// Only runs AJAX if validRecipe is not empty
	if (assocLength(validRecipe) > 0) {
		var ajaxFile = "/includes/DYLMG_calculateUnity.php";
		var dataString = $.toJSON(validRecipe);
			
		$.post(ajaxFile, 'data=' + dataString, function (res) {
			// var unityOxide = $.evalJSON(res); 	// Use when recieving $unityOxide (oxideID => unity amt) array from php
			$(tbl_selector).html(res);
		});
	}
}

function alertAssoc(assoc) {
// Alerts Values an assoc array	
// For more info on Javascript faux-associative arrays: 
// http://blog.persistent.info/2004/08/javascript-associative-arrays.html

		
	var listString = "KEY: VALUE \n";
	for (var key in assoc) {
		listString += key + ': ' + assoc[key] + '\n'; 
	}
	
	alert(listString);
}

function alert3dArray(array) {
// Alerts keys and values of a 3d array
// For more, see: http://www.devx.com/tips/Tip/12455
	var listString = "KEY1: KEY2: VALUE \n";
	for (var key1 in array) {
		for (var key2 in array[key1]) {
			var value = array[key1][key2];
			listString += key1 + ': ' + key2 + ': ' + value + ' \n';
		}
	}
	
	alert(listString);
}

function addSelectOption(selectObj, text, value, isSelected, index) {
// Adds an <option> element to a specified List box
// From: http://stevenharman.net/blog/archive/2007/07/10/add-option-elements-to-a-select-list-with-javascript.aspx
	// Sets index default to end of list
	if (!index || index == "" || index == null) {
		index = selectObj.options.length;
	}
	
	var newOption;
    if (selectObj != null && selectObj.options != null) {
        selectObj.options[selectObj.options.length] = 
			
            newOption = new Option(text, value, false, isSelected);
    }
	
	return newOption;
} // end of addSelectOption

function filterSelectOptions(selectObj, filter, filterby) {
// Filters options in a list box
// Permanently removes non-matching option
// Will want to recreate options list before calling a 2nd time (to prevent compounding removal)

	var regexp = new RegExp(filter, "i");
	var nonMatch = new Array();
	for (i=0; i<selectObj.options.length; i++) {
		// Compare filter to option text, or value? Default = text
		if (filterby == "value") {							
			var compare = selectObj.options[i].value;
		}
		else {
			var compare = selectObj.options[i].text;
		}
		
		// Cannot remove element in for loop: changes length
		// Create array of non-matching indices, then remove at end
		if (!compare.match(regexp)) {
			nonMatch.push(i);			
		}
	}
	
	// removes nonMatching <option> element 
	for (i=0; i<nonMatch.length; i++) {
		index = nonMatch[i]-i;					// "-i" because options index decreases with each nonMatch removed
		selectObj.remove(index);
	}
	
} // end of filterSelectOptions

function parseFloatAssoc(assoc) {
	floatAssoc = new Array();
	for (var stringKey in assoc) {
		var stringValue = assoc[stringKey];
		if (stringValue == "") { // sets "" to 0
			stringValue = 0;
		}
		var floatKey = parseFloat(stringKey);
		var floatValue = parseFloat(stringValue);
		floatAssoc[floatKey] = floatValue;
	}
	
	return floatAssoc;
} // end of parseFloatAssoc

function assocLength(assoc) {
//Checks the "length" of an assoc. array (actually, # of properties to the object)
	var length = 0;
	for(var key in assoc) {
		length++;
	}
	
	return length;
} // end of assocLength

function loadLightBox(selector) {
		$(selector).lightBox({
		imageLoading: '/includes/lightbox/images/loading.gif',
		imageBtnClose: '/includes/lightbox/images/close.gif',
		imageBtnPrev: '/includes/lightbox/images/prev.gif',
		imageBtnNext: '/includes/lightbox/images/next.gif'
		});
}	// end of loadLightBox

function setDefault(param, defaultValue) {
// Returns defaultValue if param is null, empty, or undefined
	if (param == null || param == "" || param == undefined) {
		return defaultValue;
	}
	else {
		return param;
	}
}	// end setParamDefault

function buildHoverDescr(glaze) {
// Returns a string: HTML for hoverGallery description (to put in img alt="...)	
	// Set defaults
	glazeID = setDefault(glaze['GlazeID'], false);
	title = setDefault(glaze['VarName'], "(no name)");
	descr = setDefault(glaze['VarDescr'], "");
	color = setDefault(glaze['VarColor'], "color not specified");
	surface = setDefault(glaze['VarSurface'], "surface not specified");
	authorLink = setDefault(glaze['UserID'], "#");
	authorLogin = setDefault(glaze['UserLogin'], "(unknown)");
	
	var titleHTML = "";
	if (glazeID == false) {	// No glazeID set, Don't use link! (eg. glaze images in postglaze.php
		titleHTML = "<b>"+title+"</b>";
	} else {
		titleHTML = "<a href=/glazes/DYLMG_postGlaze.php?glazeID="+glazeID+">"+title+"</a>";		// Link to this glaze
	}
	
	var hoverDescr = 	"<div class=title>"+titleHTML+"</div>";
    hoverDescr += 		"<div class=descrText>"+descr+"</div>";
    hoverDescr += 		"<div class=color>"+color+"</div>";
    hoverDescr += 		"<div class=surface>"+surface+"</div>";
    hoverDescr += 		"<div class=\"author ui-state-highlight ui-corner-bottom\">Posted by <a href=/usr/userProfile.php?userID="+authorLink+">"+authorLogin+"</a></div>";

	return hoverDescr;
}	// end of buildHoverDescr

function shortDate(dateObj) {
	var date = new Date(dateObj);	// to ensue variable is of correct data type
	//Returns a short date string. eg April 4, 2011
	var months = {
		0:'Jan.', 1:'Feb.',	2:'Mar.',
		3:'Apr.', 4:'May', 5:'Jun.',
		6:'July', 7:'Aug.', 8:'Sep.',
		9:'Oct.', 10:'Nov.', 11:'Dec'
	};
	
	var strMonth = months[date.getMonth()];
	var shortDate = strMonth+" "+date.getDate()+", "+date.getFullYear();
	return shortDate;
}

function roundDecimal(number,X) {
// rounds number to X decimal places, defaults to 2
    X = (!X ? 2 : X);
    return Math.round(number*Math.pow(10,X))/Math.pow(10,X);
}

function truncateStr(str, maxLen, el) {
// Truncates sting to maxLen, and creates "see more/less" buttons that toggle length (REQ JQUERY)
// 'el' defines element where truncated string should be placed
	if (str.length > maxLen) {
		var toggleLink = $("<a href='#'>...see more</a>").toggle(function() {
			$(this).html("...see less");
			el.find('p').html(str);
		}, function() {
			$(this).html("...see more");
			el.find('p').html(str.slice(0,maxLen-1) );
		});
		
		// Start with short str showing
		el.html("<p style='margin:0px; display:inline;'>"+str.slice(0,maxLen-1)+"</p>").append(toggleLink);
	} else {
		el.html(str);
	}
}

/**
 * HANDLE ASYNC LOADING 
 * (eg. ajax calls running in GlazeObject.js)
 * Calls a function with an ajax call
 * Runs callback when ajaxFn.dirty = false
 * Checks every 'int' milliseconds.
 * Shows "loading.gif" in el_loading.
 *
 * If el_loading is a string,
 * will show div#bottomMsg with text
*/
function asyncLoading(asyncFn, callbackFn, el_loading, int, failInt, dirtyFn) {
	// Sets the interval before timing out
	failInt = (failInt == null)? 10000: failInt;
	
	// OPTIONAL: Can select which function to check for dirty status
	dirtyFn = (dirtyFn == null)? asyncFn: dirtyFn;
	
	// Show loading Gif
	// Display in specified div
	var elHeight = (el_loading.height() < 20)? '80px': el_loading.height();						// Sets loading div height to original size (min 20px)
	var elWidth = (el_loading.width() < 20)? '80px': '100%';									// sets min width for loading div (else, empty div will not show img)
	el_loading.empty().append( $('<div>').addClass('loading').height(elHeight).width(elWidth) )
	
	// Run ajaxFn
	asyncFn();																					
	
	var sumInt = 0;
	var checkDirty = setTimeout(function() {
		if (!dirtyFn.dirty) {
			el_loading.html("");																// Remove 'loading.gif'
			if(callbackFn) { callbackFn() };													// Run callback function (if defined)
			el_loading.height('auto');															// Sets height back to auto
		} else {
			// Check for time out (ajax failed...)
			sumInt += int;	
			if (sumInt >= failInt) {															// TIME OUT
				el_loading.find('div').removeClass('loading').addClass('loadFail');				// Show Fail image
				return false;
			} else {  setTimeout(arguments.callee, int); }										// Runs timer again if function is still dirty (LOOP)
		}
	}, int);
}

/** 
 * Just like asyncLoading()
 * but displays a bottom message
 * instead of a loading gif
*/
function asyncMsg(asyncFn, msg, failInt) {
	var int = 300;									// Time between setTimout calls
	failInt = (failInt == null)? 10000: failInt;
	
	// Show msg
	$('div#bottomMsg').html(msg).fadeIn(100);
	
	// Run ajaxFn
	asyncFn();
	
	var sumInt = 0;
	var checkDirty = setTimeout(function() {
		if (!asyncFn.dirty) {
			$('div#bottomMsg').fadeOut(400);		// Remove 'Saving...' Message (slowly...)
		} else {
			// Check for time out (ajax failed...)
			sumInt += int;	
			if (sumInt >= failInt) {															// TIME OUT
				$('div#bottomMsg').html('Error!');
				return false;
			} else {  setTimeout(arguments.callee, int); }										// Runs timer again if function is still dirty (LOOP)
		}
	}, int);
}

function $_GET(q,s) { 
        s = s ? s : window.location.search; 
        var re = new RegExp('&'+q+'(?:=([^&]*))?(?=&|$)','i'); 
        return (s=s.replace(/^\?/,'&').match(re)) ? (typeof s[1] == 'undefined' ? '' : decodeURIComponent(s[1])) : undefined; 
}  	// end GET function

function strToSub(str) {
	//Formats all numbers in a string as subscripts. Useful for chemical formulas
	var subdArray = new Array()
	for(var char in str) {
		if ( str[char].match(/\d+/) ) {						// is it a number?
			var subNo = "<sub>"+str[char]+"</sub>";
			subdArray.push(subNo);
		} else {
			subdArray.push(str[char]);
		}
	}
	
	return subdArray.join("");
} // end stringToSub