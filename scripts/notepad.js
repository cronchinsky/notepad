$(function($) {    

	$('[name=submitbutton]').click(function () {
        $(this).removeClass('needs-save');
    });
    
    $('textarea').change(function() {
	    $('[name=submitbutton]').addClass('needs-save');
	});
	
	$('input').change(function() {
	    $('[name=submitbutton]').addClass('needs-save');
	});
	
	// capture clicks on the toggle links
	$('a.toggleLink').click(function() {
		// toggle the display
		$(this).parents().eq(2).nextAll('.toggle').toggle('fast');
		// return false so any link destination is not followed
		return false;		
	});
	
	$('a.sessiontoggleLink').click(function() {
		// toggle the display
		$(this).parents().next('.toggle').toggle('fast');
		
		// return false so any link destination is not followed
		return false;		
	});
	
	$('a.alltoggleLink').click(function() {
		
		// change the link text depending on whether the element is shown or hidden
		if ($(this).text()=='Show All') {
				$(this).text('Hide All');
				// show everything display
				$('.toggle').show('fast');
			}
			else {
				$(this).text('Show All');
				// hide everything 
				$('.toggle').hide('fast');
			}

		
		
		// return false so any link destination is not followed
		return false;		
	});
	
	$('.toggle').hide();
    $('.sessiontoggle').show();



});

function display_confirm(url) {

	var r=confirm("Did you save your work? Click cancel to return and save.");
	if (r==true) {
	  window.location.href = url;
	} 
	
	return false;
}

window.onbeforeunload = function (e) {
    if ($('#id_submitbutton').hasClass('needs-save')) {
        var message = "You haven't saved your work yet!  Click OK to navigate away from this page and lose any unsaved data.";
        var e = e || window.event;
        // For IE and Firefox
        if (e) {
            e.returnValue = message;
        }

        // For Safari
        return message;   
    }
}
