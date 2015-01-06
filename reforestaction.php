<?php
/**
* 2007-2014 PrestaShop
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
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class ReforestAction extends Module
{

	const ACCOUNT_WAITING = 1;
	const ACCOUNT_BANNED  = 2;
	const ACCOUNT_OK      = 3;
	const ACCOUNT_WAITING_SLIMPAY = 4;

	/**
	 * Module link in BO
	 * @var String
	 */
	private $link_module;

	/**
	 * Api Call
	 * @var ApiCaller
	 */
	public $call;

	/**
	 * Constructor of module
	 */
	public function __construct()
	{
		$this->name = 'reforestaction';
		$this->tab = 'front_office_features';
		$this->version = '0.0.1';
		$this->author = '202-ecommerce';

		parent::__construct();

		$this->displayName = $this->l('Reforest Action');
		$this->description = $this->l('Reforest action');

		$this->includeFiles();

		if (!extension_loaded('curl'))
			$this->warning .= $this->l('To use your module, please activate cURL (PHP extension)');

		// Check upgrade if enabled and installed
		if (self::isInstalled($this->name) && self::isEnabled($this->name))
			$this->upgrade();

		$this->dev = true;
		$this->config = array(
			'dev' => array(
				'url_to_slimpay' => 'http://localhost/api.reforestaction/slimpay/tpe-php-5/tpe-php/make_mandat_request.php',
				'host' => 'http://localhost/api.reforestaction',
			),
			'prod' => array(
				'url_slimpay' => 'http://localhost/api.reforestaction/slimpay/tpe-php-5/tpe-php/make-mandat-request.php',
				'host' => 'http://localhost/api.reforestaction',
			),
		);

	}

	private function includeFiles()
	{
		$path = $this->getLocalPath().'classes/';
		/* Import models */
		foreach (scandir($path) as $class)
		{
			if (is_file($path.$class))
			{
				$class_name = Tools::substr($class, 0, -4);
				//Check if class_name is an existing Class or not
				if (!class_exists($class_name) && $class_name != 'index')
					require_once($path.$class_name.'.php');
			}
		}

		$path .= 'helper/';

		/* Import helpers */
		foreach (scandir($path) as $class)
		{
			if (is_file($path.$class))
			{
				$class_name = Tools::substr($class, 0, -4);
				//Check if class_name is an existing Class or not
				if (!class_exists($class_name) && $class_name != 'index')
					require_once($path.$class_name.'.php');
			}
		}
	}

	############################################################################################################
	# Install / Upgrade / Uninstall
	############################################################################################################

	/**
	 * Module install
	 * @return boolean if install was successfull
	 */
	public function install()
	{
		// Install default
		if (!parent::install())
			return false;

		// Uninstall DataBase
		if (!$this->installSQL())
			return false;

		// Install tabs
		if (!$this->installTabs())
			return false;

		// Registration hook
		if (!$this->registrationHook())
			return false;

		Configuration::updateValue('RA_EVERY_HOUR', 12); // In hours

		return true;
	}

	/**
	 * Upgrade if necessary
	 */
	public function upgrade()
	{
		// Configuration name
		$cfg_name = Tools::strtoupper($this->name.'_version');
		// Get latest version upgraded
		$version = Configuration::getGlobalValue($cfg_name);
		// If the first time OR the latest version upgrade is older than this one
		if ($version === false || version_compare($version, $this->version, '<'))
		{
			// Upgrade in DataBase the new version
			Configuration::updateGlobalValue($cfg_name, $this->version);
		}
	}

	/**
	 * Module uninstall
	 * @return boolean if uninstall was successfull
	 */
	public function uninstall()
	{
		// Uninstall DataBase
		if (!$this->uninstallSQL())
			return false;

		// Delete tabs
		if (!$this->uninstallTabs())
			return false;

		// Uninstall default
		if (!parent::uninstall())
			return false;

		return true;
	}


	/**
	 * Initialisation to install / uninstall
	 */
	private function installTabs()
	{
		$result = true;

		$menu_id = -1;

		$controllers = scandir(dirname(__FILE__).'/controllers/admin');
		foreach ($controllers as $controller)
		{
			if (is_file(dirname(__FILE__).'/controllers/admin/'.$controller) && $controller != 'index.php')
			{
				require_once(dirname(__FILE__).'/controllers/admin/'.$controller);
				$controller_name = Tools::substr($controller, 0, -4);
				if (class_exists($controller_name))
				{
					if (method_exists($controller_name, 'install'))
						$result &= call_user_func(array($controller_name, 'install'), $menu_id, $this->name);
				}
			}
		}

		return $result;

	}

	/**
	 * Delete tab
	 * @return  boolean if successfull
	 */
	public function uninstallTabs()
	{
		return ReforestactionTotAdminTabHelper::deleteAdminTabs($this->name);
	}

	############################################################################################################
	# SQL
	############################################################################################################

	/**
	 * Install DataBase table
	 * @return boolean if install was successfull
	 */
	private function installSQL()
	{
		/*
		 * Install All Object Model SQL via install function
		 */
		$classes = scandir(dirname(__FILE__).'/classes');
		foreach ($classes as $class)
		{
			if (is_file(dirname(__FILE__).'/classes/'.$class))
			{
				$class_name = Tools::substr($class, 0, -4);
				// Check if class_name is an existing Class or not
				if (class_exists($class_name))
				{
					if (method_exists($class_name, 'install'))
						call_user_func(array($class_name, 'install'));
				}
			}
		}

		return true;
	}

	/**
	 * Uninstall DataBase table
	 * @return boolean if install was successfull
	 */
	private function uninstallSQL()
	{
		/*
		 * Uninstall All Object Model SQL via install function
		 */
		$classes = scandir(dirname(__FILE__).'/classes');
		foreach ($classes as $class)
		{
			if (is_file(dirname(__FILE__).'/classes/'.$class))
			{
				$class_name = Tools::substr($class, 0, -4);
				//Check if class_name is an existing Class or not
				if (class_exists($class_name))
				{
					if (method_exists($class_name, 'uninstall'))
						call_user_func(array($class_name, 'uninstall'));
				}
			}
		}

		return true;
	}

	############################################################################################################
	# Hook
	############################################################################################################

	/**
	 * Hooks registration
	 * @return boolean If hooks are registered
	 */
	private function registrationHook()
	{
		// List of hooks
		$hooks = array(
			'displayBeforeCarrier',
			'displayHeader',
			'actionCarrierProcess',
			'actionPaymentConfirmation',
			'actionOrderHistoryAddAfter'
		);

		$nb_hooks = count($hooks);

		$result = true;
		$i = 0;

		do
		{
			if (count($hooks) && array_key_exists($i, $hooks))
				$result &= $this->registerHook($hooks[$i]);

			$i++;
		}
		while ($result == true && $i < $nb_hooks);

		return $result;
	}

	/**
	 * Add CSS & JS files to header
	 */
	public function hookDisplayHeader()
	{
		$this->context->controller->addCss($this->getPathUri().'css/'.$this->name.'.css');
		$this->context->controller->addJs($this->getPathUri().'js/'.$this->name.'.js');
		$this->context->controller->addJqueryPlugin('fancybox');
	}


	public function hookDisplayBackOfficeHeader()
	{
		$this->checkStatus();
	}

	/**
	 * Add checkbox to carrier page
	 * @return mixed nothing | Smarty Template
	 */
	public function hookDisplayBeforeCarrier()
	{
		if ($this->accountIsActive())
			return;

		return $this->display(__FILE__, 'before-carrier.tpl');
	}

	/**
	 * Add cart to RA
	 * @param  array  $params Hooks paramas (cart)
	 * @return mixed          nothing
	 */
	public function hookActionCarrierProcess($params)
	{
		if ($this->accountIsActive())
			return;
		// Cart variable
		$cart = $params['cart'];
		// Get products
		$products = $cart->getProducts();
		// Get product RA
		$id_product = Configuration::get('RA_PRODUCT');
		$find = $find_product = false;
		// if checkbox was checked
		$checked = Tools::getValue('reforestaction');
		// Loop products cart
		if ($products && count($products))
		{
			foreach ($products as $product)
			{
				if ($product['id_product'] == $id_product)
				{
					$find = $product;
					break;
				}
			}
		}

		// Delete product if found and not checked
		if ($find && !$checked)
		{
			$cart->updateQty($find['cart_quantity'], $id_product, null, null, 'down');
			$model = ReforestActionModel::getInstanceByIdCart($cart->id);
			$model->delete();
		}
		else if ($checked)
		{

			if (!$find)
			{
				// GEt product informations
				$product = new Product((int)$id_product);

				$find['cart_quantity'] = 1;
				$find['total'] = $product->getPrice();
				$find['price_wt'] = $find['total'];
				$find['rate'] = $product->getTaxesRate();
				// Add in cart
				$cart->updateQty($find['cart_quantity'], $id_product);
			}

			$model             = ReforestActionModel::getInstanceByIdCart($cart->id);
			$model->total      = (float)$find_product['total'];
			$model->qty        = (int)$find['cart_quantity'];
			$model->price_exc  = (float)$find['price_wt'];
			$price_inc         = ($find['price_wt'] / (1 + $find['rate'] / 100));
			$model->price_inc  = (float)$price_inc;
			$model->newsletter = Tools::getValue('reforestaction_newsletter');

			$model->save();
		}
	}

	public function hookActionOrderHistoryAddAfter($params)
	{
		$order_history = $params['order_history'];

		$this->sendOrder($order_history->id_order, $order_history->id_order_state);
	}

	public function hookActionPaymentConfirmation($params)
	{
		$this->sendOrder($params['id_order']);
	}

	############################################################################################################
	# Administration
	############################################################################################################

	/**
	 * Admin display
	 * @return String Display admin content
	 */
	public function getContent()
	{
		Tools::redirectAdmin($this->context->link->getAdminLink('AdminReforestAction'));
	}

	############################################################################################################
	# Methodes
	############################################################################################################

	/**
	 * Init call
	 */
	public function initCall()
	{
		if (!$this->call instanceof ApiCaller)
		{
			require_once $this->getLocalPath().DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'RaApiCaller.php';
			$this->call = new RaApiCaller('http://localhost/api.reforestaction/', $this, 'reforestaction', 'apira');
		}
	}

	/**
	 * Check if account is enable
	 */
	private function accountIsActive()
	{
		if (!Configuration::get('RA_MERCHANT_KEY'))
			return false;

		$account_type = (int)Configuration::get('RA_MERCHANT_STATUS');

		return $account_type != ReforestAction::ACCOUNT_OK;
	}

	/**
	 * Create Reforest Action product
	 * @return int Created ID
	 */
	public function createRaProduct()
	{
		if (!$this->accountIsActive())
			return;

		$product = new Product(Configuration::get('RA_PRODUCT'));

		$languages = Language::getLanguages();

		foreach ($languages as $lang)
		{
			// Get id lang
			$id_lang = (int)$lang['id_lang'];
			// Name
			if ($lang['iso_code'] == 'fr')
				$product->name[$id_lang] = 'Produit Reforest Action';
			else
				$product->name[$id_lang] = 'Reforest Action Product';

			// Link
			$product->link_rewrite[$id_lang] = Tools::link_rewrite($product->name[$id_lang]);
		}

		// Get home category
		$id_category = (int)Configuration::get('PS_HOME_CATEGORY');
		// Default category
		$product->id_category_default = $id_category;

		// Hide in catalog
		$product->visibility = 'none';
		$product->indexed = false;
		$product->reference = 'reforestaction';
		$product->redirect_type = '404';
		$product->price = 0.99 / (1 + $product->getTaxesRate() / 100);

		// Save product
		$result = $product->save();

		// Associate category
		if ($result)
		{
			$product->addToCategories($id_category);
			StockAvailable::setQuantity($product->id, 0, 99999);
			Configuration::updateValue('RA_PRODUCT', $product->id);
		}
	}

	/**
	 * Check merchant status
	 */
	public function checkStatus()
	{
		if (!Configuration::get('RA_MERCHANT_KEY'))
			return false;

		$current_time = time();
		$last_check = Configuration::get('RA_LAST_CHECK');

		// Convert to seconds
		$duration = Configuration::get('RA_EVERY_HOUR') * 60 * 60;

		// if status never check or too old
		if ($last_check == false || (($current_time - $last_check) > $duration))
		{
			$this->initCall();
			$result = $this->call->getStatus();
			$current_status = Configuration::get('RA_MERCHANT_STATUS');

			// if no error
			if (!isset($result->error))
			{
				// Check status
				if ($result->status == true)
				{
					if ($current_status == ReforestAction::ACCOUNT_WAITING)
						$this->context->controller->confirmations[] = $this->l('Your account has been actived.');
					else if ($current_status == ReforestAction::ACCOUNT_BANNED)
						$this->context->controller->confirmations[] = $this->l('Your account has been unbanned.');

					$this->createRaProduct();

					

					if (!Configuration::get('RA_INSTALLATION'))
						Configuration::updateValue('RA_INSTALLATION', strftime('%Y-%m-%d %H:%M:%S')); // In hours
				}
				else
				{
					if ($current_status != ReforestAction::ACCOUNT_BANNED)
					{
						if ($result->message == 'NOT_AUTHORIZED')
							$this->context->controller->warnings[] = $this->l('Your account has not been verified by Reforest Action.');
						else if ($result->message == 'BANNED')
						{
							$this->context->controller->warnings[] = $this->l('Your account has been banned.');
							
						}
					}
				}
				Configuration::updateValue('RA_MERCHANT_STATUS', $result->status);
			}
			Configuration::updateValue('RA_LAST_CHECK', time());
		}
	}

	/**
	 * Make call to send order
	 * @param  int $id_order       Order ID
	 * @param  int $id_order_state State ID
	 */
	private function sendOrder($id_order, $id_order_state = null)
	{
		if (!is_null($id_order_state))
		{
			// Instance of state
			$order_state = new OrderState((int)$id_order_state);

			if (!$order_state->paid)
				return;
		}

		$order = new Order((int)$id_order);
		// get reforest action
		$reforestaction = ReforestActionModel::getInstanceByIdCart((int)$order->id_cart);

		// Check if exists && if not send
		if (Validate::isLoadedObject($reforestaction) && !$reforestaction->sent)
		{

			$reforestaction->sent = true;
			$reforestaction->date_sent = strftime('%Y-%m-%d %H:%M:%S');

			$customer = new Customer((int)$order->id_customer);

			$cart = new Cart($order->id_cart);

			$sum = $reforestaction->qty * $reforestaction->price_exc;

			$datas = array(
				'id_order'       => $id_order,
				'percent'        => $this->calculateRatio(),
				'merchant_key'   => Configuration::get('RA_MERCHANT_KEY'),
				'newsletter'     => $reforestaction->newsletter,
				'email'          => $customer->email,
				'date_sent'      => $reforestaction->date_sent,
				'paid'           => $reforestaction->date_sent,
				'invoiced'       => '',
				'sum'            => $sum,
				'newsletter' 	=> $reforestaction->newsletter,
				'firstname' 	=> $customer->firstname,
				'lastname'		=> $customer->lastname
			);

			$this->initCall();
			$result = $this->call->sendOrder($datas);

			if (!isset($result->error))
				$reforestaction->id_order_reforestaction = $result->id_order;

			$reforestaction->save();
		}
	}

	private function calculateRatio()
	{
		$date_to = Configuration::get('RA_INSTALLATION');
		$date_from = strftime('%Y-%m-%d %H:%M:%S');
		// count orders
		$orders = Order::getOrdersIdByDate($date_to, $date_from);
		// Count reforestactions
		$reforestactions = ReforestActionModel::getOrdersIdByDate($date_to, $date_from);
		// Get ratio
		$ratio = count($reforestactions) / count($orders);
		// get ratio in percent
		$ratio *= 100;

		return $ratio;
	}

	/**
	 * Translate error
	 * @param  string $error Error code
	 * @return string        Error message
	 */
	public function translateError($error, $order = false)
	{
		switch ($error)
		{
			case 'ALREADY_EXISTS':
				if (!$order)
					$message = $this->l('This email already exists.');
				else
					$message = $this->l('This order already exists.');
				break;

			case 'MISSING_DATA':
				$message = $this->l('Missing data in your call.');
				break;

			case 'UNEXPECTED_ERROR':
				$message = $this->l('Unexpected error.');
				break;

			case 'NOT_AUTHORIZED':
				$message = $this->l('Your account is not authorized at this time.');
				break;

			case 'MERCHANT_NOT_FOUND':
				$message = $this->l('Merchant not found.');
				break;

			case 'BANNED':
				$message = $this->l('Your merchant account is banned.');
				break;

			default:
				$message = $error;
				break;
		}

		return $message;
	}

	/**
	 * Get industries
	 * @return array industries
	 */
	public function getIndustries()
	{
		$industries = array(
			array(
				'key' => 'House, decoration',
				'name' => $this->l('House, decoration'),
			),
			array(
				'key' => 'Cosmetics',
				'name' => $this->l('Cosmetics'),
			),
			array(
				'key' => 'Shoes, accessories',
				'name' => $this->l('Shoes, accessories'),
			),
			array(
				'key' => 'Childrens',
				'name' => $this->l('Childrens'),
			),
			array(
				'key' => 'Food, drinks',
				'name' => $this->l('Food, drinks'),
			),
			array(
				'key' => 'Clothing',
				'name' => $this->l('Clothing'),
			),
			array(
				'key' => 'Sport',
				'name' => $this->l('Sport'),
			),
			array(
				'key' => 'Technology',
				'name' => $this->l('Technology'),
			),
			array(
				'key' => 'Entertainment',
				'name' => $this->l('Entertainment'),
			),
			array(
				'key' =>  'Others',
				'name' => $this->l('Others'),
			),
		);

		return $industries;
	}

	/**
	 * Get transations
	 * @return array Transactions
	 */
	public function getTransactions()
	{
		$transactions = array(
			array(
				'key' => 'Less 1 per day',
				'name' => $this->l('Less 1 per day')
			),
			array(
				'key' => 'Between 1 and 5 per day',
				'name' => $this->l('Between 1 and 5 per day')
			),
			array(
				'key' => 'Between 5 and 10 per day',
				'name' => $this->l('Between 5 and 10 per day')
			),
			array(
				'key' => 'Between 10 and 20 per day',
				'name' => $this->l('Between 10 and 20 per day')
			),
			array(
				'key' => 'More 20 per day',
				'name' => $this->l('More 20 per day')
			),
		);

		return $transactions;
	}

	public function getConfig($name)
	{
		if($this->dev)
			return $this->config['dev'][$name];
		else
			return $this->config['prod'][$name];

	}

}

?>