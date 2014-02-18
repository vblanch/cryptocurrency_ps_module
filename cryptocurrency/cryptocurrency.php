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

if (!defined('_PS_VERSION_'))
	exit;
	
/**
 * @deprecated : these defines are going to be deleted on 1.6 version of Prestashop
 * USE : Configuration::get() method in order to getting the id of order state
 */
define('_PS_OS_CRYPTOCURRENCY_',    Configuration::get('PS_OS_CRYPTOCURRENCY'));	

class CryptoCurrency extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public $details;
	public $owner;
	public $address;
	public $id_currency;	//new, stores id of current currency used (it's lost otherwise!)
	public $extra_mail_vars;
	public function __construct()
	{
	
		$logger = new FileLogger(0); //0 == nivell de debug. Sense això logDebug() no funciona.
		$logger->setFilename(_PS_ROOT_DIR_.'/log/debug.log');
		
		$this->name = 'cryptocurrency';
		$this->tab = 'payments_gateways';
		$this->version = '0.6';
		$this->author = 'PrestaShop & Victor Blanch';
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
 
		$config = Configuration::getMultiple(array('CRYPTO_CURRENCY_DETAILS', 'CRYPTO_CURRENCY_OWNER', 'CRYPTO_CURRENCY_ADDRESS', 'CRYPTO_CURRENCY_ID_CURRENCY', 'CRYPTO_CURRENCY_NAME_CURRENCY'));
		if (isset($config['CRYPTO_CURRENCY_OWNER']))
			$this->owner = $config['CRYPTO_CURRENCY_OWNER'];
		if (isset($config['CRYPTO_CURRENCY_DETAILS']))
			$this->details = $config['CRYPTO_CURRENCY_DETAILS'];
		if (isset($config['CRYPTO_CURRENCY_ADDRESS'])){
			//format of addreses string: 1|ADDRESSOFBTC,2|ADDRESSOFLTC ... etc
			$sub_addresses = explode(',', $config['CRYPTO_CURRENCY_ADDRESS']);
			if(is_array($sub_addresses)){
				foreach($sub_addresses as $sub){
					$items = explode('|', $sub);
					$this->address[$items[0]] = $items[1];
				}
			}
		}
		
		$logger->logDebug("1. current address array at the beginning: ".var_export($this->address, true));
		
		parent::__construct(); 
		
		//no context before parent::_constuct()!
		$currency = $this->context->currency;		
		
		//$cart = $this->context->cart; //context exists, so we can get currency directly
		$logger->logDebug("1.5. current cart id currency: ".$cart->id_currency/*var_export($this->address, true)*/);
		//$logger->logDebug("1.7. current context: ".var_export($this->context, true));
		
		//update if there is a valid value in the currency 		
		$logger->logDebug("2. currency id set in constructor to (before): ".$currency->id);
		$logger->logDebug("3. currency id stored in config in constructor (before): ".$config['CRYPTO_CURRENCY_ID_CURRENCY']);
		if(isset($currency->id) && $currency->id!='' && $currency->id!=null && $currency->id!=false){
			Configuration::updateValue('CRYPTO_CURRENCY_ID_CURRENCY', $currency->id);
			Configuration::updateValue('CRYPTO_CURRENCY_NAME_CURRENCY', $currency->name);
			$this->id_currency = $config['CRYPTO_CURRENCY_ID_CURRENCY'];
			//save name also so we have it!
			$this->name_currency = $config['CRYPTO_CURRENCY_NAME_CURRENCY'];	// //DEFINE NAME_CURRENCY
			//$logger->logDebug("4. currency id set in constructor to: ".$currency->id);
		}		
		
		$logger->logDebug("5. currency id set in constructor to (after): ".$currency->id);
		$logger->logDebug("6. currency id stored in config in constructor (after): ".$config['CRYPTO_CURRENCY_ID_CURRENCY']);
		
		$this->displayName = $this->l('Cryptocurrency');
		$this->description = $this->l('Accept payments for your products via Cryptocurrency cryptocurrency.');
		$this->confirmUninstall = $this->l('Are you sure about removing these details?');
		if (!isset($this->owner) || !isset($this->details) || !isset($this->address))
			$this->warning = $this->l('Wallet owner and wallet details must be configured before using this module.');
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.'); 
		
		$current_address = $this->address[$config['CRYPTO_CURRENCY_ID_CURRENCY']];		
		$current_currency_name = $config['CRYPTO_CURRENCY_NAME_CURRENCY'];
		
		$logger->logDebug("7. current address array before extra mail vars ".var_export($this->address, true));
		$logger->logDebug("8. current address before extra mail vars: : ".$current_address);
		
		//get currencies attached to the module
		//$cart = $this->context->cart;
		//$currencies_module = $this->getCurrency((int)$cart->id_currency);
			
		//$current_currency_name=$this->l('Not available');
		/*
		foreach ($currencies_module as $currency_module){ 
			$logger->logDebug("Currency of module structure".var_export($currency_module, true));
			$logger->logDebug("currency_module['id_currency']: ".$currency_module['id_currency']);			
			$logger->logDebug("config['CRYPTO_CURRENCY_ID_CURRENCY']: ".$config['CRYPTO_CURRENCY_ID_CURRENCY']);			
			if ($currency_module['id_currency']==$config['CRYPTO_CURRENCY_ID_CURRENCY']){
				$current_currency_name = $currency_module['name'];
			}
		}		
		*/
		//$current_currency_name = Currency::getCurrencies()[$config['CRYPTO_CURRENCY_ID_CURRENCY']];
		
		
		//$logger->logDebug("CURRENCY NAME: ".var_export($current_currency_name, true)); 
		$logger->logDebug("9. CURRENCY NAME: ".var_export($current_currency_name, true));		 	
		   
		$this->extra_mail_vars = array(
			'{wallet_owner}' => Configuration::get('CRYPTO_CURRENCY_OWNER'),
			'{wallet_details}' => nl2br(Configuration::get('CRYPTO_CURRENCY_DETAILS')),
			//'{wallet_address}' => /*nl2br(Configuration::get('CRYPTO_CURRENCY_ADDRESS'))*/ 'this is main cryptocurrency php file'
			//'{wallet_address}' => nl2br(Configuration::get('CRYPTO_CURRENCY_ADDRESS')[$currency->id])
			'{wallet_currency_name}' => $current_currency_name,
			'{wallet_address}' => $current_address
			//'{wallet_address}' => nl2br(strval($currency->id).Configuration::get('CRYPTO_CURRENCY_ADDRESS')[strval($currency->id)])
		); 
		  
		/* For 1.4.3 and less compatibility */
		$updateConfig = array('PS_OS_CHEQUE', 'PS_OS_PAYMENT', 'PS_OS_PREPARATION', 'PS_OS_SHIPPING', 'PS_OS_CANCELED', 'PS_OS_REFUND', 'PS_OS_ERROR', 'PS_OS_OUTOFSTOCK', 'PS_OS_BANKWIRE', 'PS_OS_PAYPAL', 'PS_OS_WS_PAYMENT', 'PS_OS_CRYPTOCURRENCY');
		if (!Configuration::get('PS_OS_PAYMENT'))
			foreach ($updateConfig as $u)
				if (!Configuration::get($u) && defined('_'.$u.'_'))
					Configuration::updateValue($u, constant('_'.$u.'_'));		 								
										
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn'))
			return false;
		
		
		//register module in hook table
		
		$results_config = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'configuration`
			WHERE `name`=\'PS_OS_CRYPTOCURRENCY\'');
			
		if(!$results_config)
			$return1 = Db::getInstance()->Execute('
				INSERT IGNORE INTO `'._DB_PREFIX_.'configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
				(\'PS_OS_CRYPTOCURRENCY\', \'14\', NOW(), NOW())');
			//14 = cryptos (1-13 are order states)
			
		//set color and state for the crypto order state

		$return2 = Db::getInstance()->Execute('
			INSERT IGNORE INTO `'._DB_PREFIX_.'order_state` (`id_order_state`, `invoice`, `send_email`, `module_name`, `color`, `unremovable`, `hidden`, `logable`, `delivery`, `shipped`, `paid`, `deleted`) VALUES
			(14, 0, 1, \'cryptocurrency\', \'RoyalBlue\', 1, 0, 0, 0, 0, 0, 0)');
			
		$results_langs = Db::getInstance()->executeS('
			SELECT DISTINCT `id_lang`
			FROM `'._DB_PREFIX_.'order_state_lang`');
			 
		if ($results_langs)
			foreach ($results_langs as $result)
				//$compareProducts[] = $result['id_product'];			
				$return3 = Db::getInstance()->Execute('
					INSERT IGNORE INTO `'._DB_PREFIX_.'order_state_lang` (`id_order_state`, `id_lang`, `name`, `template`) VALUES
					(14, '.$result['id_lang'].', \'Awaiting cryptocurrency transaction\', \'cryptocurrency\')');
		
		//update "condition" table: set the same condition as bankwire or cheque for cryptocurrency
		$return2 = Db::getInstance()->Execute('
			UPDATE IGNORE `'._DB_PREFIX_.'condition` 
			SET `request` = REPLACE (
			`request`, 
			\'"bankwire", "cheque"\', 
			\'"bankwire", "cryptocurrency", "cheque"\')'); 
		
		//TODO: find a way to show warnings in install!
		//try copy mail templates in english to /mails/en if possible
		$ps_mailspath_en = dirname(__FILE__).'/../../mails/en';
		if(is_dir($ps_mailspath_en)){
		
			//try to copy html
			$file = dirname(__FILE__).'/mails/en/cryptocurrency.html';
			$newfile = dirname(__FILE__).'/../../mails/en/cryptocurrency.html';
			
			//copy but dont overwrite files
			if(file_exists($newfile)){
				$this->warning = $this->l('File ').$newfile.$this->l(' already exists, copy aborted.');
				//$this->context->controller->errors[] = 'give an error regardless';
			}else{
				if (!copy($file, $newfile)) {
					$this->warning = $this->l('Failed to copy mail template file ').$newfile.'.';
				}
			}
			 
			//try to copy txt
			$file = dirname(__FILE__).'/mails/en/cryptocurrency.txt';
			$newfile = dirname(__FILE__).'/../../mails/en/cryptocurrency.txt';

			//copy but dont overwrite files
			if(file_exists($newfile)){
				$this->warning = $this->l('File ').$newfile.$this->l(' already exists, copy aborted.');
			}else{
				if (!copy($file, $newfile)) {
					$this->warning = $this->l('Failed to copy mail template file ').$newfile.'.';
				}
			}
		}else{
			$this->warning = $this->l('Folder ').$ps_mailspath_en.$this->l(' not found, copy of mail templates aborted.');
		}
		
		//try to copy coins icon
		$ps_img_os_dir = dirname(__FILE__).'/../../img/os';
		if(is_dir($ps_img_os_dir)){		
		
			//try to copy html
			$file = dirname(__FILE__).'/img/os/14.gif';
			$newfile = dirname(__FILE__).'/../../img/os/14.gif';
			
			//copy but dont overwrite files
			if(file_exists($newfile)){
				$this->warning = $this->l('File ').$newfile.$this->l(' already exists, copy aborted.');
				//$this->context->controller->errors[] = 'give an error regardless';
			}else{
				if (!copy($file, $newfile)) {
					$this->warning = $this->l('Failed to copy mail template file ').$newfile.'.';
				}
			}		
		}
		
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('CRYPTO_CURRENCY_DETAILS')
				|| !Configuration::deleteByName('CRYPTO_CURRENCY_OWNER')
				|| !Configuration::deleteByName('CRYPTO_CURRENCY_ADDRESS')
				|| !Configuration::deleteByName('CRYPTO_CURRENCY_ID_CURRENCY')
				|| !Configuration::deleteByName('CRYPTO_CURRENCY_NAME_CURRENCY')
				|| !parent::uninstall())
			return false;
		return true;
	}

	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('details'))
				$this->_postErrors[] = $this->l('Wallet details are required.');
			elseif (!Tools::getValue('owner'))
				$this->_postErrors[] = $this->l('Wallet owner is required.');
		}
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('CRYPTO_CURRENCY_DETAILS', Tools::getValue('details'));
			Configuration::updateValue('CRYPTO_CURRENCY_OWNER', Tools::getValue('owner'));
			
			$crypto_addresses = array();			
			$cart = $this->context->cart;
			$currencies_module = $this->getCurrency((int)$cart->id_currency);			
			
			foreach ($currencies_module as $currency_module){
				$caddress_id = $currency_module['id_currency'];
				$caddress    = Tools::getValue('address_'.$currency_module['id_currency']);
				$crypto_addresses[]= $caddress_id.'|'.$caddress;
			}
			Configuration::updateValue('CRYPTO_CURRENCY_ADDRESS', implode(',', $crypto_addresses));						
			//print_r("result of implode:\n<br/>");
			//print_r(implode(',', $crypto_addresses));
			//print_r("<br/>/result of implode:\n<br/>");
		}
		$this->_html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
	}

	private function _displayCryptoCurrency()
	{
		$this->_html .= '<img src="../modules/cryptocurrency/cryptocurrency.jpg" style="float:left; margin-right:15px;" width="86" height="49"><b>'.$this->l('This module allows you to accept payments by cryptocurrency transactions.').'</b><br /><br />
		'.$this->l('If the client chooses to pay with a cryptocurrency transaction, the order\'s status will change to "Waiting for Payment."').'<br />
		'.$this->l('That said, you must manually confirm the order upon receiving the cryptocurrency transaction. ').'<br /><br /><br />';
	}

	private function _displayForm()
	{
		//get currencies attached to the module
		$cart = $this->context->cart;
		$currencies_module = $this->getCurrency((int)$cart->id_currency);
		
		$this->_html .=
		'<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Contact details').'</legend>
				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Please specify the cryptocurrency wallet details for customers.').'.<br /><br /></td></tr>
					<tr><td width="130" style="height: 35px;">'.$this->l('Wallet owner').'</td><td><input type="text" name="owner" value="'.htmlentities(Tools::getValue('owner', $this->owner), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Details').'</td>
						<td style="padding-bottom:15px;">
							<textarea name="details" rows="4" cols="53">'.htmlentities(Tools::getValue('details', $this->details), ENT_COMPAT, 'UTF-8').'</textarea>
							<p>'.$this->l('Such as additional messages or instructions...').'</p>
						</td>
					</tr>';
					
					foreach ($currencies_module as $currency_module){
						$this->_html .='
						<tr><td width="130" style="height: 35px;">'.$this->l('Wallet address for').' '.$currency_module['name'].'</td><td><input type="text" name="address_'.$currency_module['id_currency'].'" value="'.htmlentities(Tools::getValue('address_'.$currency_module['id_currency'], $this->address[$currency_module['id_currency']]), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
						';
					}
		$this->_html .='
		
					<tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= '<div class="alert error">'.$err.'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayCryptoCurrency();
		$this->_displayForm();

		return $this->_html;
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;


		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_bw' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		//if ($state == Configuration::get('PS_OS_BANKWIRE') || $state == Configuration::get('PS_OS_OUTOFSTOCK'))
		if ($state == Configuration::get('PS_OS_CRYPTOCURRENCY') || $state == Configuration::get('PS_OS_OUTOFSTOCK'))
		{		
			$currency = $this->context->currency;					
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'cryptocurrencyDetails' => Tools::nl2br($this->details),
				//'cryptocurrencyAddress' => Tools::nl2br($this->address),
				//(('cryptocurrencyAddress' => Tools::nl2br($currency->id.' '.$this->address.' '.var_export($this->address, true).' '.$currency_address), 
				'cryptocurrencyName' => Tools::nl2br($currency->name),
				'cryptocurrencyAddress' => Tools::nl2br($this->address[$currency->id]),
				'cryptocurrencyOwner' => $this->owner,
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}
	
	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}
}
