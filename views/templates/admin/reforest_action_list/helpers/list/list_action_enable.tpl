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
{if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
	<a href="{$url_enable|escape:'htmlall':'UTF-8'}" {if isset($confirm)}onclick="return confirm('{$confirm|escape:'htmlall':'UTF-8'}');"{/if} title="{if $enabled}{l s='Enabled' mod='reforestaction'}{else}{l s='Disabled' mod='reforestaction'}{/if}">
		<img src="../img/admin/{if $enabled}enabled.gif{else}disabled.gif{/if}" alt="{if $enabled}{l s='Enabled' mod='reforestaction'}{else}{l s='Disabled' mod='reforestaction'}{/if}" />
	</a>
{else}
	<a class="list-action-enable{if isset($ajax) && $ajax} ajax_table_link{/if}{if $enabled} action-enabled{else} action-disabled{/if}" {if isset($confirm)} onclick="return confirm('{$confirm}');"{/if} title="{if $enabled}{l s='Enabled' mod='reforestaction'}{else}{l s='Disabled' mod='reforestaction'}{/if}">
		<i class="icon-check{if !$enabled} hidden{/if}"></i>
		<i class="icon-remove{if $enabled} hidden{/if}"></i>
	</a>
{/if}
