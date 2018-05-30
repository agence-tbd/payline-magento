<?php
class Monext_Payline_Model_Datasource_Checkout_Method
{
    public function toOptionArray()
    {
        $options = array();
        $options[] = array('value' => Mage_Checkout_Model_Type_Onepage::METHOD_GUEST, 'label' => Mage::helper('payline')->__('Guest'));
        $options[] = array('value' => Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER, 'label' => Mage::helper('payline')->__('Register and auto log'));

        return $options;
    }


}
