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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}{l s='Cryptocurrency payment.' mod='cryptocurrency'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='cryptocurrency'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='cryptocurrency'}</p>
{else}

<h3>{l s='Cryptocurrency payment.' mod='cryptocurrency'}</h3>
<form action="{$link->getModuleLink('cryptocurrency', 'validation', [], true)|escape:'html'}" method="post">
<p>
	<img src="{$this_path_bw}cryptocurrency.png" alt="{l s='Cryptocurrency' mod='cryptocurrency'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by Cryptocurrency cryptocurrency.' mod='cryptocurrency'}
	<br/><br />
	{l s='Here is a short summary of your order:' mod='cryptocurrency'}
</p>
<p style="margin-top:20px;">
	- {l s='The total amount of your order is' mod='cryptocurrency'}
	<span id="amount" class="price">{displayPrice price=$total}</span>
	{if $use_taxes == 1}
    	{l s='(tax incl.)' mod='cryptocurrency'}
    {/if}
</p>
<p>
	-
	{if $currencies|@count > 1}
		{l s='We allow several currencies to be sent via Cryptocurrency transaction.' mod='cryptocurrency'}
		<br /><br />
		{l s='Choose one of the following:' mod='cryptocurrency'}
		<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
			{foreach from=$currencies item=currency}
				{if $currency.name|lower == "cryptocurrency"} {* || $currency.name|lower == "doge" or bitcoin or... *}
					<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
				{/if}
			{/foreach}
		</select>
	{else}
		{l s='We allow the following currency to be sent via Cryptocurrency:' mod='cryptocurrency'}&nbsp;<b>{$currencies.0.name}</b>
		<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
	{/if}
</p>
<p>
	{l s='Cryptocurrency account information will be displayed on the next page.' mod='cryptocurrency'}
	<br /><br />
	<b>{l s='Please confirm your order by clicking "Place my order."' mod='cryptocurrency'}.</b>
</p>
<p class="cart_navigation" id="cart_navigation">
	<input type="submit" value="{l s='Place my order' mod='cryptocurrency'}" class="exclusive_large" />
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='cryptocurrency'}</a>
</p>
</form>
{/if}
