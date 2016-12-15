<?php
/*
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
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class MemberAlert extends Module
{
    private $_merchant_mails;
    const __MA_MAIL_DELIMITOR__ = "\n";

    public function __construct()
    {
        $this->name = 'memberalert';
        $this->tab = 'administration';
        $this->version = '1.0';
        $this->author = 'Prestanesia';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Member Alert');
        $this->description = $this->l('Notify website owner for every new customer registration');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() && $this->registerHook('actionCustomerAccountAdd');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $customer = $params['_POST'];
        
        if (empty($customer)) {
            return false;
        }

        $data = array(
            '{firstname}' => $customer['customer_firstname']
            ,'{lastname}' => $customer['customer_lastname']
            ,'{company}' => (isset($customer['company']) ? $customer['company'] : '-')
            ,'{email}' => $customer['email']
            ,'{newsletter}' => ((isset($customer['newsletter']) && $customer['newsletter'] == 1) ? 'Yes' : 'No')
            ,'{birthday}' => $customer['months'].'/'.$customer['days'].'/'.$customer['years']
            ,'{address1}' => (isset($customer['address1']) ? $customer['address1'] : '-')
            ,'{address2}' => (isset($customer['address2']) ? $customer['address2'] : '-')
            ,'{postcode}' => (isset($customer['postcode']) ? $customer['postcode'] : '-')
            ,'{city}' => (isset($customer['city']) ? $customer['city'] : '-')
            ,'{country}' => Country::getNameById(Context::getContext()->cookie->id_lang, (int)$customer['id_country'])
            ,'{state}' => State::getNameById((int)$customer['id_state'])
            ,'{phone}' => (isset($customer['phone']) ? $customer['phone'] : '-')
            ,'{phone_mobile}' => (isset($customer['phone_mobile']) ? $customer['phone_mobile'] : '-')
            ,'{other}' => (isset($customer['other']) ? $customer['other'] : '-')
        );

        $this->_merchant_mails = Configuration::get('PS_SHOP_EMAIL');
        if (!is_null(Configuration::get('MA_MERCHANT_MAILS')) && Configuration::get('MA_MERCHANT_MAILS') != '') {
            $this->_merchant_mails = strval(Configuration::get('MA_MERCHANT_MAILS'));
        }

        $merchant_mails = explode(self::__MA_MAIL_DELIMITOR__, $this->_merchant_mails);
        foreach ($merchant_mails as $merchant) {
            $merchant = trim($merchant);
            Mail::Send(intval(Configuration::get('PS_LANG_DEFAULT')), 'memberalert', $this->l('New member registration!'), $data, $merchant, null, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), null, null, dirname(__FILE__).'/mails/');
        }
    }
}
