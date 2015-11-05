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

	$('#reforestaction_checkbox').off('click').on('click', function(e){
		var del = false;
		if ($(this).is(':checked'))
		{
			$('#reforestaction_table .checkbox.newsletter').slideDown();
		}
		else
		{
			$('#reforestaction_table .checkbox.newsletter').slideUp();

			if ($('body').attr('id') != 'order-opc')
			{
				if ($('.cart_quantity_delete[id^='+id_reforestaction+'_0_0]').length)
					del = true;
			}
		}

		reforestActionCall($(this), true, del);
	});

	$('#reforestaction_newsletter').on('click', function(e){
		reforestActionCall($(this), false);
	});

	$('.open_popin').fancybox();
});

function reforestActionCall(el, redirect, del)
{
	if ($('body').attr('id') != 'order-opc')
		return false;

	$('#reforestaction_checkbox').hide();
	$('#reforestaction_table .loader_ajax').show();

	if (el.parents('#reforestaction_table').hasClass('opc'))
	{
		$.ajax({
			url: reforestaction_link,
			type: 'POST',
			dataType: 'json',
			data: el.parents('#reforestaction_table').find('input').serialize(),
		})
		.success(function(jsonData) {
			if (!jsonData.update_cart)
			{
				if (typeof(redirect) == 'undefined' || redirect)
				{
					if ($('.cart_quantity_delete[id^='+id_reforestaction+'_0_0]').length)
					{
						$('.cart_quantity_delete[id^='+id_reforestaction+'_0_0]').off('click').on('click', function(e){
							e.preventDefault();	
							deleteProductFromSummary($(this).attr('id')); 
						});
						$(window).scrollTo($('.cart_quantity_delete[id^='+id_reforestaction+'_0_0]').parents('tr').prev('tr'));
						$('.cart_quantity_delete[id^='+id_reforestaction+'_0_0]').click();
						
						window.location.reload();
						return;
					}
					else
					{
						window.location.reload();
						return;
					}
				}
			}
			else
			{
				if (typeof(del) == 'undefined' || del)
				{
					if ($('.cart_quantity_delete[id^='+id_reforestaction+'_0_0]').length)
					{
						$('.cart_quantity_delete[id^='+id_reforestaction+'_0_0]').off('click').on('click', function(e){
							e.preventDefault();	
							deleteProductFromSummary($(this).attr('id')); 
						});
						$(window).scrollTo($('.cart_quantity_delete[id^='+id_reforestaction+'_0_0]').parents('tr').prev('tr'));
						$('.cart_quantity_delete[id^='+id_reforestaction+'_0_0]').click();
					}
				}

				if (!$('#cart_summary > tbody > tr[id^=product_'+jsonData.id_reforestaction+']').length)
				{
					$('#cart_summary > tbody > tr:last-child').removeClass('last_item');
					var tmp = $('#cart_summary > tbody > tr:last-child').clone();
					if (tmp.hasClass('odd'))
						tmp.addClass('even').removeClass('odd');
					else
						tmp.addClass('odd').removeClass('even');
					var iad = 0;
					for (k in jsonData.summary.products)
					{
						var p = jsonData.summary.products[k];
						if (p.id_product == jsonData.id_reforestaction)
						{
							var more_info;
							for (key in jsonData.products)
							{
								if (jsonData.products[key].id == p.id_product)
									more_info = jsonData.products[key];
							}


							iad = p.id_address_delivery;

							tmp.find('.cart_product img').each(function(){
								$(this).attr('src', more_info.image);
							});

							var url = tmp.find('.cart_product a').attr('href');

							tmp.find('a[href="'+url+'"]').each(function(){
								$(this).attr('href', more_info.link);
							});

							var e = tmp.find('[id^=product_price]');
							var change = e.attr('id').replace('product_price_', '');

							if (tmp.find('.product_name a').length)
								tmp.find('.product_name a').html(p.name);
							else
								tmp.find('.s_title_block a').html(p.name);

							var ref = tmp.find('.cart_description .cart_ref');
							if (ref.length)
							{
								var refs = ref.html().split(':');
								ref.html(refs[0]+' '+p.reference);
							}
							else
							{
								var ref = tmp.find('.cart_ref');
								if (!ref.length)
								{
									var refs = $('<p>').addClass('cart_ref').html(html_reference+' '+p.reference);
									tmp.find('.s_title_block').append(refs);
								}
								else
								{
									ref.html(p.reference);
								}
							}

							tmp.find('.cart_description > a, .cart_description > small').remove();

							tmp.find('[id*='+change+'], [name*='+change+']').each(function(){
								if ($(this).attr('id'))
								{
									var id = $(this).attr('id').replace(change, jsonData.id_reforestaction+'_0_'+iad);
									$(this).attr('id', id);
								}

								if ($(this).attr('name'))
								{
									var name = $(this).attr('name').replace(change, jsonData.id_reforestaction+'_0_'+iad);
									$(this).attr('name', name);
								}

							});

							tmp.find('[id*='+tmp.find('.cart_quantity_delete').attr('id')+'], [name*='+tmp.find('.cart_quantity_delete').attr('id')+']').each(function(){
								if ($(this).attr('id'))
								{
									var id = $(this).attr('id').replace(tmp.find('.cart_quantity_delete').attr('id'), jsonData.id_reforestaction+'_0_0_'+iad);
									$(this).attr('id', id);
								}

								if ($(this).attr('name'))
								{
									var name = $(this).attr('name').replace(tmp.find('.cart_quantity_delete').attr('id'), jsonData.id_reforestaction+'_0_0_'+iad);
									$(this).attr('name', name);
								}
							});

							tmp.find('a').each(function(){
								if ($(this).attr('href'))
								{
									var href = $(this).attr('href');
									href = href.replace(/id_product=[0-9]+/g, 'id_product='+p.id_product);
									href = href.replace(/ipa=[0-9]+/g, 'ipa=0');
									href = href.replace(/id_address_delivery=[0-9]+/g, 'id_address_delivery='+iad);

									$(this).attr('href', href);
								}
							});

						}
					}

					tmp.attr('id', 'product_'+jsonData.id_reforestaction+'_0_0_'+iad);
					tmp.appendTo($('#cart_summary > tbody'));

					tmp.find('.cart_quantity_delete' ).off('click').on('click', function(e){
						e.preventDefault();	
						deleteProductFromSummary($(this).attr('id')); 
					});

					tmp.find('.cart_quantity_up').off('click').on('click', function(e){
						e.preventDefault();
						upQuantity($(this).attr('id').replace('cart_quantity_up_', ''));
					});
					tmp.find('.cart_quantity_down').off('click').on('click', function(e){
						e.preventDefault();
						downQuantity($(this).attr('id').replace('cart_quantity_down_', '')); 
					});
					tmp.find('.cart_quantity_delete' ).off('click').on('click', function(e){
						e.preventDefault();	
						deleteProductFromSummary($(this).attr('id')); 
					});
					tmp.find('.cart_address_delivery').on('change', function(e){
						changeAddressDelivery($(this));
					});

					ajaxCart.overrideButtonsInThePage();
				}

				updateCartSummary(jsonData.summary);
				updateCustomizedDatas(jsonData.customizedDatas);
				updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
				updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);

				if (window.ajaxCart != undefined)
					ajaxCart.updateCart(jsonData);

				if (typeof(getCarrierListAndUpdate) !== 'undefined')
					getCarrierListAndUpdate();

				if (typeof(updatePaymentMethodsDisplay) !== 'undefined')
					updatePaymentMethodsDisplay();
			}

			$('#reforestaction_checkbox').show();
			$('#reforestaction_table .loader_ajax').hide();
		});
		
	}
}