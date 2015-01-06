{*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="reforestaction_checkbox">
	<p class="ra_title">
		{l s='Reforest\'Action' mod='reforestaction'}
	</p>
	<p class="checkbox">
		{l s='Reforest\'Action offers to offset the carbon footprint of your purchase by planting a tree!' mod='reforestaction'}
		<a href="http://www.reforestaction.com/presentation-projet-reforestaction.html" class="iframe" rel="nofollow">{l s='Click here to learn more.' mod='reforestaction'}</a>
		<br>
		<input type="checkbox" name="reforestaction" id="reforestaction_checkbox" value="1">
		<label for="reforestaction_checkbox">
			{l s='I want to offset the carbon footprint of my purchases. (Supplement from 0.99 €)' mod='reforestaction'}
		</label>
	</p>
	<p class="checkbox newsletter">
		<input type="checkbox" name="reforestaction_newsletter" id="reforestaction_newsletter" value="1">
		<label for="reforestaction_newsletter">
			{l s='Subscribe to Reforest\'Action newsletter' mod='reforestaction'}
		</label>
	</p>
</div>