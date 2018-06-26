<?php
require_once Mage::getModuleDir('controllers', "Mage_Checkout") . DS . "OnepageController.php";

class Monext_Payline_ShortcutController extends Mage_Checkout_OnepageController
{

    /**
     * @var Mage_Customer_Model_Session
     */
    protected $_customerSession;

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer = false;

    /**
     * Payline shortcut checkout page
     */
    public function indexAction()
    {
        if(!$this->_canShowReview()) {
            $this->getLayout()->getUpdate()->addHandle('payline_shortcut_review_hide_handler');
        }

        parent::indexAction();

        try {
            if ($this->_useCheckoutGuestMethod()) {
                $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
            }
            $contractNumber = Mage::helper('payline/widget')->getContractNumberForWidgetShortcut();
            $data=array('method'=>'PaylineCPT', 'cc_type'=>$contractNumber);
            $this->getOnepage()->savePayment($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_redirect('checkout/cart');
        }
    }

    /**
     *
     */
    public function saveAddressesAction()
    {
        $result = array();
        try {
            $postData = Mage::helper('payline/widget')->prepareShortcutPostData($this->getRequest());
            $resultAutoAccount = array();
            if (!$this->_useCheckoutGuestMethod()) {
                $resultAutoAccount = $this->_autoAccountCreateOrMatch($postData['billing']);
                if($this->_customer and $this->_customer->getId()) {
                    Mage::getSingleton('customer/session')->setCustomer($this->_customer);
                    $this->getOnepage()->getQuote()->assignCustomer($this->_customer);
                } else {
                    $this->getOnepage()->getQuote()->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
                    $password = $this->_customer->generatePassword();
                    $postData['billing']['customer_password'] = $password;
                    $postData['billing']['confirm_password'] = $password;
                }
            }

            $this->getRequest()->setPost('billing', $postData['billing']);
            $this->getRequest()->setPost('shipping', $postData['shipping']);


            $this->getOnepage()->getQuote()->unsCustomerID();

            $this->saveBillingAction();
            $result =  Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
            if(empty($result['error']) and !empty($result['goto_section']) and $result['goto_section']=='shipping' ) {
                $this->saveShippingAction();
                $result =  Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
                if(empty($result['error']) and !empty($result['goto_section']) and $result['goto_section']=='shipping_method' ) {

                }
            }
            $result= array_merge($resultAutoAccount, $result);

        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__($e->getMessage());
        }

        $this->_prepareDataJSON($result);
    }


    /**
     *
     */
    public function saveShippingMethodAction()
    {
        $result = array();
        try {
            parent::saveShippingMethodAction();

            $result =  Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
            if(empty($result['error']) and !empty($result['goto_section']) and $result['goto_section']=='payment' ) {

                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );

                $result['base_grand_total'] = $this->getOnepage()->getQuote()->getBaseGrandTotal();
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__($e->getMessage());
        }

        $this->_prepareDataJSON($result);
    }

    public function saveOrderAction()
    {
        $result = array();
        try {
            $this->getOnepage()->getQuote()->collectTotals();

            parent::saveOrderAction();

            $result =  Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
            if(empty($result['error']) and !empty($result['success']) ) {
                $result['redirect'] = Mage::getUrl('checkout/onepage/success');
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__($e->getMessage());
        }

        $this->_prepareDataJSON($result);
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

        $update->setCacheId(uniqid("payline_shortcut_shippingmethod"));
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
        if(!$this->_canShowReview()) {
            return '';
        }

        $layout = $this->getLayout();
        $update = $layout->getUpdate();

        $update->setCacheId(uniqid("payline_shortcut_review"));
        $update->load('payline_shortcut_review');

        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getBlock('root')->toHtml();
    }



    protected function _autoAccountCreateOrMatch($data)
    {
        $result = array();
        $session = $this->_getCustomerSession();
        $this->_customer = $customer = Mage::getModel('customer/customer');
        $this->_customer = $customer->setWebsiteId(Mage::app()->getWebsite()->getId());

        if($session->getPaylineCustomerId()) {

            $this->_customer->load($session->getPaylineCustomerId());
            if(!$this->_customer->getId()) {
                $session->unsPaylineCustomerId();
            } else {
                $result['already_identified'] = true;
                return $result;
            }
        }

        try {
            $email = $data['email'];

            if($session->isLoggedIn()) {
                $result['is_logged'] = true;
                if($session->getCustomer()->getEmail()!==$email) {
                    $result['email_mismatch'] = true;
                }
                return false;
            }

            $this->_customer->loadByEmail($data['email']);
            if($this->_customer->getId()) {
                $session->setPaylineCustomerId($this->_customer->getId());
            }

        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__($e->getMessage());
        }

        return $result;
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return bool
     */
    protected function _useCheckoutGuestMethod()
    {
        return Mage::helper('payline/widget')->getUseCheckoutGuestMethodForWidgetShortcut();
    }

    protected function _canShowReview()
    {
        $address = $this->getOnepage()->getQuote()->getShippingAddress();
        if(!$address->getId() or !$address->getShippingMethod()) {
            return false;
        }

        return true;
    }
}
