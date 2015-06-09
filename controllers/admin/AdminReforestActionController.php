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

class AdminReforestActionController extends ModuleAdminController
{

	public function __construct()
	{
		parent::__construct();

		// Activate bootstrap
		$this->bootstrap = true;

		$disabled = Configuration::get('RA_MERCHANT_STATUS') != false;

		$this->fields_options = array(
			'settings' => array(
				'title' => $this->l('Settings'),
				'image' => '../img/admin/cog.gif',
				'fields' => array(
					'RA_MERCHANT_COMPANY'     => array(
						'title' => $this->l('Company name:'),
						'type'  => 'text',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'size'  => 80
					),
					'RA_MERCHANT_PHONE'       => array(
						'title' => $this->l('Phone:'),
						'type'  => 'text',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'size'  => 80
					),
					'RA_MERCHANT_ADDRESS_1'     => array(
						'title' => $this->l('Address 1:'),
						'type'  => 'text',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'size'  => 80
					),
					'RA_MERCHANT_ADDRESS_2'     => array(
						'title' => $this->l('Address 2:'),
						'type'  => 'text',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => false,
						'size'  => 80
					),
					'RA_MERCHANT_POSTAL_CODE'     => array(
						'title' => $this->l('Postal code :'),
						'type'  => 'text',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'size'  => 80
					),
					'RA_MERCHANT_CITY'     => array(
						'title' => $this->l('City :'),
						'type'  => 'text',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'size'  => 80
					),
					'RA_MERCHANT_INDUSTRY'    => array(
						'title' => $this->l('Industry:'),
						'type'  => 'select',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'identifier' => 'key',
						'list'  => $this->module->getIndustries()
					),
					'RA_MERCHANT_TRANSACTION' => array(
						'title' => $this->l('Transaction:'),
						'type'  => 'select',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'identifier' => 'key',
						'list'  => $this->module->getTransactions()
					),
					'RA_MERCHANT_FIRSTNAME'   => array(
						'title' => $this->l('First name:'),
						'type'  => 'text',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'size'  => 80
					),
					'RA_MERCHANT_LASTNAME'    => array(
						'title' => $this->l('Last name:'),
						'type'  => 'text',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'size'  => 80
					),
					'RA_MERCHANT_EMAIL'       => array(
						'title' => $this->l('Address email:'),
						'type'  => 'text',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'size'  => 80
					),
				)
			)
		);

		if (!$disabled)
			$this->fields_options['settings']['submit'] = array(
				'title' => $this->l('Save')
			);

	}

	/**
	 * Install tabs
	 * @param  integer $menu_id     parent id
	 * @param  string  $module_name module name
	 * @return boolean              if installation successfull
	 */
	public static function install($menu_id, $module_name)
	{
		return ReforestactionTotAdminTabHelper::addAdminTab(array(
			'id_parent'    => $menu_id,
			'className'    => 'AdminReforestAction',
			'default_name' => 'Reforest\'Action',
			'name'         => 'Reforest\'Action',
			'position'     => 0,
			'active'       => true,
			'module'       => $module_name,
		));
	}

	public function initToolbar()
	{
		parent::initToolbar();

		if (Configuration::get('RA_MERCHANT_STATUS') != false)
			unset($this->toolbar_btn['save']);

		$this->addCss($this->module->getPathUri().'views/css/configure.css');

		$this->toolbar_btn = array(
			'preview' => array(
				'href' => $this->context->link->getAdminLink('AdminReforestActionList'),
				'desc' => $this->l('List')
			)
		);

		$this->page_header_toolbar_btn = array(
			'preview' => array(
				'href' => $this->context->link->getAdminLink('AdminReforestActionList'),
				'desc' => $this->l('List')
			)
		);
	}

	/**
	 * Render options
	 */
	public function renderOptions()
	{
		$this->addJs($this->module->getLocalPath().'views/js/admin.js');

		$current_status = Configuration::get('RA_MERCHANT_STATUS');

		if ($current_status)
		{
			if ($current_status == ReforestAction::ACCOUNT_WAITING)
				$this->warnings[] = $this->l('Your account has not been verified by Reforest Action.');
			else if ($current_status == ReforestAction::ACCOUNT_WAITING_SLIMPAY)
			{
				$url = $this->module->getConfig('url_to_slimpay');
				$url .= '?id_merchant='.Configuration::get('RA_MERCHANT_ID');
				$url .= '&merchant_key='.Configuration::get('RA_MERCHANT_KEY');

				$this->warnings[] = $this->l('Please click').'
					<a href="'.$url.'" class="sign_the_mandate" target="_blank"> '.$this->l('here').' </a> '.
					$this->l('to sign the mandate.');
			}
			else if ($current_status == ReforestAction::ACCOUNT_BANNED)
			{
				$this->errors[] = $this->l('The module has been disabled by Reforest Action. For any further information, you can contact us on:').'
					<a href="mailto:contact@reforestaction.com">contact@reforestaction.com</a>';
			}
		}

		if (Configuration::get('RA_MERCHANT_STATUS') && Configuration::get('RA_MERCHANT_STATUS') == ReforestAction::ACCOUNT_OK)
			$this->module->getActiveText();

		return parent::renderOptions();
	}

	public function postProcess()
	{
		if (Tools::getValue('product') == 'force')
		{
			$this->module->createRaProduct(true);
			$product = new Product((int)Configuration::get('RA_PRODUCT'));

			if (Validate::isLoadedObject($product))
				$this->redirect_after = self::$currentIndex.'&conf=3&token='.$this->token;
			else
				$this->errors[] = $this->l('Impossible to create product');
		}

		$this->module->checkStatus();

		parent::postProcess();
	}

	public function processUpdateOptions()
	{
		if (Configuration::get('RA_MERCHANT_STATUS') == false)
		{

			if ($postcode = Tools::getValue('RA_MERCHANT_POSTAL_CODE'))
			{
				$country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
				if (!$country->checkZipCode($postcode))
				{
					$this->errors[] = sprintf(
							Tools::displayError('The Zip/Postal code you\'ve entered is invalid. It must follow this format: %s'),
							str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $country->zip_code_format)))
					);
					return;
				}
			}

			parent::processUpdateOptions();

			if (count($this->errors))
				return;

			$this->module->initCall();

			$datas = array(
				'firstname'      => Configuration::get('RA_MERCHANT_FIRSTNAME'),
				'lastname'       => Configuration::get('RA_MERCHANT_LASTNAME'),
				'email'          => Configuration::get('RA_MERCHANT_EMAIL'),
				'ps_version'     => _PS_VERSION_,
				'module_version' => $this->module->version,
				'company'        => Configuration::get('RA_MERCHANT_COMPANY'),
				'phone'          => Configuration::get('RA_MERCHANT_PHONE'),
				'address_1'      => Configuration::get('RA_MERCHANT_ADDRESS_1'),
				'address_2'      => Configuration::get('RA_MERCHANT_ADDRESS_2'),
				'postal_code' 	 => Configuration::get('RA_MERCHANT_POSTAL_CODE'),
				'city' 			 => Configuration::get('RA_MERCHANT_CITY'),
				'industry'       => Configuration::get('RA_MERCHANT_INDUSTRY'),
				'transaction'    => Configuration::get('RA_MERCHANT_TRANSACTION'),
				'shop_uri'       => $this->context->shop->getBaseURL(),
				'country_code'   => Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'))
			);

			$result = $this->module->call->createAccount($datas);

			// If create account successfull
			if (is_object($result) && isset($result->error) && $result->error == false)
			{
				Configuration::updateValue('RA_MERCHANT_ID', $result->id_merchant);
				Configuration::updateValue('RA_MERCHANT_KEY', $result->merchant_key);

				$this->module->checkStatus(true);

				$this->redirect_after = self::$currentIndex.'&conf=3&token='.$this->token;
			}
			else
				$this->errors[] = $this->module->translateError($result->message);
		}
		else
			$this->errors[] = $this->l('This informations are already saved.');
	}

}
