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
        $postVars = $params['_POST'];
        $registration_type = (int)Configuration::get('PS_REGISTRATION_PROCESS_TYPE');
        
        if (empty($postVars)) {
            return false;
        }

        $newsletter = ((isset($postVars['newsletter']) && $postVars['newsletter'] == 1) ? 'Yes' : 'No');
        $address2 = (isset($postVars['address2']) ? $postVars['address2'] : '');
        $postcode = (isset($postVars['postcode']) ? $postVars['postcode'] : '');
        $phone = (isset($postVars['phone']) ? $postVars['phone'] : '');
        $phone_mobile = (isset($postVars['phone_mobile']) ? $postVars['phone_mobile'] : '');

        $data = array(
            '{firstname}' => $postVars['firstname']
            ,'{lastname}' => $postVars['lastname']
            ,'{email}' => $postVars['email']
            ,'{newsletter}' => $newsletter
            ,'{birthday}' => $postVars['months'].'/'.$postVars['days'].'/'.$postVars['years']
            ,'{address1}' => ($registration_type == 0 ? 'N/A' : $postVars['address1'])
            ,'{address2}' => ($registration_type == 0 ? 'N/A' : $address2)
            ,'{postcode}' => ($registration_type == 0 ? 'N/A' : $postcode)
            ,'{city}' => ($registration_type == 0 ? 'N/A' : $postVars['city'])
            ,'{country}' => ($registration_type == 0 ? 'N/A' : Country::getNameById(Context::getContext()->cookie->id_lang, (int)$postVars['id_country']))
            ,'{state}' => ($registration_type == 0 ? 'N/A' : State::getNameById((int)$postVars['id_state']))
            ,'{phone}' => ($registration_type == 0 ? 'N/A' : $phone)
            ,'{phone_mobile}' => ($registration_type == 0 ? 'N/A' : $phone_mobile)
            ,'{company}' => ($registration_type == 0 ? 'N/A' : $postVars['company'])
            ,'{other}' => ($registration_type == 0 ? 'N/A' : $postVars['other'])
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
