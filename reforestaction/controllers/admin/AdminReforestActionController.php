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
					'RA_MERCHANT_ADDRESS'     => array(
						'title' => $this->l('Address:'),
						'type'  => 'textarea',
						'desc'  => null,
						'disabled' => $disabled,
						'required' => true,
						'rows'  => 10,
						'cols'  => 80
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
	}

	/**
	 * Render options
	 */
	public function renderOptions()
	{
		$current_status = Configuration::get('RA_MERCHANT_STATUS');

		if ($current_status)
		{
			if ($current_status == ReforestAction::ACCOUNT_WAITING)
				$this->warnings[] = $this->l('Your account has not been verified by Reforest Action.');
			else if ($current_status == ReforestAction::ACCOUNT_BANNED)
				$this->errors[] = $this->l('Your account has been banned.');
		}

		return parent::renderOptions();
	}

	public function postProcess()
	{
		$this->module->checkStatus();
		$this->module->createRaProduct();

		parent::postProcess();
	}

	public function processUpdateOptions()
	{
		if (Configuration::get('RA_MERCHANT_STATUS') == false)
		{
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
				'address'        => Configuration::get('RA_MERCHANT_ADDRESS'),
				'industry'       => Configuration::get('RA_MERCHANT_INDUSTRY'),
				'transaction'    => Configuration::get('RA_MERCHANT_TRANSACTION'),
				'shop_uri'       => $this->context->shop->getBaseURL(),
			);

			$result = $this->module->call->createAccount($datas);

			// If create account successfull
			if ($result->error == false)
			{
				Configuration::updateValue('RA_MERCHANT_STATUS', ReforestAction::ACCOUNT_WAITING);
				Configuration::updateValue('RA_MERCHANT_ID', $result->id_merchant);
				Configuration::updateValue('RA_MERCHANT_KEY', $result->merchant_key);

				$this->module->checkStatus();

				$this->redirect_after = self::$currentIndex.'&conf=3&token='.$this->token;
			}
			else
				$this->errors[] = $this->module->translateError($result->message);
		}
		else
			$this->errors[] = $this->l('This informations are already saved.');
	}

}
