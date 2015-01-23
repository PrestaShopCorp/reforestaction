/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @version  Release: $Revision: 7310 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
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

	if (!mail)
		return;

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