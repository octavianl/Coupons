$(document).ready(function() {
	SyntaxHighlighter.all();
	
	// menu
	$('div#navigation a.parent').click(function () {
		if ($(this).parent().children('ul').length > 0) {
			$('div#navigation ul li ul').hide();
			$(this).parent().children('ul').slideDown();
		}
		return false;
	});
	
	// trigger active menu
	$('div#navigation a.active').trigger('click');
	
	$('div#navigation ul li:last-child').css('border-bottom','0');
});