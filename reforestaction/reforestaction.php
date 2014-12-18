<?php 

if( !defined ('_PS_VERSION_') )
	exit;

/* Import models */
$classes = scandir(dirname(__FILE__).'/classes');
foreach ($classes as $class)
{
	if(is_file(dirname(__FILE__).'/classes/'.$class))
	{
		$class_name = substr($class, 0, -4);
		//Check if class_name is an existing Class or not
		if(!class_exists($class_name) && $class_name != 'index')
			require_once(dirname(__FILE__).'/classes/'.$class_name.'.php');
	}
}

/* Import helpers */
$classes = scandir(dirname(__FILE__).'/classes/helper');
foreach ($classes as $class)
{
	if(is_file(dirname(__FILE__).'/classes/helper/'.$class))
	{
		$class_name = substr($class, 0, -4);
		//Check if class_name is an existing Class or not
		if(!class_exists($class_name) && $class_name != 'index')
			require_once(dirname(__FILE__).'/classes/helper/'.$class_name.'.php');
	}
}

class ReforestAction extends Module
{

	const ACCOUNT_WAITING = 1;
	const ACCOUNT_BANNED  = 2;
	const ACCOUNT_OK      = 3;

	/**
	 * Module link in BO
	 * @var String
	 */
	private $_link;

	/**
	 * Api Call
	 * @var ApiCaller
	 */
	private $_call;

	/**
	 * Constructor of module
	 */
	public function __construct()
	{

		$this->name = 'reforestaction';
		$this->tab = 'other_modules';
		$this->version = '0.0.1';
		$this->author = '202-ecommerce';

		parent::__construct();

		$this->displayName = $this->l('Reforest Action');
		// $this->description = $this->l('');

		// Check upgrade if enabled and installed
		if (self::isInstalled($this->name) && self::isEnabled($this->name))
			$this->upgrade();

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

		// Registration hook
		if (!$this->registrationHook())
			return false;

		return true;
	}

	/**
	 * Upgrade if necessary
	 */
	public function upgrade()
	{
		// Configuration name
		$cfgName = strtoupper($this->name.'_version');
		// Get latest version upgraded
		$version = Configuration::getGlobalValue($cfgName);
		// If the first time OR the latest version upgrade is older than this one
		if ($version === false || version_compare($version, $this->version, '<'))
		{
			// Upgrade in DataBase the new version
			Configuration::updateGlobalValue($cfgName, $this->version);
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

		// Uninstall default
		if (!parent::uninstall())
			return false;

		return true;
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
			if(is_file(dirname(__FILE__).'/classes/'.$class))
			{
				$class_name = substr($class, 0, -4);
				//Check if class_name is an existing Class or not
				if(class_exists($class_name))
				{
					if(method_exists($class_name, 'install'))
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
			if(is_file(dirname(__FILE__).'/classes/'.$class))
			{
				$class_name = substr($class, 0, -4);
				//Check if class_name is an existing Class or not
				if(class_exists($class_name))
				{
					if(method_exists($class_name, 'uninstall'))
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

		$result = true;
		$i = 0;

		do
		{
			if (count($hooks) && array_key_exists($i, $hooks))
				$result &= $this->registerHook($hooks[$i]);

			$i++;
		}
		while ($result == true && $i < count($hooks));
		
		return $result;
	}

	/**
	 * Add CSS & JS files to header
	 */
	public function hookDisplayHeader()
	{
		$this->context->controller->addCss($this->getPathUri().'views/css/'.$this->name.'.css');
		$this->context->controller->addJs($this->getPathUri().'views/js/'.$this->name.'.js');
		$this->context->controller->addJqueryPlugin('fancybox');
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
				$find['rate'] = $product->getTaxesRate();;
				// Add in cart
				$cart->updateQty($find['cart_quantity'], $id_product);
			}

			$model             = ReforestActionModel::getInstanceByIdCart($cart->id);
			$model->total      = (float)$find_product['total'];
			$model->qty        = (int)$find['cart_quantity'];
			$model->price_exc  = (float)$find['price_wt'];
			$model->price_inc  = (float)($find['price_wt'] / (1 + $find['rate'] / 100));
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
		// Activate bootstrap
		$this->bootstrap = true;

		// Suffix to link
		$suffixLink = '&configure='.$this->name.'&token='.Tools::getValue('token').'&tab_module='.$this->tab.'&module_name='.$this->name;

		// Base
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$this->_link = 'index.php?controller='.Tools::getValue('controller').$suffixLink;
		else
			$this->_link = 'index.php?tab='.Tools::getValue('tab').$suffixLink;

		$this->postProcess();

		$current_status = Configuration::get('RA_MERCHANT_STATUS');

		// Assigns
		$datas = array(
			'module_link'     => $this->_link,
			'merchant_status' => $current_status,
		);

		if ($current_status)
		{
			if ($current_status == ReforestAction::ACCOUNT_WAITING)
				$this->context->controller->warnings[] = $this->l('Your account has not been verified by Reforest Action.');
			else if ($current_status == ReforestAction::ACCOUNT_BANNED)
				$this->context->controller->errors[] = $this->l('Your account has been banned.');
		}

		$this->context->smarty->assign($datas);
		// Form contnet
		$form_content = $this->display(__FILE__, 'admin-form.tpl');
		// Assign form content
		$this->context->smarty->assign('form_content', $form_content);

		if (version_compare(_PS_VERSION_, '1.6', '<'))
			$return =  $this->display(__FILE__, 'admin.tpl');
		else
			$return =  $this->display(__FILE__, 'admin_16.tpl');

		return $return;
	}

	/**
	 * Processing post in BO
	 */
	public function postProcess()
	{
		$this->checkStatus();
		$this->createRaProduct();

		// check if form has been sent
		if (Tools::isSubmit('btnSubmit'))
		{
			// Check if all field is not empty
			if (!Tools::getValue('company_name')
				|| !Tools::getValue('phone')
				|| !Tools::getValue('address')
				|| !Tools::getValue('industry')
				|| !Tools::getValue('transaction')
				|| !Tools::getValue('firstname')
				|| !Tools::getValue('lastname')
				|| !Tools::getValue('email'))
			{
				$this->context->controller->errors[] = $this->l('All fields are required.');
			}
			else
			{
				$current_status = Configuration::get('RA_MERCHANT_STATUS');

				if ($current_status != false)
				{
					$this->context->controller->errors[] = $this->l('This informations are already saved.');
					return;
				}

				Configuration::updateValue('RA_MERCHANT_EMAIL',     Tools::getValue('email'));
				Configuration::updateValue('RA_MERCHANT_FIRSTNAME', Tools::getValue('firstname'));
				Configuration::updateValue('RA_MERCHANT_LASTNAME',  Tools::getValue('lastname'));
				Configuration::updateValue('RA_EVERY_HOUR',         12); // In hours

				$this->initCall();

				$datas = array(
					'firstname'      => Tools::getValue('firstname'),
					'lastname'       => Tools::getValue('lastname'),
					'email'          => Tools::getValue('email'),
					'ps_version'     => _PS_VERSION_,
					'module_version' => $this->version,
					'company'        => Tools::getValue('company_name'),
					'phone'          => Tools::getValue('phone'),
					'address'        => Tools::getValue('address'),
					'industry'       => Tools::getValue('industry'),
					'transaction'    => Tools::getValue('transaction'),
					'shop_uri'       => $this->context->shop->getBaseURL(),
					'random'         => rand(1, 100), // TODO : delete
				);

				$result = $this->_call->createAccount($datas);

				// If create account successfull
				if ($result->error == false)
				{
					Configuration::updateValue('RA_MERCHANT_STATUS', ReforestAction::ACCOUNT_WAITING);
					Configuration::updateValue('RA_MERCHANT_ID',     $result->id_merchant);
					Configuration::updateValue('RA_MERCHANT_KEY',    $result->merchant_key);

					$this->checkStatus();

					Tools::redirectAdmin($this->_link.'&conf=3');
				}
				else
				{
					$this->context->controller->errors[] = $this->translateError($result->message);
					return;
				}

				// TODO création du produit
			}
		}
	}

	############################################################################################################
	# Methodes
	############################################################################################################
	
	/**
	 * Init call
	 */
	private function initCall()
	{
		if (!$this->_call instanceof ApiCaller)
		{
			require_once $this->getLocalPath().DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'RaApiCaller.php';
			$this->_call = new RaApiCaller('http://localhost/reforestaction/', $this);
		}
	}
	
	/**
	 * Check if account is enable
	 */
	private function accountIsActive()
	{
		$account_type = (int)Configuration::get('RA_MERCHANT_STATUS');

		return $account_type != ReforestAction::ACCOUNT_OK;
	}

	/**
	 * Create Reforest Action product
	 * @return int Created ID
	 */
	private function createRaProduct()
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
	private function checkStatus()
	{
		$current_time = time();
		$last_check = Configuration::get('RA_LAST_CHECK');

		// Convert to seconds
		$duration = Configuration::get('RA_EVERY_HOUR') * 60 * 60;

		// if status never check or too old
		if ($last_check == false || (($current_time - $last_check) > $duration))
		{
			$this->initCall();
			$result = $this->_call->getStatus();
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

					Configuration::updateValue('RA_MERCHANT_STATUS', ReforestAction::ACCOUNT_OK);

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
							Configuration::updateValue('RA_MERCHANT_STATUS', ReforestAction::ACCOUNT_BANNED);
						}
					}
				}
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
			$reforestaction->date_sent = strftime("%Y-%m-%d %H:%M:%S");

			$customer = new Customer((int)$order->id_customer);

			$datas = array(
				'id_order'       => $id_order,
				'pourcentage'    => $this->calculateRatio(),
				'merchant_state' => Configuration::get('RA_MERCHANT_STATUS'),
				'merchant_key'   => Configuration::get('RA_MERCHANT_KEY'),
				'newsletter'     => $reforestaction->newsletter,
				'email'          => $customer->email,
			);

			$this->initCall();
			$result = $this->_call->sendOrder($datas);

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
	private function translateError($error, $order = false)
	{
		switch ($error) {
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

}

?>