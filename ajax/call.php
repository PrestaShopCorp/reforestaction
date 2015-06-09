<?php
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

	$result['id_reforestaction'] =  Configuration::get('RA_PRODUCT');
	$result['summary'] = $context->cart->getSummaryDetails(null, true);
	$result['customizedDatas'] = Product::getAllCustomizedDatas($context->cart->id, null, true);
	$result['HOOK_SHOPPING_CART'] = Hook::exec('displayShoppingCartFooter', $result['summary']);
	$result['HOOK_SHOPPING_CART_EXTRA'] = Hook::exec('displayShoppingCart', $result['summary']);

	Hook::exec('actionCartListOverride', array('summary' => $result, 'json' => &$json));

	if (version_compare(_PS_VERSION_, '1.6', '<'))
	{
		$json = Tools::jsonDecode($json);
		foreach ($json->products as &$row)
		{
			$image = Product::getCover($row->id);
			$product = new Product($row->id, false, $context->language->id);

			$row->image = $context->link->getImageLink($product->link_rewrite, $image['id_image'], 'small_default');
		}
		$json = Tools::jsonEncode($json);
	}
	
	die(Tools::jsonEncode(array_merge($result, (array)Tools::jsonDecode($json, true))));
}
