<?php

/**
 * Class used as a datasource for deliveryTime
 * @from https://payline.atlassian.net/wiki/spaces/DT/pages/1192854248/Codes+-+deliveryTime
 *
 */
class Monext_Payline_Model_Datasource_Delivery_Deliverytime
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('payline')->__('Express')),
            array('value' => 2, 'label' => Mage::helper('payline')->__('Standard')),
            array('value' => 3, 'label' => Mage::helper('payline')->__('Electronic Delivery')),
            array('value' => 4, 'label' => Mage::helper('payline')->__('Same day shipping')),
            array('value' => 5, 'label' => Mage::helper('payline')->__('Overnight shipping')),
            array('value' => 6, 'label' => Mage::helper('payline')->__('Two-day or more shipping')),
        );
    }
}
