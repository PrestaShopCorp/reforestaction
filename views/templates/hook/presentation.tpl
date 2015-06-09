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
<fieldset class="reforestaction_help reforestaction_intro panel">
	<h2 class="panel-heading">{l s='Reforest\'Action' mod='reforestaction'}</h2>
	<div class="col-lg-2 text-center">
		<img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-full.png" alt="Reforest'Action logo" width="200px" />
	</div>
	<div class="col-lg-10">
		<dl>
			<dt>{l s='Attract more clients on social networks : ' mod='reforestaction'}</dt>
			<dd>{l s='Your customers can share that they just planted a tree thanks to your website.' mod='reforestaction'}</dd>
			<dt>{l s='Improve your website overall image : ' mod='reforestaction'}</dt>
			<dd>{l s='More than two thirds of consumers claim that to be "proud to buy a product that is eco-friendly or good for the planet"' mod='reforestaction'}</dd>
			<dt>{l s='No costs, installation in a just a few clicks' mod='reforestaction'}</dt>
		</dl>
	</div>
</fieldset>

{if isset($display_video) && $display_video}
	<fieldset class="panel reforestaction_help">
		<h2 class="panel-heading">{l s='Presentation' mod='reforestaction'}</h2>
		<div class="col-lg-12 center">
			<div class="col-lg-3"></div>
			<a href="http://www.videops.reforestaction.com" class="col-lg-6"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/video.png" alt="{l s='Youtube' mod='reforestaction'}" class="col-lg-12"/></a>
			<div class="col-lg-3"></div>
		</div>
	</fieldset>
{/if}

<fieldset class="reforestaction_help panel">
	<h2 class="panel-heading">{l s='Installation : 5 minutes only' mod='reforestaction'}</h2>
	<ul>
		<li>{l s='1- Complete the form below' mod='reforestaction'}</li>
		<li>{l s='2- Complete the money order on Slimpay' mod='reforestaction'}</li>
		<li>{l s='3-  Put the Responsible Purchase logo you will receive by email on your homepage (optional)' mod='reforestaction'}</li>
	</ul>
	<div class="alert alert-warning warn">
		{l s='Caution : your firstname, name and address will be automatically used for the money order on Slimpay. Please make sure they are the same as communicated to your bank.' mod='reforestaction'}
	</div>
	<p>
		{l s='The Reforest’Action module will be automatically activated within 12 hours after your account creation. After each transaction, you will receive from your Customers 0,99 € for the tree plantation. Each quarter, we will automatically execute a standing order from your bank account for the corresponding amount, without creating extra work on your side. Of course, you will be informed beforehand about the amount collected. So you can focus on your core business, we take care of the trees!' mod='reforestaction'}
	</p>
</fieldset>