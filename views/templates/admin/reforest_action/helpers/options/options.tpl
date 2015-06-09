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
	{if $show_toolbar}
		{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
		<div class="leadin">{block name="leadin"}{/block}</div>
	{/if}

	<script type="text/javascript">
		id_language = Number({$current_id_lang|escape:'htmlall':'UTF-8'});
	</script>

	{block name="defaultOptions"}
	<form action="{$current|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}"
		id="{if $table == null}configuration_form{else}{$table|escape:'htmlall':'UTF-8'}_form{/if}"
		{if isset($categoryData['name'])} name={$categoryData['name']|escape:'htmlall':'UTF-8'}{/if}
		{if isset($categoryData['id'])} id={$categoryData['id']|escape:'htmlall':'UTF-8'} {/if}
		method="post"
		enctype="multipart/form-data">
		{foreach $option_list AS $category => $categoryData}
			{if isset($categoryData['top'])}{$categoryData['top']|escape:'htmlall':'UTF-8'}{/if}
			{hook h='displayAdminOptions'}
			{if isset($name_controller)}
				{capture name=hookName assign=hookName}display{$name_controller|ucfirst}Options{/capture}
				{hook h=$hookName}
			{elseif isset($smarty.get.controller)}
				{capture name=hookName assign=hookName}display{$smarty.get.controller|ucfirst|htmlentities}Options{/capture}
				{hook h=$hookName}
			{/if}
			<fieldset {if isset($categoryData['class'])}class="{$categoryData['class']|escape:'htmlall':'UTF-8'}"{/if}>
			{* Options category title *}
			<legend>
				<img src="{$categoryData['image']|escape:'htmlall':'UTF-8'}"/>
				{if isset($categoryData['title'])}{$categoryData['title']|escape:'htmlall':'UTF-8'}{else}{l s='Options' mod='reforestaction'}{/if}
			</legend>

			{* Category description *}
			{if (isset($categoryData['description']) && $categoryData['description'])}
				<div class="optionsDescription">{$categoryData['description']|escape:'htmlall':'UTF-8'}</div>
			{/if}
			{* Category info *}
			{if (isset($categoryData['info']) && $categoryData['info'])}
				<p>{$categoryData['info']|escape:'htmlall':'UTF-8'}</p>
			{/if}

			{if !$categoryData['hide_multishop_checkbox'] && $use_multishop}
				<input type="checkbox" style="vertical-align: text-top" onclick="checkAllMultishopDefaultValue(this)" /> <b>{l s='Check/uncheck all' mod='reforestaction'}</b> {l s='(Check boxes if you want to set a custom value for this shop or group shop context)' mod='reforestaction'}
				<div class="separation"></div>
			{/if}

			{foreach $categoryData['fields'] AS $key => $field}
					{if $field['type'] == 'hidden'}
						<input type="hidden" name="{$key|escape:'htmlall':'UTF-8'}" value="{$field['value']|escape:'htmlall':'UTF-8'}" />
					{else}
						<div style="clear: both; padding-top:15px;" id="conf_id_{$key|escape:'htmlall':'UTF-8'}" {if $field['is_invisible']} class="isInvisible"{/if}>
						{if !$categoryData['hide_multishop_checkbox'] && $field['multishop_default'] && empty($field['no_multishop_checkbox'])}
							<div class="preference_default_multishop">
								<input type="checkbox" name="multishopOverrideOption[{$key|escape:'htmlall':'UTF-8'}]" value="1" {if !$field['is_disabled']}checked="checked"{/if} onclick="checkMultishopDefaultValue(this, '{$key|escape:'htmlall':'UTF-8'}')" />
							</div>
						{/if}
						{block name="label"}
							{if isset($field['title'])}
								<label class="conf_title">
								{$field['title']|escape:'htmlall':'UTF-8'}</label>
							{/if}
						{/block}
						{block name="field"}
							<div class="margin-form">
						{block name="input"}
							{if $field['type'] == 'select'}
								{if $field['list']}
									<select name="{$key|escape:'htmlall':'UTF-8'}"{if isset($field['js'])} onchange="{$field['js']|escape:'htmlall':'UTF-8'}"{/if} id="{$key|escape:'htmlall':'UTF-8'}" {if isset($field['size'])} size="{$field['size']|escape:'htmlall':'UTF-8'}"{/if} {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if}>
										{foreach $field['list'] AS $k => $option}
											<option value="{$option[$field['identifier']]|escape:'htmlall':'UTF-8'}"{if $field['value'] == $option[$field['identifier']]} selected="selected"{/if}>{$option['name']|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								{else if isset($input.empty_message)}
									{$input.empty_message|escape:'htmlall':'UTF-8'}
								{/if}
							{elseif $field['type'] == 'bool'}
								<label class="t" for="{$key|escape:'htmlall':'UTF-8'}_on"><img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='reforestaction'}" title="{l s='Yes' mod='reforestaction'}" /></label>
								<input type="radio" name="{$key|escape:'htmlall':'UTF-8'}" id="{$key|escape:'htmlall':'UTF-8'}_on" value="1" {if $field['value']} checked="checked"{/if}{if isset($field['js']['on'])} {$field['js']['on']|escape:'htmlall':'UTF-8'}{/if}/>
								<label class="t" for="{$key|escape:'htmlall':'UTF-8'}_on"> {l s='Yes' mod='reforestaction'}</label>
								<label class="t" for="{$key|escape:'htmlall':'UTF-8'}_off"><img src="../img/admin/disabled.gif" alt="{l s='No' mod='reforestaction'}" title="{l s='No' mod='reforestaction'}" style="margin-left: 10px;" /></label>
								<input type="radio" name="{$key|escape:'htmlall':'UTF-8'}" id="{$key|escape:'htmlall':'UTF-8'}_off" value="0" {if !$field['value']} checked="checked"{/if}{if isset($field['js']['off'])} {$field['js']['off']|escape:'htmlall':'UTF-8'}{/if}/>
								<label class="t" for="{$key|escape:'htmlall':'UTF-8'}_off"> {l s='No' mod='reforestaction'}</label>
							{elseif $field['type'] == 'radio'}
								{foreach $field['choices'] AS $k => $v}
									<input type="radio" name="{$key|escape:'htmlall':'UTF-8'}" id="{$key|escape:'htmlall':'UTF-8'}_{$k|escape:'htmlall':'UTF-8'}" value="{$k|escape:'htmlall':'UTF-8'}"{if $k == $field['value']} checked="checked"{/if}{if isset($field['js'][$k])} {$field['js'][$k]|escape:'htmlall':'UTF-8'}{/if}/>
									<label class="t" for="{$key|escape:'htmlall':'UTF-8'}_{$k|escape:'htmlall':'UTF-8'}"> {$v|escape:'htmlall':'UTF-8'}</label><br />
								{/foreach}
								<br />
							{elseif $field['type'] == 'checkbox'}
								{foreach $field['choices'] AS $k => $v}
									<input type="checkbox" name="{$key|escape:'htmlall':'UTF-8'}" id="{$key|escape:'htmlall':'UTF-8'}{$k|escape:'htmlall':'UTF-8'}_on" value="{$k|intval}"{if $k == $field['value']} checked="checked"{/if}{if isset($field['js'][$k])} {$field['js'][$k]|escape:'htmlall':'UTF-8'}{/if}/>
									<label class="t" for="{$key|escape:'htmlall':'UTF-8'}{$k|escape:'htmlall':'UTF-8'}_on"> {$v|escape:'htmlall':'UTF-8'}</label><br />
								{/foreach}
								<br />
							{elseif $field['type'] == 'text'}
								<input type="{$field['type']|escape:'htmlall':'UTF-8'}"{if isset($field['id'])} id="{$field['id']|escape:'htmlall':'UTF-8'}"{/if} size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key|escape:'htmlall':'UTF-8'}" value="{$field['value']|escape:'htmlall':'UTF-8'}" {if isset($field['autocomplete']) && !$field['autocomplete']}autocomplete="off"{/if} {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if}/>
								{if isset($field['suffix'])}&nbsp;{$field['suffix']|strval|escape:'htmlall':'UTF-8'}{/if}
							{elseif $field['type'] == 'password'}
								<input type="{$field['type']|escape:'htmlall':'UTF-8'}"{if isset($field['id'])} id="{$field['id']|escape:'htmlall':'UTF-8'}"{/if} size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key|escape:'htmlall':'UTF-8'}" value="" {if isset($field['autocomplete']) && !$field['autocomplete']}autocomplete="off"{/if} />
								{if isset($field['suffix'])}&nbsp;{$field['suffix']|strval|escape:'htmlall':'UTF-8'}{/if}
							{elseif $field['type'] == 'textarea'}
								<textarea name={$key|escape:'htmlall':'UTF-8'} cols="{$field['cols']|escape:'htmlall':'UTF-8'}" rows="{$field['rows']|escape:'htmlall':'UTF-8'}" {if isset($field['disabled']) && $field['disabled']}readonly="readonly"{/if}>{$field['value']|escape:'htmlall':'UTF-8'}</textarea>
							{elseif $field['type'] == 'file'}
								{if isset($field['thumb']) && $field['thumb']}
									<img src="{$field['thumb']|escape:'htmlall':'UTF-8'}" alt="{$field['title']|escape:'htmlall':'UTF-8'}" title="{$field['title']|escape:'htmlall':'UTF-8'}" /><br />
								{/if}
								<input type="file" name="{$key|escape:'htmlall':'UTF-8'}" />
	             {elseif $field['type'] == 'color'}
	              <input type="color"
	                size="{$field['size']|escape:'htmlall':'UTF-8'}"
	                data-hex="true"
	                {if isset($input.class)}class="{$field['class']|escape:'htmlall':'UTF-8'}"
	                {else}class="color mColorPickerInput"{/if}
	                name="{$field['name']|escape:'htmlall':'UTF-8'}"
	                class="{if isset($field['class'])}{$field['class']|escape:'htmlall':'UTF-8'}{/if}"
	                value="{$field['value']|escape:'htmlall':'UTF-8'}" />
							{elseif $field['type'] == 'price'}
								{$currency_left_sign|escape:'htmlall':'UTF-8'}<input type="text" size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key|escape:'htmlall':'UTF-8'}" value="{$field['value']|escape:'htmlall':'UTF-8'}" />{$currency_right_sign|escape:'htmlall':'UTF-8'} {l s='(tax excl.)' mod='reforestaction'}
							{elseif $field['type'] == 'textLang' || $field['type'] == 'textareaLang' || $field['type'] == 'selectLang'}
								{if $field['type'] == 'textLang'}
									{foreach $field['languages'] AS $id_lang => $value}
										<div id="{$key|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}" style="margin-bottom:8px; display: {if $id_lang == $current_id_lang}block{else}none{/if}; float: left; vertical-align: top;">
											<input type="text" size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}" value="{$value|escape:'htmlall':'UTF-8'}" />
										</div>
									{/foreach}
								{elseif $field['type'] == 'textareaLang'}
									{foreach $field['languages'] AS $id_lang => $value}
										<div id="{$key|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}" style="display: {if $id_lang == $current_id_lang}block{else}none{/if}; float: left;">
											<textarea rows="{$field['rows']|escape:'htmlall':'UTF-8'}" cols="{$field['cols']|intval}"  name="{$key|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}">{$value|replace:'\r\n':"\n"|escape:'htmlall':'UTF-8'}</textarea>
										</div>
									{/foreach}
								{elseif $field['type'] == 'selectLang'}
									{foreach $languages as $language}
									<div id="{$key|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" style="margin-bottom:8px; display: {if $language.id_lang == $current_id_lang}block{else}none{/if}; float: left; vertical-align: top;">
										<select name="{$key|escape:'htmlall':'UTF-8'}_{$language.iso_code|upper|escape:'htmlall':'UTF-8'}">
											{foreach $field['list'] AS $k => $v}
												<option value="{if isset($v.cast)}{$v.cast[$v[$field.identifier]]|escape:'htmlall':'UTF-8'}{else}{$v[$field.identifier]|escape:'htmlall':'UTF-8'}{/if}"
													{if $field['value'][$language.id_lang] == $v['name']} selected="selected"{/if}>
													{$v['name']|escape:'htmlall':'UTF-8'}
												</option>
											{/foreach}
										</select>
									</div>
									{/foreach}
								{/if}
								{if count($languages) > 1}
									<div class="displayed_flag">
										<img src="../img/l/{$current_id_lang|escape:'htmlall':'UTF-8'}.jpg"
											class="pointer"
											id="language_current_{$key|escape:'htmlall':'UTF-8'}"
											onclick="toggleLanguageFlags(this);" />
									</div>
									<div id="languages_{$key|escape:'htmlall':'UTF-8'}" class="language_flags">
										{l s='Choose language:' mod='reforestaction'}<br /><br />
										{foreach $languages as $language}
												<img src="../img/l/{$language.id_lang|escape:'htmlall':'UTF-8'}.jpg"
													class="pointer"
													alt="{$language.name|escape:'htmlall':'UTF-8'}"
													title="{$language.name|escape:'htmlall':'UTF-8'}"
													onclick="changeLanguage('{$key|escape:'htmlall':'UTF-8'}', '{if isset($custom_key)}{$custom_key|escape:'htmlall':'UTF-8'}{else}{$key|escape:'htmlall':'UTF-8'}{/if}', {$language.id_lang|escape:'htmlall':'UTF-8'}, '{$language.iso_code|escape:'htmlall':'UTF-8'}');" />
										{/foreach}
									</div>
								{/if}
								<br style="clear:both">
							{/if}

							{if isset($field['required']) && $field['required'] && $field['type'] != 'radio'}
								<sup>*</sup>
							{/if}
							{if isset($field['hint'])}<span class="hint" name="help_box">{$field['hint']|escape:'htmlall':'UTF-8'}<span class="hint-pointer">&nbsp;</span></span>{/if}
						{/block}{* end block input *}
						{if isset($field['desc'])}<p class="preference_description">{$field['desc']|escape:'htmlall':'UTF-8'}</p>{/if}
						{if $field['is_invisible']}<p class="warn">{l s='You can\'t change the value of this configuration field in the context of this shop.' mod='reforestaction'}</p>{/if}
						</div>
						</div>
						<div class="clear"></div>
					{/block}{* end block field *}
				{/if}
			{/foreach}
			{if isset($categoryData['submit'])}
				<div class="margin-form">
					<input type="submit"
							value="{if isset($categoryData['submit']['title'])}{$categoryData['submit']['title']|escape:'htmlall':'UTF-8'}{else}{l s='Save' mod='reforestaction'}{/if}"
							name="{if isset($categoryData['submit']['name'])}{$categoryData['submit']['name']|escape:'htmlall':'UTF-8'}{else}submitOptions{$table|escape:'htmlall':'UTF-8'}{/if}"
							class="{if isset($categoryData['submit']['class'])}{$categoryData['submit']['class']|escape:'htmlall':'UTF-8'}{else}button{/if}"
							id="{$table|escape:'htmlall':'UTF-8'}_form_submit_btn"
					/>
				</div>
			{/if}
			{if isset($categoryData['required_fields']) && $categoryData['required_fields']}
				<div class="small"><sup>*</sup> {l s='Required field' mod='reforestaction'}</div>
			{/if}
			{if isset($categoryData['bottom'])}{$categoryData['bottom']|escape:'htmlall':'UTF-8'}{/if}
			</fieldset><br />
		{/foreach}
	</form>
	{/block}
	{block name="after"}{/block}
{else}

	<div class="leadin">{block name="leadin"}{/block}</div>

	<script type="text/javascript">
		id_language = Number({$current_id_lang|escape:'htmlall':'UTF-8'});
		{if isset($tabs) && $tabs|count}
			var helper_tabs= {$tabs|json_encode};
			var unique_field_id = '{$table|escape:'htmlall':'UTF-8'}_';
		{/if}
	</script>
	{block name="defaultOptions"}
	{if isset($table_bk) && $table_bk == $table}{capture name='table_count'}{counter name='table_count'}{/capture}{/if}
	{assign var='table_bk' value=$table scope='parent'}
	<form action="{$current|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}" id="{if $table == null}configuration_form{else}{$table|escape:'htmlall':'UTF-8'}_form{/if}{if isset($smarty.capture.table_count) && $smarty.capture.table_count}_{$smarty.capture.table_count|intval}{/if}" method="post" enctype="multipart/form-data" class="form-horizontal">
		{hook h='displayAdminOptions'}
		{if isset($name_controller)}
			{capture name=hookName assign=hookName}display{$name_controller|ucfirst}Options{/capture}
			{hook h=$hookName}
		{elseif isset($smarty.get.controller)}
			{capture name=hookName assign=hookName}display{$smarty.get.controller|ucfirst|htmlentities}Options{/capture}
			{hook h=$hookName}
		{/if}
		{foreach $option_list AS $category => $categoryData}
			{if isset($categoryData['top'])}{$categoryData['top']|escape:'htmlall':'UTF-8'}{/if}
			<div class="panel {if isset($categoryData['class'])}{$categoryData['class']|escape:'htmlall':'UTF-8'}{/if}" id="{$table|escape:'htmlall':'UTF-8'}_fieldset_{$category|escape:'htmlall':'UTF-8'}">
				{* Options category title *}
				<div class="panel-heading">
					<i class="{if isset($categoryData['icon'])}{$categoryData['icon']|escape:'htmlall':'UTF-8'}{else}icon-cogs{/if}"></i>
					{if isset($categoryData['title'])}{$categoryData['title']|escape:'htmlall':'UTF-8'}{else}{l s='Options' mod='reforestaction'}{/if}
				</div>

				{* Category description *}

				{if (isset($categoryData['description']) && $categoryData['description'])}
					<div class="alert alert-info">{$categoryData['description']|escape:'htmlall':'UTF-8'}</div>
				{/if}
				{* Category info *}
				{if (isset($categoryData['info']) && $categoryData['info'])}
					<div>{$categoryData['info']|escape:'htmlall':'UTF-8'}</div>
				{/if}

				{if !$categoryData['hide_multishop_checkbox'] && $use_multishop}
				<div class="well clearfix">
					<label class="control-label col-lg-3">
						<i class="icon-sitemap"></i> {l s='Multistore' mod='reforestaction'}
					</label>
					<div class="col-lg-9">
						<span class="switch prestashop-switch fixed-width-lg">
							{strip}
							<input type="radio" name="{$table|escape:'htmlall':'UTF-8'}_multishop_{$category|escape:'htmlall':'UTF-8'}" id="{$table|escape:'htmlall':'UTF-8'}_multishop_{$category|escape:'htmlall':'UTF-8'}_on" value="1" onclick="toggleAllMultishopDefaultValue($('#{$table|escape:'htmlall':'UTF-8'}_fieldset_{$category|escape:'htmlall':'UTF-8'}'), true)"/>
							<label for="{$table|escape:'htmlall':'UTF-8'}_multishop_{$category|escape:'htmlall':'UTF-8'}_on">
								{l s='Yes' mod='reforestaction'}
							</label>
							<input type="radio" name="{$table|escape:'htmlall':'UTF-8'}_multishop_{$category|escape:'htmlall':'UTF-8'}" id="{$table|escape:'htmlall':'UTF-8'}_multishop_{$category|escape:'htmlall':'UTF-8'}_off" value="0" checked="checked" onclick="toggleAllMultishopDefaultValue($('#{$table|escape:'htmlall':'UTF-8'}_fieldset_{$category|escape:'htmlall':'UTF-8'}'), false)"/>
							<label for="{$table|escape:'htmlall':'UTF-8'}_multishop_{$category|escape:'htmlall':'UTF-8'}_off">
								{l s='No' mod='reforestaction'}
							</label>
							{/strip}
							<a class="slide-button btn"></a>
						</span>
						<div class="row">
							<div class="col-lg-12">
								<p class="help-block">
									<strong>{l s='Check / Uncheck all' mod='reforestaction'}</strong>
									{l s='(If you are editing this page for several shops, some fields may be disabled. If you need to edit them, you will need to check the box for each field)' mod='reforestaction'}
								</p>
							</div>
						</div>
					</div>
				</div>
				{/if}

				<div class="form-wrapper">
				{foreach $categoryData['fields'] AS $key => $field}
						{if $field['type'] == 'hidden'}
							<input type="hidden" name="{$key|escape:'htmlall':'UTF-8'}" value="{$field['value']|escape:'htmlall':'UTF-8'}" />
						{else}
							<div class="form-group{if isset($field.form_group_class)} {$field.form_group_class|escape:'htmlall':'UTF-8'}{/if}"{if isset($tabs) && isset($field.tab)} data-tab-id="{$field.tab|escape:'htmlall':'UTF-8'}"{/if}>
								<div id="conf_id_{$key|escape:'htmlall':'UTF-8'}"{if $field['is_invisible']} class="isInvisible"{/if}>								
									{block name="label"}
										{if isset($field['title']) && isset($field['hint'])}
											<label class="control-label col-lg-3{if isset($field['required']) && $field['required'] && $field['type'] != 'radio'} required{/if}">
												{if !$categoryData['hide_multishop_checkbox'] && $field['multishop_default'] && empty($field['no_multishop_checkbox'])}
												<input type="checkbox" {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} name="multishopOverrideOption[{$key|escape:'htmlall':'UTF-8'}]" value="1"{if !$field['is_disabled']} checked="checked"{/if} onclick="toggleMultishopDefaultValue(this, '{$key|escape:'htmlall':'UTF-8'}')"/>
												{/if}
												<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="
													{if is_array($field['hint'])}
														{foreach $field['hint'] as $hint}
															{if is_array($hint)}
																{$hint.text|escape:'htmlall':'UTF-8'}
															{else}
																{$hint|escape:'htmlall':'UTF-8'}
															{/if}
														{/foreach}
													{else}
														{$field['hint']|escape:'htmlall':'UTF-8'}
													{/if}
												" data-html="true">
													{$field['title']|escape:'htmlall':'UTF-8'}
												</span>
											</label>
										{elseif isset($field['title'])}
											<label class="control-label col-lg-3">
												{if !$categoryData['hide_multishop_checkbox'] && $field['multishop_default'] && empty($field['no_multishop_checkbox'])}
												<input type="checkbox" {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} name="multishopOverrideOption[{$key|escape:'htmlall':'UTF-8'}]" value="1"{if !$field['is_disabled']} checked="checked"{/if} onclick="checkMultishopDefaultValue(this, '{$key|escape:'htmlall':'UTF-8'}')" />
												{/if}
												{$field['title']|escape:'htmlall':'UTF-8'}
											</label>
										{/if}
									{/block}
									{block name="field"}

									{block name="input"}
										{if $field['type'] == 'select'}
											<div class="col-lg-9">
												{if $field['list']}
													<select {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} class="form-control fixed-width-xxl {if isset($field['class'])}{$field['class']|escape:'htmlall':'UTF-8'}{/if}" name="{$key|escape:'htmlall':'UTF-8'}"{if isset($field['js'])} onchange="{$field['js']|escape:'htmlall':'UTF-8'}"{/if} id="{$key|escape:'htmlall':'UTF-8'}" {if isset($field['size'])} size="{$field['size']|escape:'htmlall':'UTF-8'}"{/if}>
														{foreach $field['list'] AS $k => $option}
															<option value="{$option[$field['identifier']]|escape:'htmlall':'UTF-8'}"{if $field['value'] == $option[$field['identifier']]} selected="selected"{/if}>{$option['name']|escape:'htmlall':'UTF-8'}</option>
														{/foreach}
													</select>
												{else if isset($input.empty_message)}
													{$input.empty_message|escape:'htmlall':'UTF-8'}
												{/if}
											</div>
										{elseif $field['type'] == 'bool'}
											<div class="col-lg-9">
												<span class="switch prestashop-switch fixed-width-lg">
													{strip}
													<input {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="radio" name="{$key|escape:'htmlall':'UTF-8'}" id="{$key|escape:'htmlall':'UTF-8'}_on" value="1" {if $field['value']} checked="checked"{/if}{if isset($field['js']['on'])} {$field['js']['on']|escape:'htmlall':'UTF-8'}{/if}/>
													<label for="{$key|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
														{l s='Yes' mod='reforestaction'}
													</label>
													<input {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="radio" name="{$key|escape:'htmlall':'UTF-8'}" id="{$key|escape:'htmlall':'UTF-8'}_off" value="0" {if !$field['value']} checked="checked"{/if}{if isset($field['js']['off'])} {$field['js']['off']|escape:'htmlall':'UTF-8'}{/if}/>
													<label for="{$key|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
														{l s='No' mod='reforestaction'}
													</label>
													{/strip}
													<a class="slide-button btn"></a>
												</span>
											</div>
										{elseif $field['type'] == 'radio'}
											<div class="col-lg-9">
												{foreach $field['choices'] AS $k => $v}
													<p class="radio">
														{strip}
														<label for="{$key|escape:'htmlall':'UTF-8'}_{$k|escape:'htmlall':'UTF-8'}">
															<input {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="radio" name="{$key|escape:'htmlall':'UTF-8'}" id="{$key|escape:'htmlall':'UTF-8'}_{$k|escape:'htmlall':'UTF-8'}" value="{$k|escape:'htmlall':'UTF-8'}"{if $k == $field['value']} checked="checked"{/if}{if isset($field['js'][$k])} {$field['js'][$k]|escape:'htmlall':'UTF-8'}{/if}/>
														 	{$v|escape:'htmlall':'UTF-8'}
														</label>
														{/strip}
													</p>
												{/foreach}
											</div>
										{elseif $field['type'] == 'checkbox'}
											<div class="col-lg-9">
												{foreach $field['choices'] AS $k => $v}
													<p class="checkbox">
														{strip}
														<label class="col-lg-3" for="{$key|escape:'htmlall':'UTF-8'}{$k|escape:'htmlall':'UTF-8'}_on">
															<input {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="checkbox" name="{$key|escape:'htmlall':'UTF-8'}" id="{$key|escape:'htmlall':'UTF-8'}{$k|escape:'htmlall':'UTF-8'}_on" value="{$k|intval}"{if $k == $field['value']} checked="checked"{/if}{if isset($field['js'][$k])} {$field['js'][$k]|escape:'htmlall':'UTF-8'}{/if}/>
														 	{$v|escape:'htmlall':'UTF-8'}
														</label>
														{/strip}
													</p>
												{/foreach}
											</div>
										{elseif $field['type'] == 'text'}
											<div class="col-lg-9">{if isset($field['suffix'])}<div class="input-group">{/if}
												<input {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} class="form-control {if isset($field['class'])}{$field['class']|escape:'htmlall':'UTF-8'}{/if}" type="{$field['type']|escape:'htmlall':'UTF-8'}"{if isset($field['id'])} id="{$field['id']|escape:'htmlall':'UTF-8'}"{/if} size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key|escape:'htmlall':'UTF-8'}" value="{$field['value']|escape:'html':'UTF-8'}" {if isset($field['autocomplete']) && !$field['autocomplete']}autocomplete="off"{/if}/>
												{if isset($field['suffix'])}
												<span class="input-group-addon">
													{$field['suffix']|strval|escape:'htmlall':'UTF-8'}
												</span>
												{/if}
												{if isset($field['suffix'])}</div>{/if}
											</div>
										{elseif $field['type'] == 'password'}
											<div class="col-lg-9">{if isset($field['suffix'])}<div class="input-group">{/if}
												<input {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="{$field['type']|escape:'htmlall':'UTF-8'}"{if isset($field['id'])} id="{$field['id']|escape:'htmlall':'UTF-8'}"{/if} size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key|escape:'htmlall':'UTF-8'}" value=""{if isset($field['autocomplete']) && !$field['autocomplete']} autocomplete="off"{/if} />
												{if isset($field['suffix'])}
												<span class="input-group-addon">
													{$field['suffix']|strval|escape:'htmlall':'UTF-8'}
												</span>
												{/if}
												{if isset($field['suffix'])}</div>{/if}
											</div>
										{elseif $field['type'] == 'textarea'}
											<div class="col-lg-9">
												<textarea {if isset($field['disabled']) && $field['disabled']}readonly="readonly"{/if} class="textarea-autosize" name={$key|escape:'htmlall':'UTF-8'} cols="{$field['cols']|escape:'htmlall':'UTF-8'}" rows="{$field['rows']|escape:'htmlall':'UTF-8'}">{$field['value']|escape:'html':'UTF-8'}</textarea>
											</div>
										{elseif $field['type'] == 'file'}
											<div class="col-lg-9">{$field['file']|escape:'htmlall':'UTF-8'}</div>
										{elseif $field['type'] == 'color'}
											<div class="col-lg-2">
												<div class="input-group">
													<input {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="color" size="{$field['size']|escape:'htmlall':'UTF-8'}" data-hex="true" {if isset($input.class)}class="{$field['class']|escape:'htmlall':'UTF-8'}" {else}class="color mColorPickerInput"{/if} name="{$field['name']|escape:'htmlall':'UTF-8'}" class="{if isset($field['class'])}{$field['class']|escape:'htmlall':'UTF-8'}{/if}" value="{$field['value']|escape:'html':'UTF-8'}" />
												</div>
								            </div>
										{elseif $field['type'] == 'price'}
											<div class="col-lg-9">
												<div class="input-group fixed-width-lg">
													<span class="input-group-addon">{$currency_left_sign|escape:'htmlall':'UTF-8'}{$currency_right_sign|escape:'htmlall':'UTF-8'} {l s='(tax excl.)' mod='reforestaction'}</span>
													<input {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="text" size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key|escape:'htmlall':'UTF-8'}" value="{$field['value']|escape:'html':'UTF-8'}" />
												</div>
											</div>
										{elseif $field['type'] == 'textLang' || $field['type'] == 'textareaLang' || $field['type'] == 'selectLang'}
											{if $field['type'] == 'textLang'}
												<div class="col-lg-9">
													<div class="row">
													{foreach $field['languages'] AS $id_lang => $value}
														{if $field['languages']|count > 1}
														<div class="translatable-field lang-{$id_lang|escape:'htmlall':'UTF-8'}" {if $id_lang != $current_id_lang}style="display:none;"{/if}>
															<div class="col-lg-9">
														{else}
														<div class="col-lg-12">
														{/if}
																<input {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="text"
																	name="{$key|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}"
																	value="{$value|escape:'html':'UTF-8'}"
																	{if isset($input.class)}class="{$input.class|escape:'htmlall':'UTF-8'}"{/if}
																/>
														{if $field['languages']|count > 1}
															</div>
															<div class="col-lg-2">
																<button {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
																	{foreach $languages as $language}
																		{if $language.id_lang == $id_lang}{$language.iso_code|escape:'htmlall':'UTF-8'}{/if}
																	{/foreach}
																	<span class="caret"></span>
																</button>
																<ul class="dropdown-menu">
																	{foreach $languages as $language}
																	<li>
																		<a href="javascript:hideOtherLanguage({$language.id_lang|escape:'htmlall':'UTF-8'});">{$language.name|escape:'htmlall':'UTF-8'}</a>
																	</li>
																	{/foreach}
																</ul>
															</div>
														</div>
														{else}
														</div>
														{/if}
													{/foreach}
													</div>
												</div>
											{elseif $field['type'] == 'textareaLang'}
												<div class="col-lg-9">
													{foreach $field['languages'] AS $id_lang => $value}
														<div class="row translatable-field lang-{$id_lang|escape:'htmlall':'UTF-8'}" {if $id_lang != $current_id_lang}style="display:none;"{/if}>
															<div id="{$key|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}" class="col-lg-9" >
																<textarea {if isset($field['disabled']) && $field['disabled']}readonly="readonly"{/if} class="textarea-autosize" name="{$key|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}">{$value|replace:'\r\n':"\n"|escape:'htmlall':'UTF-8'}</textarea>
															</div>
															<div class="col-lg-2">
																<button {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
																	{foreach $languages as $language}
																		{if $language.id_lang == $id_lang}{$language.iso_code|escape:'htmlall':'UTF-8'}{/if}
																	{/foreach}
																	<span class="caret"></span>
																</button>
																<ul class="dropdown-menu">
																	{foreach $languages as $language}
																	<li>
																		<a href="javascript:hideOtherLanguage({$language.id_lang|escape:'htmlall':'UTF-8'});">{$language.name|escape:'htmlall':'UTF-8'}</a>
																	</li>
																	{/foreach}
																</ul>
															</div>

														</div>
													{/foreach}
													<script type="text/javascript">
														$(document).ready(function() {
															$(".textarea-autosize").autosize();
														});
													</script>
												</div>
											{elseif $field['type'] == 'selectLang'}
												{foreach $languages as $language}
													<div id="{$key|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" style="display: {if $language.id_lang == $current_id_lang}block{else}none{/if};" class="col-lg-9">
														<select {if isset($field['disabled']) && $field['disabled']}disabled="disabled"{/if} name="{$key|escape:'htmlall':'UTF-8'}_{$language.iso_code|upper|escape:'htmlall':'UTF-8'}">
															{foreach $field['list'] AS $k => $v}
																<option value="{if isset($v.cast)}{$v.cast[$v[$field.identifier]]|escape:'htmlall':'UTF-8'}{else}{$v[$field.identifier]|escape:'htmlall':'UTF-8'}{/if}"
																	{if $field['value'][$language.id_lang] == $v['name']} selected="selected"{/if}>
																	{$v['name']|escape:'htmlall':'UTF-8'}
																</option>
															{/foreach}
														</select>
													</div>
												{/foreach}
											{/if}
										{/if}
										{if isset($field['desc']) && !empty($field['desc'])}
										<div class="col-lg-9 col-lg-offset-3">
											<div class="help-block">
												{if is_array($field['desc'])}
													{foreach $field['desc'] as $p}
														{if is_array($p)}
															<span id="{$p.id|escape:'htmlall':'UTF-8'}">{$p.text|escape:'htmlall':'UTF-8'}</span><br />
														{else}
															{$p|escape:'htmlall':'UTF-8'}<br />
														{/if}
													{/foreach}
												{else}
													{$field['desc']|escape:'htmlall':'UTF-8'}
												{/if}
											</div>
										</div>
										{/if}
									{/block}{* end block input *}
									{if $field['is_invisible']}
									<div class="col-lg-9 col-lg-offset-3">
										<p class="alert alert-warning row-margin-top">
											{l s='You can\'t change the value of this configuration field in the context of this shop.' mod='reforestaction'}
										</p>
									</div>
									{/if}
									{/block}{* end block field *}
								</div>
							</div>
					{/if}
				{/foreach}
				</div><!-- /.form-wrapper -->

				{if isset($categoryData['bottom'])}{$categoryData['bottom']|escape:'htmlall':'UTF-8'}{/if}
				{block name="footer"}
					{if isset($categoryData['submit']) || isset($categoryData['buttons'])}
						<div class="panel-footer">
							{if isset($categoryData['submit']) && !empty($categoryData['submit'])}
							<button type="{if isset($categoryData['submit']['type'])}{$categoryData['submit']['type']|escape:'htmlall':'UTF-8'}{else}submit{/if}" {if isset($categoryData['submit']['id'])}id="{$categoryData['submit']['id']|escape:'htmlall':'UTF-8'}"{/if} class="btn btn-default pull-right" name="{if isset($categoryData['submit']['name'])}{$categoryData['submit']['name']|escape:'htmlall':'UTF-8'}{else}submitOptions{$table|escape:'htmlall':'UTF-8'}{/if}"><i class="process-icon-{if isset($categoryData['submit']['imgclass'])}{$categoryData['submit']['imgclass']|escape:'htmlall':'UTF-8'}{else}save{/if}"></i> {$categoryData['submit']['title']|escape:'htmlall':'UTF-8'}</button>
							{/if}
							{if isset($categoryData['buttons'])}
							{foreach from=$categoryData['buttons'] item=btn key=k}
							{if isset($btn.href) && trim($btn.href) != ''}
								<a href="{$btn.href|escape:'html':'UTF-8'}" {if isset($btn['id'])}id="{$btn['id']|escape:'htmlall':'UTF-8'}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']|escape:'htmlall':'UTF-8'}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js|escape:'htmlall':'UTF-8'}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']|escape:'htmlall':'UTF-8'}" ></i> {/if}{$btn.title|escape:'htmlall':'UTF-8'}</a>
							{else}
								<button type="{if isset($btn['type'])}{$btn['type']|escape:'htmlall':'UTF-8'}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']|escape:'htmlall':'UTF-8'}"{/if} class="{if isset($btn['class'])}{$btn['class']|escape:'htmlall':'UTF-8'}{else}btn btn-default{/if}" name="{if isset($btn['name'])}{$btn['name']|escape:'htmlall':'UTF-8'}{else}submitOptions{$table|escape:'htmlall':'UTF-8'}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js|escape:'htmlall':'UTF-8'}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']|escape:'htmlall':'UTF-8'}" ></i> {/if}{$btn.title|escape:'htmlall':'UTF-8'}</button>
							{/if}
							{/foreach}
							{/if}
						</div>
					{/if}
				{/block}
			</div>
		{/foreach}
	</form>
	{/block}
	{block name="after"}{/block}

{/if}