/**
 * click2edit
 *
 * Allows user to click any text
 * (with click2edit class)
 * and change it into an editable input
 *
 * REQUIRES:
 * jquery
 * click2edit.css
*/

function click2edit() {
	var hint = $('<div>').addClass('c2e_hint');
	hint.html("Click to edit");

	$('.click2edit').hover(function() {
		// on hoverIn
		hint.appendTo($(this));
	},
	function() {
		// on hoverOut
		hint.remove();
	});
}