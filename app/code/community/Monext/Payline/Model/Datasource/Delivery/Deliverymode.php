<?php

/**
 * Class used as a datasource for deliveryTime
 * @from https://payline.atlassian.net/wiki/spaces/DT/pages/28901416/Codes+-+deliveryMode
 *
 */
class Monext_Payline_Model_Datasource_Delivery_Deliverymode
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('payline')->__('Collect goods from the merchant')),
            array('value' => 2, 'label' => Mage::helper('payline')->__('Use a network of third-party pick-up points (such as Kiala, Alveol, etc.)')),
            array('value' => 3, 'label' => Mage::helper('payline')->__('Collect from an airport, a train station or a travel agent')),
            array('value' => 4, 'label' => Mage::helper('payline')->__('Mail (Colissimo, UPS, DHL, etc., or any private courier)')),
            array('value' => 5, 'label' => Mage::helper('payline')->__('Issuing an electronic ticket, downloads')),
            array('value' => 6, 'label' => Mage::helper('payline')->__('Ship to cardholder’s billing address')),
            array('value' => 7, 'label' => Mage::helper('payline')->__('Ship to another verified address on file with merchant')),
            array('value' => 8, 'label' => Mage::helper('payline')->__('Ship to address that is different than the cardholder’s billing address')),
            array('value' => 9, 'label' => Mage::helper('payline')->__('Travel and Event tickets, not shipped')),
            array('value' => 10, 'label' => Mage::helper('payline')->__('Locker delivery (or other automated pick-up)')),
            array('value' => 999, 'label' => Mage::helper('payline')->__('Other')),
        );
    }
}
