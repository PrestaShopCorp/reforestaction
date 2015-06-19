{*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="delivery_option">
	<div>
		<table id="reforestaction_table" class="resume{if isset($opc) && $opc} opc{/if}{if isset($ps_version_15) && $ps_version_15} ps15{/if}">
			<tr>
				<td{if isset($ps_version_15) && !$ps_version_15} class="delivery_option_radio"{/if}>
					{if isset($opc) && $opc}<div class="loader_ajax"></div>{/if}
					<input type="checkbox" name="reforestaction" id="reforestaction_checkbox" value="1"{if $model && $model->id} checked="checked"{/if}>
				</td>
				<td class="delivery_option_logo">
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/{$ra_logo|escape:'htmlall':'UTF-8'}" alt="">
				</td>
				<td class="reforestaction_content">	
					<p class="ra_title">
						<label for="reforestaction_checkbox">{l s='Buy Responsible' mod='reforestaction'}</label>
					</p>
					<p>
						{l s='I plant a tree with Reforest\'Action to compensate CO2\'s emissions from my purchase !' mod='reforestaction'} <a href="#popin" class="open_popin" rel="nofollow">{l s='Learn more.' mod='reforestaction'}</a>
					</p>
					<p class="checkbox newsletter"{if $model && $model->id} style="display: block;"{/if}>
						<input type="checkbox" name="reforestaction_newsletter" id="reforestaction_newsletter" value="1"{if $model && $model->newsletter} checked="checked"{/if}>
						<label for="reforestaction_newsletter">
							{l s='Receive the monthly newsletter Reforest\'Action for news of my tree' mod='reforestaction'}
						</label>
					</p>
				</td>
				<td class="delivery_option_price">
					{if $priceDisplay == 0 || $priceDisplay == 2}
						{convertPrice price=$ra_product_price} {l s='tax incl.' mod='reforestaction'}
					{else if $priceDisplay == 1}
						{convertPrice price=$ra_product_price_wt_tax} {l s='tax excl.' mod='reforestaction'}
					{/if}
				</td>
			</tr>
		</table>
	</div>
</div>
<div id="popin">
	<div class="center">
		<img class="popin-logo" alt="logo" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-popin.png" />
	</div>
	<div class="center">
		<p class="p1">
			<img class="popin-logo-2" alt="logo" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/Arbre-RA-popin.png" />
		</p>
		<p class="p2 first">{l s="By subscribing to the Purchase Manager Reforest'Action, you plant a tree on one of our reforestation projects to offset the CO2 emissions of your purchase on this website." mod='reforestaction'}</p>
		<p class="p2">{l s="A tree stores on average 150 kg of CO2 during its first 30 years of life, more than the C02 emissions from the production of most goods purchased over the Internet." mod='reforestaction'}</p>
		<p class="p2">{l s="After your purchase you will receive by email a planting certificate that your tree is planted by Reforest'Action." mod='reforestaction'}</p>
	</div>
</div>
<script type="text/javascript">
	{if isset($opc) && $opc}
		var reforestaction_link = '{$reforestaction_link|escape:'htmlall':'UTF-8'}';
		var id_reforestaction = '{$id_reforestaction|escape:'htmlall':'UTF-8'}';
	{/if}
	var ps_version_15 = '{$ps_version_15|escape:'htmlall':'UTF-8'}';
	var html_reference = '{l s='Reference:' mod='reforestaction'}';
</script>