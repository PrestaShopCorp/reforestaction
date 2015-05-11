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

class ReforestactionTotAdminTabHelper
{
	/**
	 * Function to delete admin tabs from a menu with the module name
	 * @param  string $name name of the module to delete
	 * @return void       
	 */
	public static function deleteAdminTabs($module_name)
	{
		$tabs = Tab::getCollectionFromModule($module_name);

		$result = true;

		if ($tabs && count($tabs))
		{
			foreach ($tabs as $tab)
			{
				if (!is_object($tab))
					$tab = new Tab((int)$tab['id_tab']);

				$result &= $tab->delete();
			}
		}

		return $result;
	}

	/**
	 * Add admin tabs in the menu
	 * @param Array $tabs 
	 *        Array[
	 *        	Array[
	 *        		id_parent => 0 || void
	 *        		className => Controller to link to
	 *        		module => modulename to easily delete when uninstalling
	 *        		name => name to display
	 *        		position => position
	 *        	]
	 *        ]
	 */
	public static function addAdminTab($t)
	{
		$id_parent = isset($t['id_parent']) ? $t['id_parent'] : self::getAdminTabIDByClassName($t['classNameParent']);

		$tab = new Tab(Tab::getIdFromClassName($t['className']));
		$tab->name = array(Configuration::get('PS_LANG_DEFAULT') => $t['name']);
		$tab->class_name = $t['className'];
		$tab->module = $t['module'];
		$tab->position = null;
		$tab->active = true;
		$tab->id_parent = $id_parent;

		return $tab->save();
	}



	/**
	 * Get the Id of a tab by its class name
	 * @param  string $name classname to get the ID
	 * @return int       id_tab
	 */
	public static function getAdminTabIDByClassName($name)
	{
		return Db::getInstance()->getValue('SELECT id_tab FROM '._DB_PREFIX_.'tab WHERE class_name = "'.pSQL($name).'"');
	}
}