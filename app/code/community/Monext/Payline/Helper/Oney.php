<?php

/**
 * In staging environment: https://oney-staging.azure-api.net/staging/[API_PATH]
 * In production environment: https://api.oney.io/[API_PATH]
 *
 *
 * This service is used to simulate a payment.
 *
 * GET /sale_support_tools/v1/simulation?
 * merchant_guid={merchant_guid}&
 * payment_amount={payment_amount}&
 * business_transaction_code={business_transaction_code}
 */



class Monext_Payline_Helper_Oney extends Mage_Core_Helper_Data
{
    const STATUS_PENDING_ONEY = 'pending_oney';

    protected $_pending_quote_id;

    protected $_pending_oney;

    protected $_quote_for_simulation;


    protected $_mapping_cttype_oney;


    protected $_api_url = array('PROD' => 'https://api.oney.io/',
        'HOMO' => 'https://oney-staging.azure-api.net/staging/'
    );

    public function getJsonSimulation($quote, $contractType)
    {
        if(!$this->_getConfig('instalment_active')
                || !$this->_getMappingCtTypeOney()
                || empty($this->_api_url[$this->_getEnv()]) ) {
            return;
        }

        $this->_quote_for_simulation = $quote;

        return $this->_getSimulationForQuote($quote, $contractType);
    }

    protected function _getSimulationForQuote($quote, $contractType)
    {
        $transactionCode = $this->_getTransactionCode($contractType);
        if(!$transactionCode) {
            return array();
        }

        $apiParams = array();
        $apiParams['merchant_guid'] = $this->_getConfig('merchant_guid');
        $apiParams['psp_guid'] = $this->_getConfig('psp_guid');
        $apiParams['payment_amount'] = $quote->getGrandTotal();
        $apiParams['business_transaction_code'] = $transactionCode;

        $response = $this->_getJsonResponse('sale_support_tools/v1/simulation', $apiParams);

        return $response ? $this->_formatSimulationJson($response) : array();

    }

    protected function _getJsonResponse($apiPath, $apiParams)
    {
        $client = new Varien_Http_Client();
//        $client->setConfig(array(
//            'timeout' => 45,
//            'verifyhost' => 2,
//            'verifypeer' => true,
//        ));

        $client->setUri($this->_getApiUrl($apiPath));
        foreach ($apiParams as $paramKey=>$paramValue) {
            $client->setParameterGet($paramKey, $paramValue);
        }

        $client->setHeaders(array('Accept: application/json'));
        $client->setHeaders('X-Oney-Authorization' , $this->_getConfig('marketing_api_key')); //Value of partner API KEY (PSP or merchant or brand)
        $client->setHeaders('X-Oney-Partner-Country-Code' , 'FR'); //Country code ISO 3166-1 alpha-2 of partner (merchant)

        $response = false;
        try {
            $response = $client->request(Varien_Http_Client::GET)->getBody();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $response;
    }

    protected function _formatSimulationJson($response)
    {
        $simulation = Mage::helper('core')->jsonDecode($response);

        array_walk_recursive($simulation, array($this, '_walkFunction'));

        return $simulation;
    }

    protected function _walkFunction(&$item, $key)
    {
        if(in_array($key, array('first_instalment_date', 'collection_date'))) {
            $item = new Zend_Date($item);
            $item = $item->toString('dd/MM/Y');
        } elseif (in_array($key, array('down_payment_amount', 'accrued_interest_amount', 'instalment_amount', 'interest_amount') )) {
            $item = $this->_quote_for_simulation->getStore()->formatPrice($item);
        }
    }

    protected function _getMappingCtTypeOney($key = null)
    {
        if(is_null($this->_mapping_cttype_oney)) {
            $this->_mapping_cttype_oney = Mage::helper('core')->jsonDecode( $this->_getConfig('mapping_cttype'));
        }

        if(empty($key)) {
            return $this->_mapping_cttype_oney;
        }

        return !empty($this->_mapping_cttype_oney[$key]) ? $this->_mapping_cttype_oney[$key] : false;

    }

    protected function _getConfig($path)
    {
        return Mage::getStoreConfig('payment/PaylineCPT/oney_' . $path);
    }

    protected function _getApiUrl($apiPath)
    {
        return !empty($this->_api_url[$this->_getEnv()]) ?  $this->_api_url[$this->_getEnv()] . $apiPath : false;
    }

    protected function _getEnv()
    {
        return Mage::getStoreConfig('payment/payline_common/environment');
    }

    protected function _getTransactionCode($contractType)
    {
        return $this->_getMappingCtTypeOney($contractType);
    }

    public function isPengingOney($resultCode, $quoteId = 0)
    {
        if($resultCode && $quoteId) {

            if(!empty($this->_pending_quote_id) and $this->_pending_quote_id == $quoteId ) {
                return $this->_pending_oney;
            }
            $this->_pending_oney = false;
            $this->_pending_quote_id = $quoteId;

            // List of accepted codes
            $acceptedCodes = array(
                '36030', // Waiting for payment validation by Oney
                '02306', // Pending Oney ... à vérifier
            );

            // Transaction OK
            if (in_array($resultCode, $acceptedCodes) ) {
                $quote = Mage::getModel('sales/quote_payment')->getCollection()->addFieldToFilter('quote_id', $quoteId);
                $quote->getSelect()->join( array('pc'=>$quote->getTable('payline/contract')),
                    'main_table.cc_type=pc.number and main_table.method="PaylineCPT" and pc.contract_type like "%ONEY"');

                if($quote->getSize() == 1) {
                    $this->_pending_oney = true;
                }
            }
        }

        return $this->_pending_oney;
    }
}
