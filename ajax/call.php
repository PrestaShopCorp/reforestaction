<?php
/**
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('TMP_DS'))
	define('TMP_DS', DIRECTORY_SEPARATOR);

require_once dirname(__FILE__).TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'config'.TMP_DS.'config.inc.php';
require_once _PS_ROOT_DIR_.TMP_DS.'init.php';

$context = Context::getContext();

if (Module::isInstalled('reforestaction') && Module::isEnabled('reforestaction'))
{
	$module = Module::getInstanceByName('reforestaction');
	$module->hookActionCarrierProcess(array('cart' => $context->cart));

	$result = array('update_cart' => ReforestAction::UPDATE_CART);

	if (!ReforestAction::UPDATE_CART)
		die(Tools::jsonEncode($result));

	$result['id_reforestaction'] = Configuration::get('RA_PRODUCT');
	$result['summary'] = $context->cart->getSummaryDetails(null, true);
	$result['customizedDatas'] = Product::getAllCustomizedDatas($context->cart->id, null, true);
	$result['HOOK_SHOPPING_CART'] = Hook::exec('displayShoppingCartFooter', $result['summary']);
	$result['HOOK_SHOPPING_CART_EXTRA'] = Hook::exec('displayShoppingCart', $result['summary']);

	$json = null;

	Hook::exec('actionCartListOverride', array('summary' => $result, 'json' => &$json));

	if (version_compare(_PS_VERSION_, '1.6', '<'))
	{
		$json = Tools::jsonDecode($json);
		foreach ($json->products as &$row)
		{
			$image = Product::getCover($row->id);
			$product = new Product($row->id, false, $context->language->id);

			$row->image = $context->link->getImageLink($product->link_rewrite, $image['id_image'], ImageType::getFormatedName('small'));
		}
		$json = Tools::jsonEncode($json);
	}

	die(Tools::jsonEncode(array_merge($result, (array)Tools::jsonDecode($json, true))));
}
