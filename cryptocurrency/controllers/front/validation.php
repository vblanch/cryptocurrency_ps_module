<?php
/*
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
*/

/**
 * @since 1.5.0
 */
class CryptocurrencyValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'cryptocurrency')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		
		//find out the correct wallet address
		/*
		$sub_addresses = explode(',', Configuration::get('CRYPTO_CURRENCY_ADDRESS'));
		$address_list=array();
		if(is_array($sub_addresses)){
			foreach($sub_addresses as $sub){
				$items = explode('|', $sub);
				$address_list[$items[0]] = $items[1];
			}
		}
		$currency_address = $address_list[$currency->id];
		if(!$currency_address || $currency_address =='')
			$currency_address = $this->l('Not available');
			
		//print_r($currency_address); 
		*/
		/*
		$logger = new FileLogger(0); //0 == nivell de debug. Sense això logDebug() no funciona.
		$logger->setFilename(_PS_ROOT_DIR_.'/log/debug.log');
		$logger->logDebug("currency address id:".$currency->id);
		$logger->logDebug("currency address list:".$address_list); 
		$logger->logDebug("currency address:".$currency_address);
		$logger->logDebug("currency address var export:".var_export($currency_address, true));
		*/
		//print_r($currency_address); 
		
		$mailVars = array(
			'{wallet_owner}' => Configuration::get('CRYPTO_CURRENCY_OWNER'),
			'{wallet_details}' => nl2br(Configuration::get('CRYPTO_CURRENCY_DETAILS')),
			'{wallet_address}' => nl2br(Configuration::get('CRYPTO_CURRENCY_ADDRESS')[$currency->id])
			//'{wallet_address}' => nl2br($currency_address)
			//'{wallet_address}' => nl2br(Configuration::get('CRYPTO_CURRENCY_ADDRESS'))
		);

		//$this->module->validateOrder($cart->id, Configuration::get('PS_OS_BANKWIRE'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
		$this->module->validateOrder($cart->id, Configuration::get('PS_OS_CRYPTOCURRENCY'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
		Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
	}
}
