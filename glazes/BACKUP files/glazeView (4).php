<?php
/**
 * glazes/glazeView.php
 * Allows users to post new glazes,
 * edit existing glazes, and view
 * glazes by other authors.
 */
/**
 * Uses DYLMG_SuperHTML class
 * as a page template.
 */



/**
 * Body of page (HTML)
 */
 
$body = <<<HERE
<div id='content-container' >

	<!-- HIGHLIGHT COLUMN (glaze details) -->
	<div id='highlight-column'>
			<!-- Header (Glaze Name)-->
        <div id="pageHeader">
        <div id="glazeTitle">Glaze name</div>
        </div><!-- end pageHeader div-->
            
		<div class="section"><div class="sectionContent">
			<div id="headerBtns">
                <button onclick="window.location='/glazes/DYLMG_postGlaze.php?glazeID=new'" class="ui-button ui-widget ui-state-default ui-button-text-only" role="button"><span class="ui-icon ui-icon-plusthick" style="float:left"></span>&nbsp; Add a new Glaze</button><br />
                <button class="input_newVar ui-button ui-widget ui-state-default ui-button-text-only" role="button"><span class="ui-icon ui-icon-plusthick" style="float:left"></span>&nbsp; Add a new Variation</button>
                <div class="clear"></div>
            </div><!-- End headerBtns div -->
		</div></div>

		<!-- Glaze Details -->
		<div class='section'>
			<div class ='sectionHead'>Glaze Details</div>
			<div class='sectionContent'>
				<table id="glazeDetails" >
					<colgroup style="width:6em;"></colgroup>
					<colgroup></colgroup>
					<tr><td><b>Color: </b></td><td><div id="details_color" class="vtip click2edit" title="Click to edit"></div></td></tr>
					<tr><td><b>Surface: </b></td><td><div id="details_surface" class="vtip click2edit" title="Click to edit"></div></td></tr>
					<tr><td><b>Author: </b></td><td><div id="details_author" class="vtip click2edit" title="Click to edit"></div></td></tr>
					<tr><td><b>Posted: </b></td><td><div id="details_datePosted" class="vtip click2edit" title="Click to edit"></div></td></tr>
					<tr><td><b>Description: </b></td><td><div id="details_descr" class="vtip click2edit" title="Click to edit"></div></td></tr>
				</table>
			</div>	<!-- end sectionContent -->
		</div>	<!-- end of section -->
		
		<!-- Unity Table-->
		<div class='section'>
			<div class='sectionHead'>Unity Table</div>
			<div class='sectionContent'>
				<div id="div_unityTable">
					<table class="unityTable">
						<!-- JS creates unity table here-->
					</table>
				</div>	<!-- end div_unityTable-->
			</div>	<!-- end sectionContent -->
		</div>	<!-- end of section -->
		
		<!-- Glaze Variations -->
		<div class='section'>
			<div class='sectionHead'>Variations</div>
			<div id="div_vars" class="sectionContent">
				<table id="table_vars" style="width:100%">
					<tbody>
						<!-- List of glaze variations goes here -->
					</tbody>
				</table>
			</div><!-- End Section Content-->
		</div><!-- End section-->

		<!-- Add new Variation button --> 
		<div class="section" style="border:none; margin-top:3px; text-align:right">
			<button id="btn_addVariation" class="input_newVar ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
				<span class="ui-button-text">Add New Variation</span>
			</button>
		</div>	<!-- end of section (for new variation button) -->

	</div>	<!-- end of highlight-column-->
	
	<!-- CONTENT-COLUMN -->
	<div id='content-column'>
		<!-- Base Ingredients-->
		<div class='section'>
		<div class='sectionHead'>Base Recipe</div>
		<div class='sectionContent'>
		
			<colgroup width="150px"></colgroup>
			<table id="table_glaze" class="recipeTable">
				<thead> 
					<td width='150px'>Ingredient</td>
					<td>Amount</td>
				</thead>
				<tbody>
					<!-- Recipe Ingredients go here -->
					<tr class='new_ingrRow'>
						<td>
							<input type='text' class='ingrSuggest' id='new_baseIngr' name='new_baseIngr' />
						</td>
						<td><!-- Amount -->
							<input type='text' id='new_ingrAmt' name='new_ingrAmt' value='' size='6' readonly />
						</td>
					</tr>
				</tbody>
				<tfoot style="text-align:right; font-size:0.9em; border-top:1px solid #c2c2c2">
					<tr>
						<td colspan=2 style="padding:0px">
							<!-- Set Batch Size-->
							<label for="input_batchAmt" >Total:</label>
							<input type="input" name="input_batchAmt" id="input_batchAmt" value="100" size="4" style='text-align:right; font-size:0.8em' ><br>
						</td>
					</tr>
				</tfoot>
			</table>
			
		</div><!-- End Section content-->
		</div><!-- end section (Base Ingredients)-->
		
		<div class="clear"></div>
		<button id="btn_calcPW" id="btn_calcPW" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Calculate PW</span></button>	
		<br /><br />
		
		<!-- Additional Ingredients -->
		<div class="section" style="width:250px; float:left">
			<div class="sectionHead">Additional Ingredients</div>
			<div id="div_additions" class="sectionContent">
				<table id="table_variation" class="recipeTable">
					<thead> 
						<td width='150px'>Ingredient</td>
						<td width='100px'>Amount</td>
					</thead>
					<tbody>
						<!-- Recipe Ingredients go here -->
						<tr class='new_ingrRow'>
							<td>
								<input type='text' class='ingrSuggest' id='new_varIngr' name='new_varIngr' />
							</td>
							<td><!-- Amount -->
								<input type='text' id='new_ingrAmt' name='new_varAmt' value='' size='5' readonly />
							</td>
						</tr>
					</tbody>
				</table>
			</div><!-- End Section Content-->
		</div><!-- End Section (additional ingredients)-->
		
		
		<div class='section' style="margin-top:2px;">
			<!-- Save Results / reminder to save-->
			<div id="saveResults" class="ui-state-highlight ui-corner-all">
				<span class="ui-icon ui-icon-info" style="float:left"></span><b>Remember to save!</b>
			</div>
			
	
			<!-- Save Glaze-->
			<button id="saveGlaze"  class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Save Glaze</span></button>	
			
		</div><!--End Section-->


	</div>	<!-- end of content-column -->
</div><!-- end of content-container div -->

<!-- Outside of container-->
<div id="outside-container" >

    <div class="section">
    <div class="sectionContent">
        <!-- Thumbnail gallery -->
        <div id="loadedImg" style="float:left; border:0px outset #4f4f4f"></div>
        <div style="float:left;">
			<ul id="thumbGallery" class="hoverGallery" style="width:55px">
			</ul><br />
        </div><!-- End hovergallery div-->
        
        <div style="float:right; margin-top:15px;">
        <button id="btn_imgUpload" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Upload an Image</span></button>
        </div>
        <div class='clear'></div><!-- Required for full height of container (since we cant use overflow:hidden)-->
    </div><!-- End sectionContent-->
    </div><!-- End section -->

    

</div><!-- End outsideContainer -->

<!-- Bottom Message (eg. 'Saving...') -->
<div id="bottomMsg" class="ui-state-highlight ui-corner-all"></div>

<!-- DIALOG: Image Upload -->
<div id="dialog_imgUpload">
<form id='form_imgUpload' name='form_imgUpload' action='/includes/imgUpload.php' method='post' enctype="multipart/form-data" target="imgUpload" >
<!-- IFRAME was slowing down page load and calling document.ready multiple times. Now, iframe is added and removed dynamically by glazeView.js -->
<!-<iframe id='imgUpload' name="imgUpload" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>-->
<label for='imgFile'>Select a file:</label><br />
<input name="imgFile" type="file" value="Browse..." /><br />
<label for='imgDescr'>Description:</label><br />
<textarea row='5' cols='35' name='imgDescr'></textarea>
<input type="hidden" name="imgUpload_userID" />
<input type="hidden" name="imgUpload_varID" />
</form>
<div class='progressbar'></div>
</div>
<!-- End image upload DIALOG -->

<!-- DIALOG: Add new Variation (cloned as new Glaze) -->
<!-- from: jqueryui.com/demos/dialog/ -->
<div id='dialog_newVariation' class='dialog_newInfo' title="Add New Variation">
	<form><fieldset>
		<!-- MUST SET ALL IDs EQUAL TO MySQL FIELD NAMES -->
		<label for='newVar_name'>Variation Name</label><br />
		<input type='text' name='newVar_name' id='VarName' /><br />
		<label for='newVar_color'>Variation Color</label><br />
		<input type='text' name='newVar_color' id='VarColor' /><br />
		<label for='newVar_surface'>Variation Surface</label><br />
		<input type='text' name='newVar_surface' id='VarSurface' /><br />
		<label for='newVar_descr'>Variation Description</label><br />
		<textarea rows='5' cols='35' name='newVar_descr' id='VarDescr' /></textarea>
		<input type='hidden' id='newVar_default' name='VarDefault' value=1 />
	</fieldset></form>
</div>
<!-- End new Glaze DIALOG -->


HERE;
// end of body HTML

/**
 * Build $page DYLMG_SUPERHTML Object.
 * Used as a template.
*/

require_once('../includes/DYLMG_SuperHTML.php');
$page = new DYLMG_SuperHTML("Glaze View");			// Title should be reset by javascript to reflect nature of page (ie, view, edit, post, glaze name)

/**
 * Send variables from php to javascript
*/
// Check that glazeID is a number
if (is_numeric($_GET['glazeID']) ) {
	$GET_glazeID = (int)$_GET['glazeID'];
} else {
	$GET_glazeID = 0;						// Set glazeID to 0 (new glaze)
}
$js = "var GET_glazeID = ".$GET_glazeID .";";
$page->addJS($js);


/**
 * INCLUDED FILES
*/

// JQUERY resources
$page->addCSSLink('/includes/jquery_ui/css/start/jquery-ui-1.8.10.custom.css');	// Style for all jquery ui elements
$page->addJSLink('/includes/jquery-1.5.min.js');									// Core jquery file
$page->addJSLink('/includes/jquery_ui/js/jquery-ui-1.8.10.custom.min.js');			// jquery ui 
$page->addJSLink('/includes/jquery.json-2.2.min.js');								// handles JSON conversion (for AJAX)

// All-purpose javascript functions
$page->addJSLink('/includes/jsFunctions.js');

// Glaze Object javascript
$page->addJSLink('/includes/Objects/MySQLFields.js');		// Defines Object properties from SQL field names
$page->addJSLink('/includes/Objects/GlazeObject.js');

// auto-suggest CSS
$page->addCSSLink('/includes/CSS/suggestTable.css');

// Thumbnail hover animations
$page->addCSSLink('/includes/hoverGallery/hoverStyle.css');						// Style for hovergallery
$page->addJSLink('/includes/hoverGallery/hoverGallery.js');						// Hovergallery script 

// vTip tooltip
$page->addCSSLink('/includes/vTip/css/vtip.css');
$page->addJSLink('/includes/vTip/vtip.js');

// Procedural Code
$page->addJSLink('glazeView.js');

$page->buildTop();
$page->addText($body);
$page->buildBottom();

print $page->getPage();
?>