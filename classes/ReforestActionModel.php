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

class ReforestActionModel extends ObjectModel
{

	public $id_cart, $id_order, $total, $qty, $price_exc, $price_inc, $newsletter, $sent, $id_order_reforestaction, $date_sent, $date_add, $date_upd;

	public static $definition = array(
		'table'     => 'reforestaction',
		'primary'   => 'id_reforestaction',
		'multilang' => false,
		'fields'    => array(
			'id_cart'                 => array('type' => self::TYPE_INT,   'validate' => 'isInt'),
			'id_order'                => array('type' => self::TYPE_INT,   'validate' => 'isInt'),
			'total'                   => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
			'qty'                     => array('type' => self::TYPE_INT,   'validate' => 'isInt'),
			'price_exc'               => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
			'price_inc'               => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
			'newsletter'              => array('type' => self::TYPE_BOOL,  'validate' => 'isBool'),
			'sent'                    => array('type' => self::TYPE_BOOL,  'validate' => 'isBool'),
			'id_order_reforestaction' => array('type' => self::TYPE_INT,   'validate' => 'isInt'),
			'date_sent'               => array('type' => self::TYPE_DATE,  'validate' => 'isDateFormat'),
			'date_add'                => array('type' => self::TYPE_DATE,  'validate' => 'isDateFormat'),
			'date_upd'                => array('type' => self::TYPE_DATE,  'validate' => 'isDateFormat'),
		),
	);

	/**
	 * Install SQL Table
	 */
	public static function install()
	{
		$sql = array();
		// Create Category Table in Database
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::$definition['table'].'` (
					`'.self::$definition['primary'].'` int(11) NOT NULL AUTO_INCREMENT,
					`id_cart` int(11) unsigned NOT NULL,
					`id_order` int(11) unsigned NOT NULL,
					`total` float unsigned NOT NULL,
					`qty` int(11) unsigned NOT NULL,
					`price_exc` float unsigned NOT NULL,
					`price_inc` float unsigned NOT NULL,
					`newsletter` tinyint(1) NOT NULL,
					`sent` tinyint(1) NOT NULL,
					`id_order_reforestaction` int(11) unsigned NOT NULL,
					`date_sent` datetime NOT NULL,
					`date_add` datetime NOT NULL,
					`date_upd` datetime NOT NULL,
					UNIQUE(`'.self::$definition['primary'].'`),
					PRIMARY KEY  ('.self::$definition['primary'].')
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		foreach ($sql as $q)
			Db::getInstance()->Execute($q);
	}

	/**
	 * Uninstall SQL table
	 */
	public static function uninstall()
	{
		$sql = array();
		// Create Category Table in Database
		$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::$definition['table'].'`';

		foreach ($sql as $q)
			Db::getInstance()->Execute($q);
	}

	/**
	 * Create instance by Id Cart
	 * @param  int            $id_cart Cart ID
	 * @return ReforestAction          Instance
	 */
	public static function getInstanceByIdCart($id_cart)
	{
		$query = new DbQuery();
		$query->select(self::$definition['primary']);
		$query->from(self::$definition['table']);
		$query->where('id_cart = '.(int)$id_cart);

		$id_reforestaction = DB::getInstance()->getValue($query);

		$instance = new ReforestActionModel($id_reforestaction);
		$instance->id_cart = (int)$id_cart;

		return $instance;
	}

	/**
	 * Get reforestaction
	 * @param  string $date_from Date to
	 * @param  string $date_to   Date from
	 * @return array             Results
	 */
	public static function getOrdersIdByDate($date_from, $date_to)
	{
		$query = new DbQuery();

		$query->select(self::$definition['primary']);
		$query->from(self::$definition['table']);
		$query->where("DATE_ADD(date_upd, INTERVAL -1 DAY) <= '".pSQL($date_to)."' AND date_upd >= '".pSQL($date_from)."'");

		$result = Db::getInstance()->executeS($query);

		$reforestactions = array();
		foreach ($result as $reforestaction)
			$reforestactions[] = (int)$reforestaction[self::$definition['primary']];

		return $reforestactions;
	}

}