<?php
include('../includes/DYLMG_SuperHTML.php');
?>
<?php
function ingrOptions() {
	// Creates Options list of of Ingredients
	// From SQL query of Ingredients tbl
	$sql = "SELECT IngredientID, IngredientName\n"
			. "FROM Ingredients";
	$rs = quickQuery($sql);
	
	$ingOptions = "<option value='none' selected='selected'>--Select an Ingredient--</option>";	// Selected by default. JS will change 'selected' for new ingrs
	
	while ($row = mysql_fetch_array($rs)) {
		$ingOptions .= "<option value='$row[IngredientID]'>$row[IngredientName]</option>";
	}
	
	return $ingOptions;
}
$ingrOptions = ingrOptions();	// Defines dropdown list of ingrOptions.
								// Could probably use SuperHTML method instead...
?>

<?php
$js = <<<HERE
	// Begin JS Code
	var ingrArry = new Object();
	
	function validateIngr() {
		var IngrID = document.getElementById("select_newIngrID").value;
		var ingrAmt = document.getElementById("input_newIngrAmt").value;
		
		var validationText = "";
		if (isNaN(ingrAmt) || ingrAmt == 0 || ingrAmt == "") {		//checks for a number not 0
				alert("Please enter a valid amount");
				return false;	// For presubmission check, won't submit invalid info
		}
		else if (ingrArry[IngrID] != undefined) {						// Checks in name already exists as a key in the ingrArry. ingrArry is updated in addIngr()
				alert("Your glaze recipe already contains this ingredient");
				return false;
		}
		else {	
			addIngr();
		}
	}
	
	
	function addIngr() {	
		// Create a new row BEFORE the newIngr row, set values to submitted ingr
		var table_addIngr = document.getElementById("table_addIngr");
		var ingrCount = table_addIngr.rows.length - 2;
		
		var row_lastIngr = table_addIngr.insertRow(ingrCount);				// Adds lastIngr row at index of ingrCount
		var cell_lastIngrID = row_lastIngr.insertCell(0);					// Adds IngrID cell to lastIngr row
		var cell_lastIngrAmt = row_lastIngr.insertCell(1);					// Adds ingrAmt cell to last Ingr row
		
		// Inserts submitted ingr in new row
		var ingrID_new = document.getElementById("select_newIngrID").value;
		var ingrAmt_new = document.getElementById("input_newIngrAmt").value;
		cell_lastIngrID.innerHTML = "<td><select name='ingrID_" + ingrCount + "' id='select_ingrID_" + ingrCount + "' />$ingrOptions</td>";
		cell_lastIngrAmt.innerHTML = "<td><input type='text' name='ingrAmt_" + ingrCount + "' value='" + ingrAmt_new + "' /></td>";		
		
		// Set selected item from added Ingr
		var select_lastIngrID = document.getElementById("select_ingrID_" + ingrCount);
		select_lastIngrID.value = ingrID_new;
		
		// Create an array of IngrID: ingrValue
		// To be used later for dups validation
		ingrArry[ingrID_new] = ingrAmt_new;
			
			
		
		// Empties the text from the NEW INGR row
		document.getElementById("select_newIngrID").value = "none";
		document.getElementById("input_newIngrAmt").value = "";
	}
	
	function createIngrLists() {
		// Checks if there is a new ingredient to process, before submission
		var ingrID_new = document.getElementById("select_newIngrID").value;
		var ingrAmt_new = document.getElementById("input_newIngrAmt").value;
		if (ingrID_new != "none" || ingrAmt_new != "") {
			if(validateIngr() == false) {
				return false;		// Prevents submission if ingr is not valid
			}
		}
		
		// ingrArry[ingrID] = ingrAmt
		// Create a list of ingrIDs, ingrAmt
		// Then join as string, and POST
		// PHP code will process strings to create $ingrIDAmt[ingrID] = ingrAmt
		ingrIDList = new Array();
		ingrAmtList = new Array();
		for (var ingrID in ingrArry) {
			var ingrAmt = ingrArry[ingrID];
			ingrIDList.push(ingrID);
			ingrAmtList.push(ingrAmt);
		}
		
		document.getElementById("input_ingrIDList").value = ingrIDList.join(",");
		document.getElementById("input_ingrAmtList").value = ingrAmtList.join(",");
	}
HERE;
// End $js

$s = new DYLMG_SuperHTML('Submit a Glaze');
$s->addJS($js);
$s->buildTop();
$body = <<<HERE
<h1>Add a glaze</h1>
<form action="DYLMG_postGlazeReceipt.php" method="post" onsubmit="return createIngrLists()">
<h4>Glaze name</h4>
<input type="text" name="glaze_name" />
<input type='hidden' value="empty" name='ingrIDList' id='input_ingrIDList' /> 
<input type='hidden' value="empty" name='ingrAmtList' id='input_ingrAmtList' />         
<input type="submit" value="Next Step" />
</form>

		<div style="float:left">
<h3>Add Ingredients</h3>
<table id="table_addIngr">
	<tr class="header_row">
    	<td>Ingredient Name</td>
        <td>Amount</td>
    </tr>
    <tr id="row_newIngr">
    	<td><select name="ingrID_new" id="select_newIngrID">
        		$ingrOptions
            </select>
        </td>
        <td><input type="text" name="ingrAmt_new" id="input_newIngrAmt" /></td>
    </tr>
    <tr>
    	<td colspan="2" style="text-align:right"><input type="button" name="btn_addIngr" value="Add Ingredient" onclick="validateIngr()"/></td>
    </tr>
</table>
		</div>
        <div style="float:left; margin-left:55px;">
        	<h3>Unity Formula</h3>
            I would like to add:
            <ul>
            	<li>% Amt for each ingr</li>
                <li>Amt total-- maybe this should be red if less than 100</li>
                <li>Check box for "Base Ingr/Non Base", like for colorants, etc.</li>
                <li>Create dropdowns for ingredient names</li>
                <li>And of course, ingr. names will have to read compent parts from SQL DB (for unity calculation)</li>
                <li>Be able to delete a row -- I would imagine an "X" next to a row, goes to a js function that deletes row, calls array rewrite fnctn</li>
             </ul>
        </div>
HERE;
// End $body
$s->addText($body); 		// Could and should use SuperHTML to build page. But that's a project for another day...
$s->buildBottom();
print $s->getPage();