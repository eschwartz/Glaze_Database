<?php
include_once('../includes/DYLMG_SuperHTML.php');
include_once('../includes/SuperJS.php');
include_once('../includes/CalculateUnity.js.php');
?>
<?php
$ingrIDName = createIDValue('IngredientID', 'IngredientName', 'Ingredients');
$ingrCount = count($ingrIDName);
$sjs = new SuperJS();

// Seems like we have to make all SuperJS calls BEFORE js code
$jsIngrIDName = $sjs->getPHPAssoc($ingrIDName);
$js = <<<HERE
var ingrIDName = $jsIngrIDName;		// Returns a JS assoc array equiv. to $ingrIDName = ingrIDName[ingrID] = name
var recipeIngr = new Object(); 		// recipeIngr[ingrID] = amt

function buildIngrList() {
// Creates ingr ListBox options from SQL Ingredients Query	
	var select_ingrID = document.getElementById('select_ingrID');
	select_ingrID.innerHTML = "<option value='default' selected='selected'>Select an Ingredient</option>";
	
	// Add an <option> element for each ingredient
	// addSelectOption(selectObj, text, value, isSelected)
	for (var ingrID in ingrIDName) {
		var ingrName = ingrIDName[ingrID]
		var ingrOption = addSelectOption(select_ingrID, ingrName, ingrID, false);			// addSelectOption(selectObj, text, value, isSelected)
		ingrOption.setAttribute('ondblclick', 'addIngr(this)');		// sets dblclick event to add the ingredient to the recipe table
	}
} // end JS getIngrListBox

function filterIngrList() {
// Filters ingr ListBox options by search text
	buildIngrList();		// Rebuilds list, so filtering is not compounded
	
	var select_ingrID = document.getElementById('select_ingrID');
	var ingrFilter = document.getElementById('input_ingrFilter').value;
	filterSelectOptions(select_ingrID, ingrFilter, 'text');					// filterSelectOptions(selectObj, filter, filterby['text' or 'value']
																											
} // end filterIngrList

function addIngr(option_ingr) {
// Adds the selected ingredient to the recipeIngr array,
// Then rebuilds the recipe table from the array
	var ingrID = option_ingr.value;
	
	// Check if ingr exists, then adds
	if (!recipeIngr[ingrID]) {
		recipeIngr[ingrID] = 0;		// Sets initial amount to 0
		buildRecipeTable();
	}
}

function buildRecipeTable() {
// Clears and rebuilds recipe table from recipeIngr array
	var table_recipe = document.getElementById('table_recipe');
	
	// Clear table, before rebuilding
	var emptyTable = new String();
	emptyTable = "<thead><td><b>Ingredient</b></td><td>Amount</td></thead>";
	table_recipe.innerHTML = emptyTable;

	
	for (var ingrID in recipeIngr) {
		var lastRow = table_recipe.rows.length;
		var newRow = table_recipe.insertRow(lastRow);		// Inserts a row at the end of the recipe table
		var cell_ingrName = newRow.insertCell(0);		// Inserts cell for ingrName
		var cell_ingrAmt = newRow.insertCell(1);		// Inserts cell for ingrAmt
		
		cell_ingrName.innerHTML = "<b>" + ingrIDName[ingrID] + "</b>";		// Outputs ingredient name
		cell_ingrAmt.innerHTML = "<input type='text' name='" + ingrID + "' value='" + recipeIngr[ingrID] + "' onkeyup='updateAmt(this)' />";					// Outputs ingredient amount
	}
	
	updateUnity();
}

function updateAmt(input_amt) {
// Updates ingrRecipe array with new amount
	var ingrID = input_amt.name;
	var ingrAmt = input_amt.value;
	recipeIngr[ingrID] = ingrAmt;
	
	updateUnity();
}

function updateUnity() {
// Updates values in unity table
	var unityTable = getUnityTable(recipeIngr);
	document.getElementById('unityTable').innerHTML = "<h3>Unity Table</h3>" + unityTable;
}
HERE;
// End $js

$s = new DYLMG_SuperHTML('Submit a Glaze');
$s->addJSLink('../includes/jsFunctions.js');
$s->addJS($js);
$s->addBodyParam('onload=buildIngrList()');
$s->buildTop();


$body = <<<HERE

<b>Search</b>
<input type='text' name='ingrFilter' id='input_ingrFilter' onkeyup='filterIngrList()' /><br>

<h3>Ingredient List</h3>
<div style="float:left; width:200px">
<select name='ingrID' size='$ingrCount' id='select_ingrID' style='width:175px; height:400px;'>
<option value='default' selected='selected'>Select an Ingredient</option>
</select>
</div>
<div style="float:left;">
<table id="table_recipe" width="400px">
	<thead> 
		<td width='200px'>Ingredient</td>
		<td width='100px'>Amount</td>
	</thead>
</table>
</div>

<div id='unityTable' style="float:left" >
<h3>Unity table</h3>
</div>
			
HERE;
// end of body HTML

$s->addText($body);

$s->buildBottom();
print $s->getPage();