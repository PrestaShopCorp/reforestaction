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

class AdminReforestActionListController extends ModuleAdminController
{

	public function __construct()
	{
		$this->table = 'reforestaction';
		$this->className = 'ReforestActionModel';

		parent::__construct();

		// Activate bootstrap
		$this->bootstrap = true;
	}

	public function renderList()
	{
		$this->list_no_link = true;

		$this->fields_list = array(
			'id_order' => array(
				'title' => $this->l('Order ID'),
			),
			'qty' => array(
				'title' => $this->l('Quantity'),
			),
			'sent' => array(
				'title' => $this->l('Sent'),
				'type' => 'bool',
				'active' => 'status',
				'activeVisu' => true,
			),
			'date_sent' => array(
				'title' => $this->l('Date sent'),
				'type' => 'datetime',
			),
			'date_add' => array(
				'title' => $this->l('Date'),
				'type' => 'datetime',
			),
		);

		return parent::renderList();
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
			'className'    => 'AdminReforestActionList',
			'default_name' => 'Reforest\'Action list',
			'name'         => 'Reforest\'Action list',
			'position'     => 0,
			'active'       => true,
			'module'       => $module_name,
		));
	}

	public function initToolbar()
	{
		parent::initToolbar();

		if (version_compare(_PS_VERSION_, '1.6', '<'))
			$this->addCss($this->module->getPathUri().'views/css/configure.css');

		$this->toolbar_btn = array(
			'configure' => array(
				'href' => $this->context->link->getAdminLink('AdminReforestAction'),
				'desc' => $this->l('Configure')
			)
		);

		$this->page_header_toolbar_btn = array(
			'configure' => array(
				'href' => $this->context->link->getAdminLink('AdminReforestAction'),
				'desc' => $this->l('Configure')
			)
		);

		unset($this->toolbar_btn['add']);
		unset($this->toolbar_btn['new']);
	}
}

?>