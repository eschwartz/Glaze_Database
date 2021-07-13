/*
Basically all we are doing is animating the thumbnail’s size, 
absolute positioning coordinates (vertical alignment w/ css), 
and padding when we hover over. 

During this animation, we also switch the value of the z-index, 
so that the selected image stays on top of the rest.
*/

/* REQUIRES JQUERY */
/* REQUIRES hoverStyle.css */

// Adapted from http://www.sohtanaka.com/web-design/fancy-thumbnail-hover-effect-w-jquery/ by SOHTANAKA

$(document).ready(function(){
	hoverGallery();						   
});

function hoverGallery() {
	$("ul.hoverGallery li").hover(function() {
											   
		// Add description div on top of image
		var descrText = $(this).find('img').attr('alt');
		$(this).append("<div class='hoverDescr'>" + descrText + "</div>");
		
		// Find auto-height of div (.animate doesn't alow for height:auto...)
		var autoHeight = 0;
		var originalWidth = $(this).find('div.hoverDescr').width();
		var targetWidth = 224;
		// Temporarily set width to target, to get an accurate auto-height measurement
		$(this).find('div.hoverDescr').width(targetWidth);
		// Adds up height of each nested div
		$(this).find('div.hoverDescr').children('div').each(function() {
			autoHeight += $(this).height();
		});
		// Set width back to orignial (small) size
		$(this).find('div.hoverDescr').width(originalWidth)

		
		// Animates description div
		$(this).find('div.hoverDescr').addClass("hover").addClass("ui-corner-all").stop() /* Add class of "hover", then stop animation queue buildup*/
		.animate({
			 /* The next 4 lines will vertically align this image */ 
			top: '100px',
			left: '-50%',
			width: targetWidth, /* Set new width */
			height: autoHeight+10, /* Set new height */
			opacity:'0.95', 
			filter:'alpha(opacity=95)'
		}, 200); /* this value of "200" is the speed of how fast/slow this hover animates */
		
		// Animate Image
		$(this).css({'z-index' : '10'}); /*Add a higher z-index value so this image stays on top*/ 
		$(this).find('img').addClass("hover").stop() /* Add class of "hover", then stop animation queue buildup*/
		.animate({
			marginTop: '-110px', /* The next 4 lines will vertically align this image */ 
			marginLeft: '-110px',
			top: '50%',
			left: '50%',
			width: '174px', /* Set new width */
			height: '174px', /* Set new height */
			padding: '20px',
		}, 200); /* this value of "200" is the speed of how fast/slow this hover animates */
	
		} , function() {
		// BACKWARDS Animates description div
		$(this).find('div.hoverDescr').addClass("hover").stop() /* Add class of "hover", then stop animation queue buildup*/
		.animate({
			marginTop: '0', /* Set alignment back to default */
			marginLeft: '0',
			top: '55px',
			left: '5px',
			width: '100px', /* Set width back to default */
			height: '50px', /* Set height back to default */
			padding: '0px',
			opacity:'0.1', 
			filter:'alpha(opacity=10)'
		}, 400, "swing", function() {	// Animation callback function
				$(this).remove();		// Removes div after animation completes
		}); 
		
		// BACKWARDS Animates image
		$(this).css({'z-index' : '0'}); /* Set z-index back to 0 */
		$(this).find('img').removeClass("hover").stop()  /* Remove the "hover" class , then stop animation queue buildup*/
			.animate({
				marginTop: '0', /* Set alignment back to default */
				marginLeft: '0',
				top: '0',
				left: '0',
				width: '50px', /* Set width back to default */
				height: '50px', /* Set height back to default */
				padding: '5px'
			}, 400);
	});
	
	// Swap Image on Click
	// Except for <a> with class="link"
	// or if #loadedImg div doesn't exist
		$("ul.hoverGallery li a").click(function() {
			if ($(this).attr("class") == "link" || $('#loadedImg').length == 0) {
				return true;
			}
			else {
				var mainImage = $(this).attr("href"); //Find Image Name
				$("#loadedImg img").attr({ src: mainImage });
				return false;		
			}
		});
}