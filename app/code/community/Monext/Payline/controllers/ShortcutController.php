<?php
require_once Mage::getModuleDir('controllers', "Mage_Checkout") . DS . "OnepageController.php";

class Monext_Payline_ShortcutController extends Mage_Checkout_OnepageController
{

    public function indexAction()
    {
        parent::indexAction();

        try {
            $contractNumber = Mage::helper('payline/widget')->getContractNumberForWidgetShortcut();
            $data=array('method'=>'PaylineCPT', 'cc_type'=>$contractNumber);
            $this->getOnepage()->savePayment($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_redirect('checkout/cart');
        }
    }

    public function saveAddressesAction()
    {

        $this->getOnepage()->saveCheckoutMethod('guest');


        $this->_preparePostData();


        $this->saveBillingAction();

        $result =  Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
        if(empty($result['error']) and !empty($result['goto_section']) and $result['goto_section']=='shipping-method' ) {

        }

        $result['post'] = $this->getRequest()->getPost();

        $this->_prepareDataJSON($result);
    }

    public function saveShippingMethodAction()
    {
        parent::saveShippingMethodAction();
        $result =  Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
        if(empty($result['error']) and !empty($result['goto_section']) and $result['goto_section']=='payment' ) {





//            $contractNumber = Mage::helper('payline/widget')->getContractNumberForWidgetShortcut();
//            $data=array('method'=>'PaylineCPT', 'cc_type'=>$contractNumber);
//            $this->getOnepage()->savePayment($data);

            //$this->loadLayout('checkout_onepage_review');
            $result['goto_section'] = 'review';
            $result['update_section'] = array(
                'name' => 'review',
                'html' => $this->_getReviewHtml()
            );

            $result['base_grand_total'] = $this->getOnepage()->getQuote()->getBaseGrandTotal();
        }
        $this->_prepareDataJSON($result);
    }

    protected function _preparePostData()
    {
        $billing = array();

        $commonParams = array('firstName'=>'firstname', 'lastName'=>'lastname', 'email'=>'email');

        $baseParams = array('street1'=>'street1', 'street2'=>'street2', 'cityName'=>'city', 'zipCode'=>'postcode', 'country'=>'country_id');




        foreach ($commonParams as $paylineKey=>$mageKey) {
            $billing[$mageKey] =  $this->getRequest()->getParam($paylineKey);
        }

        foreach ($baseParams as $paylineKey=>$mageKey) {
            $billing[$mageKey] =  $this->getRequest()->getParam($paylineKey);
        }

        if(empty($billing['firstname'])) {
            $billing['firstname'] = 'n/a';
        }

        //array('save_in_address_book'=>0, 'use_for_shipping'=>1);

        $billing['street'] = array($this->getRequest()->getParam('street1'), $this->getRequest()->getParam('street2'));
        $billing['save_in_address_book'] = 0;
        $billing['use_for_shipping'] = 1;

        //TODO
        //telephone
        //region
        //region_id
        //vat_id
        //company
        $billing['telephone'] = '0606060606';
        //$billing['street'] = array('rue dummy');
        $billing['region'] = "13";
        $billing['region_id'] = "13";

        $this->getRequest()->setPost('billing', $billing);

    }

    /**
     * Get payment method step html
     *
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('payline_shortcut_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }


    /**
     * Get payment method step html
     *
     * @return string
     */
    protected function _getReviewHtml()
    {

        //return $this->getLayout()->getBlock('root')->toHtml();
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('payline_shortcut_review');
        $layout->generateXml();
        $layout->generateBlocks();
//        $output = $layout->getOutput();
//        return $output;
        return $layout->getBlock('root')->toHtml();
    }
}
