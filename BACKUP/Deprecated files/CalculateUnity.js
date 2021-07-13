// Requires jsFunctions.js
// Script to calculate unity formula.

// Summary of Functions
// Function:			Param:								Return:									Description:
// calcUnity			recipe	(ingrID => ingrAmt)			oxideUnity (oxideID => unity amt)		MASTER FUNCTION	
// getUnityTable		recipe								html_unityTable							Returns HTML code for unity table
//
// calcPercentPW		recipe (ingrID => ingrAmt)			recipePW (ingrID => % PW)				1. Convert base ingredient amts to percentage PW. 
// calcingrIDME			recipePW (ingrID => ingrPW)			ingrIDME (ingrID => ingr ME)			2. Calculate ME of each ingredient. 	
// calcSumOfOxides		ingrIDME (ingrID => ingrME)			sumOfOxides (oxideID => OxideSumME)		3. Calculates the sum of each oxide in the glaze
// calcOxideUnity		sumOfOxides	(oxideID => OxideSumME)	oxideUnity (oxideID => unityAmt)		4. Calculates untiy amount for each oxide in the glaze


function calcUnity(stringRecipe) {
// Master Function to calculate unity
	// Converts recipe to numbers
	// (in javascript, all form inputs are strings, by default)
	var recipe = parseFloatAssoc(stringRecipe);
	
	var recipePW = calcPercentPW(recipe);			// 1. Convert base ingredient amts to percentage PW. 
	var ingrIDME = calcingrIDME(recipePW);			// 2. Calculate ME of each ingredient. ME = PW/(MW of ingredient)
	var sumOfOxides = calcSumOfOxides(ingrIDME);	// 3. Calculates the sum of each oxide in the glaze
	var oxideUnity = calcOxideUnity(sumOfOxides);	// 4. Calculates untiy amount for each oxide in the glaze
	
	return oxideUnity;
}

function getUnityTable(recipe) {
// Returns HTML code for unity table
	var oxideUnity = calcUnity(recipe);			// gets unity formula for each oxideID
	var oxideIDCat = <?=$js_oxideIDCat?>;		// get category(Flux, Flow, Glass) for each oxideID
	var oxideIDFormula = <?=$js_oxideIDFormula?>;
	
	var html = new String();
	var fluxTable = new String();
	var flowTable = new String();
	var glassTable = new String();
	html = "<table><!-- Unity table --> \n \t <tr>";			// Define umbrella table
	fluxTable = "<table><!-- flux sub-table -->\n";
	flowTable = "<table><!-- flow sub-table -->\n";
	glassTable = "<table><!-- glass sub-table -->\n";
	
	for (var oxideID in oxideUnity) {
		var unityAmt = oxideUnity[oxideID];
		// Validate unityAmt
		if (!(unityAmt > 0)) {	
			unityAmt = 0;
		}
		
		var type = oxideIDCat[oxideID];
		var formula = oxideIDFormula[oxideID];
		var tableAdd =  "\t <tr> \n";
		tableAdd +=		"\t\t <td>" + formula + "</td> <td> " + unityAmt.toFixed(2) + "</td> \n";
		tableAdd +=		"\t </tr> \n";
		switch (type) {
			case "Flux":
				fluxTable += tableAdd;
				break;
			case "Flow":
				flowTable += tableAdd;
				break;
			case "Glass":
				glassTable += tableAdd;
				break;
		}
	}
	
	// Finish up tables
	fluxTable += "</table><!-- end flux table -->";
	flowTable += "</table><!-- end flow table -->";
	glassTable += "</table><!-- end glass table -->";
	html +=  "\t \t <td> " + fluxTable + "</td>";
	html += "\t \t <td> " + flowTable + "</td>";
	html += "\t \t <td> " +  glassTable  + "</td>";
	html += "\t </tr> \n </table><!-- end unity table -->";
	
	return html;	
} // end getUnityTable
	

function calcPercentPW(recipe) {
// 1. Convert base ingredient amts to percentage PW. 
	var sumOfIngr = 0;
	var recipePW = new Array();
	
	// Adds each ingredient amt to sum
	for (var ingrID in recipe) {
		var ingrAmt = recipe[ingrID];
		sumOfIngr += ingrAmt;
	}
	
	// Calculates percent PW
	for (var ingrID in recipe) {
		var ingrAmt = recipe[ingrID];
		recipePW[ingrID] = (ingrAmt / sumOfIngr) * 100;
	}
	
	return recipePW;
}

function calcingrIDME (recipePW) {
//	2. Calculate ME of each ingredient. ME = PW/(MW of ingredient)
	var ingrIDME = new Array();
	
	// Creates array of all ingr Mol. weights
	// ingrMW[ingrID] = ingrMW
	var ingrMW = <?=$js_ingrMW?>;
	ingrMW = parseFloatAssoc(ingrMW);

	// Creates ingrIDME[ingrID] = ingr ME
	for(var ingrID in recipePW) {
		var PW = recipePW[ingrID];
		var MW = ingrMW[ingrID];
		ingrIDME[ingrID] = PW / MW;
	}
	
	ingrIDME = parseFloatAssoc(ingrIDME);		// Converts array from 'numbers' to numbers format
	return ingrIDME;
}

function calcSumOfOxides(ingrIDME) {
// Calculates the sum total of each oxide in the glaze recipe
	<?=$js_ingrOxideAmt?>					// Returns JS  array ingrOxideAmt[ingrID][oxideID] = oxideamt
	
	// Define sumOfOxides array, set each init. value to 0 (yes... this is necessary)
	sumOfOxides = new Array(); 				// sumOfOxides[oxideID] = total oxide amt in glaze
	for (var ingrID in ingrIDME) {	
		for (var oxideID  in ingrOxideAmt[ingrID]) {
			sumOfOxides[oxideID] = 0;
		}
	}
	
	for (var ingrID in ingrIDME) {							// loops through recipe ingredient MEs
		// Loop through oxides in this ingredient
		for (var oxideID  in ingrOxideAmt[ingrID]) {
			ingrOxideAmt[ingrID] = parseFloatAssoc(ingrOxideAmt[ingrID]); 	// Converts all 'numbers' to numbers
			var oxideAmt = ingrOxideAmt[ingrID][oxideID];
			oxideME = oxideAmt * ingrIDME[ingrID];							// Calculates ME of oxide for each ingredient
			sumOfOxides[oxideID] += parseFloat(oxideME);					// Adds the oxide ME for this ingredient to total
		}
	}
	
	return sumOfOxides;
} // end caclSumOfOxides

function calcOxideUnity(sumOfOxides) {
	var oxideIDCat = <?=$js_oxideIDCat?>;	// Returns oxideIDCat[oxideID] = oxide cat (Flux,Flow,Glass)
	
	// find sum of flux
	var fluxSum = 0;
	for (var oxideID in sumOfOxides) {
		oxideSum = sumOfOxides[oxideID];
		oxideCat = oxideIDCat[oxideID];
		if (oxideCat == "Flux") {
			fluxSum += oxideSum;
		}
	}
	
	// Divides each oxide by fluxSum to get unity amt
	oxideUnity = new Array();
	for (var oxideID in sumOfOxides) {
		oxideSum = sumOfOxides[oxideID];
		oxideUnity[oxideID] = oxideSum / fluxSum;
	}
	
	return oxideUnity;
} // end calcOxideUnity
