$(function() {    

	$('[name=submitbutton]').click(function () {
        $(this).removeClass('needs-save');
    });
    
    $('textarea').change(function() {
	    $('[name=submitbutton]').addClass('needs-save');
	});
	
	$('input').change(function() {
	    $('[name=submitbutton]').addClass('needs-save');
	});

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
