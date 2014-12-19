$(function(){

	$('.fancybox').fancybox();

	$('#reforestaction_checkbox').click(function(){

		if ($(this).is(':checked'))
		{
			$('.reforestaction_checkbox .checkbox.newsletter').slideDown();
		}
		else
		{
			$('.reforestaction_checkbox .checkbox.newsletter').slideUp();	
		}

	});

});