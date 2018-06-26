<?php

class Monext_Payline_Helper_Widget extends Monext_Payline_Helper_Data
{

    protected $_token;


    /**
     * Order increment ID getter (either real from order or a reserved from quote)
     *
     * @return string
     */
    protected function _getReservedOrderId($quote)
    {
        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId()->save();
        }
        return $quote->getReservedOrderId();
    }

    public function getDataTokenForShortcut($keepSessionToken=false)
    {
        $checkoutSession = Mage::getSingleton('checkout/session');
        if($keepSessionToken and $checkoutSession->getPaylineDataToken()) {
            $webPaymentDetails = Mage::helper('payline')->initPayline('CPT')->getWebPaymentDetails(array('token' => $checkoutSession->getPaylineDataToken(), 'version' => Monext_Payline_Helper_Data::VERSION));

            if(isset($webPaymentDetails) and !empty($webPaymentDetails['result'])) {
                if($webPaymentDetails['result']['code']!='02306') {
                    $checkoutSession->unsPaylineDataToken();
                }
            } else {
                $checkoutSession->unsPaylineDataToken();
            }

            if($checkoutSession->getPaylineDataToken()) {
                return $checkoutSession->getPaylineDataToken();
            }
        }

        $token = $this->getDataToken(true);
        $checkoutSession->setPaylineDataToken($token);

        return $token;
    }

    public function getDataToken($forShortcut = false)
    {
        if (is_null($this->_token)) {
            $this->_token = false;
            try {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $orderId = $this->_getReservedOrderId($quote);
                $quote->setRealOrderId($orderId);

                $array = Mage::helper('payline/payment')->initWithQuote($quote);
                $array['version'] = Monext_Payline_Helper_Data::VERSION;
                $array['payment']['action'] = Mage::getStoreConfig('payment/PaylineCPT/payline_payment_action');
                $array['payment']['mode'] = 'CPT';

                $returnUrl = Mage::getUrl('payline/index/cptReturnWidget');
                $array['payment']['contractNumber'] = $this->getDefaultContractNumberForWidget();
                $array['contracts'] = $this->getContractsForWidget(true);
                $array['secondContracts'] = $this->getContractsForWidget(false);
                if(empty($array['secondContracts'])) {
                    $array['secondContracts'] = array('');
                }

                if($forShortcut) {
                    $returnUrl = Mage::getUrl('payline/index/cptWidgetShortcut');
                    $array['payment']['contractNumber'] = $this->getContractNumberForWidgetShortcut();
                    $array['contracts'] = array($array['payment']['contractNumber']);
//                    $array['secondContracts'] = array('');
                }


                $paylineSDK = $this->initPayline('CPT', $array['payment']['currency']);
                $paylineSDK->returnURL          = $returnUrl;
                $paylineSDK->cancelURL          = $paylineSDK->returnURL;
                $paylineSDK->notificationURL    = $paylineSDK->returnURL;

                // WALLET
                // ADD CONTRACT WALLET ARRAY TO $array
                $helperPayline = Mage::helper('payline');
                $array['walletContracts'] = $helperPayline->buildContractNumberWalletList();

                if (Mage::getStoreConfig('payment/PaylineCPT/send_wallet_id')) {

                    if (! isset($array['buyer']['walletId'])) {
                        if (isset($this->walletId)) {
                            $array['buyer']['walletId'] = $this->walletId;
                        }
                    }

                    $expiredWalletId = false;
                    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $customer = Mage::getSingleton('customer/session')->getCustomer();
                        if ($customer->getWalletId() && ! Mage::getModel('payline/wallet')->checkExpirationDate()) {
                            $expiredWalletId = true;
                        }
                    }

                    if ($expiredWalletId) {
                        $this->walletId = null;
                    }

                    if ($helperPayline->canSubscribeWallet()) {
                        // If the wallet is new (registered during payment), we must
                        // save it in the private data since it's not sent back by
                        // default
                        if ($this->isNewWallet) {
                            if ($this->walletId) {
                                $paylineSDK->setPrivate(array('key' => 'newWalletId',
                                                              'value' => $this->walletId));
                            }
                        }
                    }
                }

                $response = $paylineSDK->doWebPayment($array);
                if(isset($response) and $response['result']['code'] == '00000' and !empty($response['token'])){
                    $this->_token =  $response['token'];
                    Mage::getModel('payline/token')
                        ->setOrderId($quote->getRealOrderId())
                        ->setToken($this->_token)
                        ->setDateCreate(time())
                        ->save();
                }

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $this->_token;
    }

    public function getDataTemplate()
    {
        return $this->getCptConfigTemplate();
    }

    /**
     * @return bool
     */
    public function getUseCheckoutGuestMethodForWidgetShortcut()
    {
        return (Mage::getStoreConfig('payline/PaylineSHORTCUT/checkout_method') == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
    }

    public function getContractNumberForWidgetShortcut()
    {
        return Mage::getStoreConfig('payline/PaylineSHORTCUT/use_contracts');
    }

    public function getAllowedShippingMethodForWidgetShortcut()
    {
        $methods = explode(',', Mage::getStoreConfig('payline/PaylineSHORTCUT/shipping_method_allowed'));

        return $methods;
    }


    /**
     * @param Mage_Core_Controller_Request_Http $request
     *
     * @return array
     * @throws Exception
     */
    public function prepareShortcutPostData($request)
    {
        if(!$request->getParam('email')) {
            throw new Exception('Email from amazon cannot be empty');
        }

        if(!$request->getParam('lastName')) {
            throw new Exception('LastName from amazon cannot be empty');
        }


        $shipping = $billing = array('save_in_address_book'=>0);

        $commonParams = array('firstName'=>'firstname', 'lastName'=>'lastname', 'email'=>'email');
        foreach ($commonParams as $paylineKey=>$mageKey) {
            $shipping[$mageKey] =  $billing[$mageKey] =  $request->getParam($paylineKey);
        }

        $baseParams = array('cityName'=>'city', 'zipCode'=>'postcode', 'country'=>'country_id', 'phone'=>'telephone');
        foreach ($baseParams as $paylineKey=>$mageKey) {
            $shipping[$mageKey] =  $request->getParam($paylineKey);
            $billing[$mageKey] =  $request->getParam('billing'.uc_words($paylineKey));
        }

        $configParams = array('prefix', 'middlename', 'suffix', 'dob', 'taxvat', 'gender');
        foreach ($configParams as $configKey) {
            if(Mage::helper('customer/address')->getConfig($configKey .'_show') == 'req') {
                $shipping[$configKey] = $billing[$configKey] = $this->getDefaultAttributeValue($configKey, '-');
            }
        }

        $billing = $this->_amazonFormatFullName($billing);
        $shipping = $this->_amazonFormatFullName($shipping);

        $shipping['street'] =   $billing['street'] =  array($request->getParam('street1'), $request->getParam('street2'));

        if($request->getParam('billingStreet1')) {
            $billing['street'] = array($request->getParam('billingStreet1'), $request->getParam('billingStreet2'));
        }

        if(empty($shipping['telephone'])) {
            $shipping['telephone'] = $this->getDefaultAttributeValue('telephone', '0000000000');
        }

        if(empty($billing['telephone'])) {
            $billing['telephone'] = $this->getDefaultAttributeValue('telephone', '0000000000');
        }

        $billing['region'] = $shipping['region'] = $this->getDefaultAttributeValue('region', '-');
        $billing['region_id'] = $shipping['region_id'] = $this->getDefaultAttributeValue('region', '1');

        return array('billing'=>$billing, 'shipping'=>$shipping);
    }

    /**
     * @param array $data
     */
    protected function _amazonFormatFullName($data)
    {
        if(empty($data['firstname'])) {
            if(preg_match('/(.*?)\s(.*)/', $data['lastname'], $match)) {
                $data['firstname'] = $match[1];
                $data['lastname'] = $match[2];
            } else {
                $data['firstname'] = $this->getDefaultAttributeValue('firstname', 'n/a');;
            }
        }
        return $data;
    }

    protected function getDefaultAttributeValue($attributeCode='', $defaultValue='-') {
        $default = Mage::getStoreConfig('payline/PaylineSHORTCUT/default_address_value/'.$attributeCode);
        return !empty($default) ? $default : $defaultValue;
    }


} // end class
