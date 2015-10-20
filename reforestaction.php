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

if (!defined('_PS_VERSION_'))
	exit;

class ReforestAction extends Module
{

	const ACCOUNT_WAITING = 1;
	const ACCOUNT_BANNED  = 2;
	const ACCOUNT_OK      = 3;
	const ACCOUNT_WAITING_SLIMPAY = 4;

	const UPDATE_CART = false;

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
		$this->tab = 'advertising_marketing';
		$this->version = '1.0.3';
		$this->author = '202-ecommerce';

		parent::__construct();

		$this->displayName = $this->l('Purchase responsable');
		$this->description = $this->l('With Reforest\'action, your customers plant a tree in the world to offset the CO2 emissions of their purchases. They do this for the planet and allow you to increase your visibility on social networks.');

		$this->includeFiles();

		if (!extension_loaded('curl'))
			$this->warning .= $this->l('To use your module, please activate cURL (PHP extension)');

		$this->env = 'prod';
		$this->config = array(
			'local' => array(
				'url_to_slimpay' => 'http://localhost/api.reforestaction/slimpay/api/make_mandat_request.php',
				'host' => 'http://localhost/api.reforestaction/',
			),
			'prod' => array(
				'url_to_slimpay' => 'http://api.reforestaction.com/slimpay/api/make_mandat_request.php',
				'host' => 'http://api.reforestaction.com/',
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
				if ($class_name != 'index' && !class_exists($class_name))
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
				if ($class_name != 'index' && !class_exists($class_name))
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
			if ($controller != 'index.php' && is_file(dirname(__FILE__).'/controllers/admin/'.$controller))
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
			if ($class != 'index.php' && is_file(dirname(__FILE__).'/classes/'.$class))
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
			if ($class != 'index.php' && is_file(dirname(__FILE__).'/classes/'.$class))
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
			'actionOrderHistoryAddAfter',
			'actionCartSave',
			'displayAdminReforestActionOptions'
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
		if (!$this->active)
			return;

		$this->context->controller->addJqueryPlugin('fancybox');
		$this->context->controller->addCss($this->getPathUri().'views/css/'.$this->name.'.css');
		$this->context->controller->addJs($this->getPathUri().'views/js/'.$this->name.'.js');
	}


	public function hookDisplayBackOfficeHeader()
	{
		if (!$this->active)
			return;

		$this->checkStatus();
	}

	/**
	 * Add checkbox to carrier page
	 * @return mixed nothing | Smarty Template
	 */
	public function hookDisplayBeforeCarrier()
	{
		if (!$this->active)
			return;

		$this->checkStatus(true);

		if (!$this->accountIsActive())
			return;

		$ra_product = new Product((int)Configuration::get('RA_PRODUCT'));

		$this->context->smarty->assign('ra_product_price', $ra_product->getPrice());
		$this->context->smarty->assign('ra_product_price_wt_tax', $ra_product->getPrice(false, null));

		if ($this->context->language->id == Language::getIdByIso('fr'))
			$ra_logo = 'logo-fr.png';
		else
			$ra_logo = 'logo-en.png';

		$this->context->smarty->assign('ra_logo', $ra_logo);
		$this->context->smarty->assign('model', ReforestActionModel::getInstanceByIdCart($this->context->cart->id));
		$this->context->smarty->assign('id_reforestaction', $ra_product->id);
		$this->context->smarty->assign('ps_version_15', version_compare(_PS_VERSION_, '1.6', '<'));
		$this->context->smarty->assign('reforestaction_link', $this->getPathUri().'ajax/call.php');

		return $this->display(__FILE__, 'before-carrier.tpl');
	}

	/**
	 * Add cart to RA
	 * @param  array  $params Hooks paramas (cart)
	 * @return mixed          nothing
	 */
	public function hookActionCarrierProcess($params)
	{
		if (!$this->active || !$this->accountIsActive())
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
			$this->no_check = true;
		}
		else if ($checked)
		{
			$this->no_check = true;

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
		if (!$this->active)
			return;

		$order_history = $params['order_history'];

		$this->sendOrder($order_history->id_order, $order_history->id_order_state);
	}

	public function hookActionPaymentConfirmation($params)
	{
		if (!$this->active)
			return;

		$this->sendOrder($params['id_order']);
	}

	public function hookActionCartSave()
	{
		if (!$this->active || (isset($this->no_check) && $this->no_check) || !Validate::isLoadedObject($this->context->cart))
			return;

		$cart = $this->context->cart;

		$model = ReforestActionModel::getInstanceByIdCart($cart->id);

		$products = $cart->getProducts();
		$find = false;
		$ra_product_id = (int)Configuration::get('RA_PRODUCT');

		if (is_array($products) && count($products))
		{
			foreach ($products as $product)
			{
				if ($product['id_product'] == $ra_product_id)
				{
					$find = $product;
					break;
				}
			}
		}

		if (!$find && Validate::isLoadedObject($model))
			$model->delete();
		else if ($find && !Validate::isLoadedObject($model))
			$cart->updateQty((int)$find['cart_quantity'], $ra_product_id, null, null, 'down');
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
			$this->call = new RaApiCaller($this->getConfig('host'), $this, 'reforestaction', 'apira');
		}
	}

	/**
	 * Check if account is enable
	 */
	private function accountIsActive($check_product = true)
	{
		if (!Configuration::get('RA_MERCHANT_KEY'))
			return false;
		// Get account type
		$account_type = (int)Configuration::get('RA_MERCHANT_STATUS');

		// Check if product exists
		$product = new Product((int)Configuration::get('RA_PRODUCT'));

		return $account_type == ReforestAction::ACCOUNT_OK && (($check_product == true && Validate::isLoadedObject($product)) || $check_product == false);
	}

	/**
	 * Create Reforest Action product
	 * @return int Created ID
	 */
	public function createRaProduct($force = false)
	{
		if ($this->accountIsActive(false) && !$force)
			return;

		$product = new Product(Configuration::get('RA_PRODUCT'));

		$languages = Language::getLanguages();

		foreach ($languages as $lang)
		{
			// Get id lang
			$id_lang = (int)$lang['id_lang'];
			// Name
			if ($lang['iso_code'] == 'fr')
			{
				$product->name[$id_lang]        = '1 arbre planté avec Reforest\'Action';
				$product->description[$id_lang] = '
					<p>
						En cochant l\'option <strong>Achat Responsable</strong> de Reforest\'Action, 
						<strong>vous plantez un arbre</strong>
						sur un de nos projets de reforestation pour compenser les émissions de CO2 de votre achat sur ce site Internet.
					</p>
				
					<p>
						1 arbre stocke en moyenne 150 kg de CO2 pendant ses 30 premières années de vie, 
						soit plus que les émissions de C02 issues de la fabrication de la plupart des produits achetés sur Internet.
					</p>
					<p>
						Suite à votre achat, vous recevrez par email un <strong>certificat de plantation</strong> 
						et la présentation du projet de reforestation auquel vous avez participé.
					</p>';
				$product->description_short[$id_lang] = '
					<p>Un arbre est planté sur un projet de reforestation Reforest\'Action pour compenser les émissions de CO2 de votre achat sur ce site Internet.<br>
1 arbre stocke en moyenne 150 kg de CO2, soit plus que les émissions de C02 issues de la fabrication de la plupart des produits achetés sur Internet.
<br>Suite à votre achat, vous recevrez par email un certificat de plantation.</p>';
			}
			else
			{
				$product->name[$id_lang]        = '1 tree planted with Reforest\'Action';
				$product->description[$id_lang] = '
				<p>
					By selecting the option to <strong>Purchase Responsable</strong> Reforest\'Action, 
					<strong>you plant a tree</strong> on one of our reforestation projects to offset the CO2 emissions of your purchase on this website.
				</p>
				<p>
					1 tree stores on average 150 kg of CO2 during its first 30 years of life, 
					more than the C02 emissions from the production of most goods purchased over the Internet.
				</p>
				<p>
					After your purchase you will receive by email a <strong>certificate of planting</strong> 
					and the presentation of the reforestation project in which you participated.
				</p>';
				$product->description_short[$id_lang] = '
				<p>A tree is planted on a Reforest’Action project to compensate the CO2 emissions of the product purchased.<br>
One tree sequesters in average 150kg of CO2, more than the manufacturing emissions of most products sold on the web.<br>
Following your purchase, you will receive a plantation certificate by email.</p>';
			}

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
		$product->price = sprintf('%0.9f', (0.99 / (1 + $product->getTaxesRate() / 100)));

		// Save product
		$result = $product->save();

		// Associate category
		if ($result)
		{
			$product->addToCategories($id_category);
			StockAvailable::setQuantity($product->id, 0, 99999);

			$this->createImage($product);

			Configuration::updateValue('RA_PRODUCT', $product->id);
		}
	}

	private function createImage($product)
	{
		$pictures = array(
			array(
				'path' => 'carrier-logo.png',
				'cover' => true
			),
			array(
				'path' => 'les-bordes-11.jpg',
			),
			array(
				'path' => 'plantation-hommes.jpg',
			),
			array(
				'path' => 'plantation-arbre.jpg',
			)
		);

		foreach ($pictures as $img)
		{

			$image = new Image();
			$image->id_product = $product->id;
			$image->position = Image::getHighestPosition($product->id) + 1;
			$image->legend = $product->name;
			$image->cover = isset($img['cover']) && $img['cover'] == true;
			$image->save();

			$result = $this->copyImage($product, $image, 'auto', $img['path']);

			if (!isset($result['success']))
			{
				$handle = fopen(dirname(__FILE__).'/Log upload.txt', 'a+');
				fwrite($handle, $result['error']."\n");
				fclose($handle);
			}
		}
	}

	private function copyImage($product, $image, $method = 'auto', $img_name = 'logo.png')
	{
		$tmp_name = _PS_TMP_IMG_DIR_.$img_name;
		copy($this->getLocalPath().'views'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$img_name, $tmp_name);

		if (!$new_path = $image->getPathForCreation())
			return array('error' => Tools::displayError('An error occurred during new folder creation'));
		elseif (!ImageManager::resize($tmp_name, $new_path.'.'.$image->image_format))
			return array('error' => Tools::displayError('An error occurred while copying image.'));
		elseif ($method == 'auto')
		{
			$images_types = ImageType::getImagesTypes('products');
			foreach ($images_types as $image_type)
			{
				if (!ImageManager::resize(
						$tmp_name,
						$new_path.'-'.Tools::stripslashes($image_type['name']).'.'.$image->image_format,
						$image_type['width'],
						$image_type['height'], $image->image_format))
					return array('error' => Tools::displayError('An error occurred while copying image:').' '.Tools::stripslashes($image_type['name']));
			}
		}
		unlink($tmp_name);
		Hook::exec('actionWatermark', array('id_image' => $image->id, 'id_product' => $product->id));

		if (!$image->update())
			return array('error' => Tools::displayError('Error while updating status'));

		return array('success' => true);
	}

	/**
	 * Check merchant status
	 */
	public function checkStatus($force = false)
	{
		if (!Configuration::get('RA_MERCHANT_KEY'))
			return false;

		$current_time = time();
		$last_check = Configuration::get('RA_LAST_CHECK');

		// Convert to seconds
		$duration = Configuration::get('RA_EVERY_HOUR') * 60 * 60;
		$current_status = Configuration::get('RA_MERCHANT_STATUS');

		// if status never check or too old
		if ($force || $last_check == false || (($current_time - $last_check) > $duration))
		{
			$this->initCall();
			$result = $this->call->getStatus();

			if (is_object($result) || !is_null($result))
			{
				// if no error
				if (!isset($result->error) && $result->status != $current_status)
				{
					// Check status
					if ($result->status == true)
					{
						if ($current_status == ReforestAction::ACCOUNT_WAITING)
							$this->context->controller->confirmations[] = $this->l('Your account has been actived.');
						else if ($current_status == ReforestAction::ACCOUNT_BANNED)
							$this->context->controller->confirmations[] = $this->l('The module has been re-enabled by Reforest\'Action.');

						$this->createRaProduct();

						if (!Configuration::get('RA_INSTALLATION'))
							Configuration::updateValue('RA_INSTALLATION', strftime('%Y-%m-%d %H:%M:%S')); // In hours
					}
					else
					{
						if ($current_status != ReforestAction::ACCOUNT_BANNED)
						{
							$controller = $this->context->controller;
							if ($result->message == 'NOT_AUTHORIZED')
								$controller->warnings[] = $this->l('Your account has not been verified by Reforest Action.');
							else if ($result->message == 'BANNED')
								$controller->warnings[] = $this->l('The module has been disabled by Reforest\'Action. For any further information, you can contact us on:').
									'<a href="mailto:contact@reforestaction.com">contact@reforestaction.com</a>';
						}
					}
				}

				if ($result->status)
					Configuration::updateValue('RA_MERCHANT_STATUS', $result->status);
				
				Configuration::updateValue('RA_LAST_CHECK', time());
			}
			else
				$this->context->controller->errors[] = $this->l('Reforest\'Action Server shutting down.');
		}

		$product = new Product((int)Configuration::get('RA_PRODUCT'));

		if (!Validate::isLoadedObject($product))
			$this->context->controller->warnings[] = $this->l('Your ReforestAction product has been delected, click the following link to recreate it:').
				' <a href="'.$this->context->link->getAdminLink('AdminReforestAction').'&product=force">'.$this->l('here').'</a>';
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

		if (!Validate::isLoadedObject($reforestaction))
			return;

		$reforestaction->id_order = $id_order;
		$reforestaction->save();

		// Check if exists && if not send
		if (!$reforestaction->sent)
		{

			$reforestaction->date_sent = strftime('%Y-%m-%d %H:%M:%S');

			$customer = new Customer((int)$order->id_customer);

			$sum = $reforestaction->qty * $reforestaction->price_exc;

			$datas = array(
				'id_order'       => $id_order,
				'percent'        => $this->calculateRatio(),
				'merchant_key'   => Configuration::get('RA_MERCHANT_KEY'),
				'email'          => $customer->email,
				'date_sent'      => $reforestaction->date_sent,
				'paid'           => $reforestaction->date_sent,
				'invoiced'       => '',
				'sum'            => $sum,
				'newsletter' 	 => $reforestaction->newsletter,
				'firstname' 	 => $customer->firstname,
				'lastname'		 => $customer->lastname,
				'module_version' => $this->version
			);

			$this->initCall();
			$result = $this->call->sendOrder($datas);

			if (is_object($result) || !is_null($result))
			{
				if (!isset($result->error))
				{
					$reforestaction->sent = true;
					$reforestaction->id_order_reforestaction = $result->id_order;
				}
			}
			else
				$this->context->controller->errors[] = $this->l('Reforest\'Action Server shutting down.');

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
				$message = $this->l('The module has been disabled by Reforest\'Action. For any further information, you can contact us on:').
					'<a href="mailto:contact@reforestaction.com">contact@reforestaction.com</a>';
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
				'key' => '',
				'name' => $this->l('--- CHOOSE ---')
			),
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
				'key' => 'Children',
				'name' => $this->l('Children'),
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
				'key' => '',
				'name' => $this->l('--- CHOOSE ---')
			),
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
		return $this->config[$this->env][$name];
	}

	public function hookDisplayAdminReforestActionOptions()
	{
		$this->context->smarty->assign(array(
			'module_dir' => $this->_path,
			'display_video' => $this->context->language->id == Language::getIdByIso('fr')
		));

		return $this->display(__FILE__, 'presentation.tpl');
	}

	public function getActiveText()
	{
		return $this->display(__FILE__, 'active.tpl');
	}

}

?>