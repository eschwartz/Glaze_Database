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
	<!-- Header (Glaze Name)-->
        <div id="pageHeader">
        <div id="glazeTitle">Glaze name</div>
            <div id="headerBtns">
                <button onclick="window.location='/glazes/DYLMG_postGlaze.php?glazeID=new'" class="ui-button ui-widget ui-state-default ui-button-text-only" role="button"><span class="ui-icon ui-icon-plusthick" style="float:left"></span> Add a new glaze</button>
                <button class="input_newVar ui-button ui-widget ui-state-default ui-button-text-only" role="button"><span class="ui-icon ui-icon-plusthick" style="float:left"></span> Add a new glaze variation</button>
                <div class="clear"></div>
            </div><!-- End headerBtns div -->
        </div><!-- end pageHeader div-->

	<!-- HIGHLIGHT COLUMN (glaze details) -->
	<div id='highlight-column'>
		
		<!-- Glaze Details -->
		<div class='section'>
			<div class ='sectionHead'>Glaze Details</div>
			<div class='sectionContent'>
				<table id="glazeDetails" >
					<colgroup width='80px'></colgroup>
					<colgroup></colgroup>
					<tr><td><b>Glaze Name: </b></td><td><div id="details_name"></div></td></tr>
					<tr><td><b>Color: </b></td><td><div id="details_color"></div></td></tr>
					<tr><td><b>Surface: </b></td><td><div id="details_surface"></div></td></tr>
					<tr><td><b>Author: </b></td><td><div id="details_author"></div></td></tr>
					<tr><td><b>Posted: </b></td><td><div id="details_datePosted"></div></td></tr>
					<tr><td><b>Description: </b></td><td><div id="details_descr"></div></td></tr>
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
				<table id="table_vars">
					<tbody>
						<!-- List of glaze variations goes here -->
					</tbody>
				</table>
			</div><!-- End Section Content-->
		</div><!-- End section-->

		<!-- Add new Variation button --> 
		<div class="section" style="border:none; margin-top:3px; text-align:right">
			<button class="input_newVar ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
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
			<table id="table_recipe" class="recipeTable">
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
							<input type='text' id='new_ingrAmt' name='new_ingrAmt' value='' size='5' readonly />
						</td>
					</tr>
				</tbody>
			</table>
			
		</div><!-- End Section content-->
		</div><!-- end section (Base Ingredients)-->
		
		<!-- Additional Ingredients -->
		<div class="section" style="width:250px; float:left">
			<div class="sectionHead">Additional Ingredients</div>
			<div id="div_additions" class="sectionContent">
				<table id="table_additional" class="recipeTable">
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
			
			<!-- Set Batch Size-->
			<label for="input_batchAmt" >Batch Size:</label>
			<input type="input" name="input_batchAmt" id="input_batchAmt" value="100" size="5"><br>
			<button id="btn_calcPW" id="btn_calcPW" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Calculate PW</span></button>	
	
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
        <div id="loadedImg" style="float:left; border:2px outset #4f4f4f"></div>
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
<button id="testBtn">Log Glaze</button>
HERE;
// end of body HTML

/**
 * Build $page DYLMG_SUPERHTML Object.
 * Used as a template.
*/

require_once('../includes/DYLMG_SuperHTML.php');
$page = new DYLMG_SuperHTML("Glaze View");			// Title should be reset by javascript to reflect nature of page (ie, view, edit, post, glaze name)

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

// AJAX Manager (required for Glaze object)
$page->addJSLink('/includes/jquery.ajaxmanager.js');

// Glaze Object javascript
$page->addJSLink('/includes/Objects/MySQLFields.js');		// Defines Object properties from SQL field names
$page->addJSLink('/includes/Objects/GlazeObject.js');

// auto-suggest CSS
$page->addCSSLink('/includes/CSS/suggestTable.css');

// Thumbnail hover animations
$page->addCSSLink('/includes/hoverGallery/hoverStyle.css');						// Style for hovergallery
$page->addJSLink('/includes/hoverGallery/hoverGallery.js');						// Hovergallery script 

// Procedural Code
$page->addJSLink('glazeView.js');

$page->buildTop();
$page->addText($body);
$page->buildBottom();

print $page->getPage();
?>