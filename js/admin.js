$(function(){

	$('[name="submitOptionsconfiguration"]').click(function() {
		return RaVerifyForm();
	});

	$('[name="RA_MERCHANT_EMAIL"]').blur(function(){
		return RaVerifyForm();
	});

	$('[name="RA_MERCHANT_PHONE"]').blur(function(){
		return RaVerifyForm();
	});

	$('#configuration_form').submit(function(){
		return RaVerifyForm();
	});
	
	RaVerifyForm();
});

function RaVerifyForm()
{
	var verifMailREGEX = /^([\w+-]+(?:\.[\w+-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/;
	var mail = $('[name="RA_MERCHANT_EMAIL"]').val();
	$('[name="RA_MERCHANT_EMAIL"]').removeAttr('style');
	if (!verifMailREGEX.test(mail))
	{
		$('[name="RA_MERCHANT_EMAIL"]').css({
			'background-color' : '#f2dede',
			'border-color' : '#ebccd1'
		});

		return false;
	}

	var verifPhoneREGEX = /^[+0-9. ()-]*$/;
	var phone = $('[name="RA_MERCHANT_PHONE"]').val();
	$('[name="RA_MERCHANT_PHONE"]').removeAttr('style');
	if (!verifPhoneREGEX.test(phone))
	{
		$('[name="RA_MERCHANT_PHONE"]').css({
			'background-color' : '#f2dede',
			'border-color' : '#ebccd1'
		});

		return false;
	}
}