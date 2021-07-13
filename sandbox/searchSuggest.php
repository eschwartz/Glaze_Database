<head>
<style>
table.suggest td, table.suggest {
	border-top:1px dotted black;
	padding: 5px;
}

table.suggest {
	background-color:#EFEFEF;
	border:2px solid #996600;
	border-top: none;
	border-collapse: collapse;
	color:#880000 ;
	font: 80% 'Lucida Grande',Verdana,Arial,sans-serif;
	z-index:100;	/* Overlaps any other content */
	position: relative;
	top:24px;
	left: -1px;
	line-height:1px; 	/* Will animate rows expanding height */
	position:absolute;
}

table.suggest tr:hover {
	background-color:#FFFF99;
	font-weight:bold;
	color:black;
	cursor:pointer; cursor:hand; /* Browser compatible */
}
</style>
<script type='text/javascript' src='../includes/jquery-1.5.min.js'></script>
<script type='text/javascript' src='../includes/jquery.json-2.2.min.js'></script>
<script type='text/javascript' src='../includes/jsFunctions.js'></script>
<script type='text/javascript' >
$(document).ready(function() {
	$('#filter').keyup(suggest);
});

function suggest() {
	var filter = $('#filter').val();
	var ajaxFile = '/includes/AJAX/filterIngrList.php';
	$.post(ajaxFile, "filter="+filter, function(res) {
		// Returns array of matching ingredients (ingrID => ingName
		var ingrIDName = $.evalJSON(res);
		
		// Creates drop-down table (if it doesn't exist already)
		if ($('#tbl_suggest').length == 0) {
			$("<table id='tbl_suggest'></table>").insertAfter('#filter');
			// Set width of table to same as input
			var inputWidth = parseFloat($('#filter').css('width'));
			$('#tbl_suggest').width(inputWidth+6);
			
			// Animate table like drop-down
			$('#tbl_suggest').addClass('suggest')
			.animate({
					'line-height':'20px',
			}, 300);
		}
		else {
			// Empties the table
			$('#tbl_suggest').empty();
		}
		
		// Add each ingredient to table
		if (assocLength(ingrIDName) > 0) {
			for (var ingrID in ingrIDName) {
				var ingrName = ingrIDName[ingrID];
				$('#tbl_suggest').append("<tr id='ingr_"+ingrID+ "'><td>"+ingrName+"</td></tr>");
				// Add hover event, and sends id and name to event
				$("#ingr_"+ingrID).bind('mouseover', {name: ingrName, id:ingrID}, ingrHover);
			}
		}
		else {
			$('tbl_suggest').append("<tr><td><i>No ingredients found</i></td></tr>");
		}
		
	});
}

function ingrHover(evt) {
	var ingrID = evt.data.id;
	var ingrName = evt.data.name;
	alert(ingrID+ingrName);
}

</script>
<title>searchSuggest - Sandbox</title>
</head>
<h1>Search with cool dropdown css table</h1>
<div style="position:relative">
<input type='text' id='filter' style="width:200" />
</div>
some text
