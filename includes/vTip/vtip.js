/**
Vertigo Tip by www.vertigo-project.com
Requires jQuery
*/

this.vtip = function() {
    this.xOffset = -10; // x distance from mouse
    this.yOffset = 0; // y distance from mouse       
    
    $(".vtip").unbind().hover(    
        function(e) {
            this.t = this.title;
            this.title = ''; 
            this.top = (e.pageY + yOffset); this.left = (e.pageX + xOffset);
            
            $('body').append( '<p id="vtip"><img id="vtipArrow" />' + this.t + '</p>' );
                        
            $('p#vtip #vtipArrow').attr("src", '/images/vtip_arrow.png');
            $('p#vtip').css("top", this.top+"px").css("left", this.left+"px").fadeIn("slow");
            
        },
        function() {
            this.title = this.t;
            $("p#vtip").fadeOut("slow").remove();
        }
    ).mousemove(
        function(e) {
            this.top = (e.pageY + yOffset);
            this.left = (e.pageX + xOffset);
                         
            $("p#vtip").css("top", this.top+"px").css("left", this.left+"px");
        }
    );     
	
	/*
	* Click on the vTip'd area to 
	* convert to editable text
	* (by Edan)
	*/
	$('.click2edit').click(function() {		
		var elInit = $(this).clone(true, true);			
		elInit.attr('title', this.t);									// Title doesn't copy with clone(), becuase this.title is changed in vtip code above
		
		// Remove tooltip
		this.title = this.t;
        $("p#vtip").fadeOut("slow").remove();
		
		// Create input
		var c2eInput = $('<input>').val(elInit.text() ).attr('id',elInit.attr('id') );		// Input maintains id, to make for easier event handling on input change
		$(this).replaceWith(c2eInput);
		c2eInput.select();
		
		c2eInput.bind('keypress blur', function(e) {
			if (e.which == '13' || e.which == '9' || e.type == 'blur') {					// ENTER or TAB or BLUR
				var newText = $(this).val();
				elInit.text(newText);
				$(this).replaceWith(elInit);
			}
		});
		
	});
    
};

jQuery(document).ready(function($){vtip();}) 