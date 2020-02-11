<?php

/**
 * Class used as a datasource for deliveryTime
 * @from https://payline.atlassian.net/wiki/spaces/DT/pages/28901418/Codes+-+Title
 * @note only codes 3 and 4 are currently accepted on Payline side
 * @note this is a discrepancy with the documentation and it will be fixed
 *
 */
class Monext_Payline_Model_Datasource_Customer_Title
{
    public function toOptionArray()
    {
        return array(
//            array('value' => 1, 'label' => Mage::helper('payline')->__('Mrs')),
//            array('value' => 2, 'label' => Mage::helper('payline')->__('Ladies')),
            array('value' => 3, 'label' => Mage::helper('payline')->__('Miss')),
            array('value' => 4, 'label' => Mage::helper('payline')->__('Mr. / Mister')),
//            array('value' => 5, 'label' => Mage::helper('payline')->__('Gentlemen')),
//            array('value' => 6, 'label' => Mage::helper('payline')->__('Widow')),
//            array('value' => 7, 'label' => Mage::helper('payline')->__('Dr. / Doctor')),
//            array('value' => 8, 'label' => Mage::helper('payline')->__('Doctors')),
//            array('value' => 9, 'label' => Mage::helper('payline')->__('Pr. / Professor')),
//            array('value' => 10, 'label' => Mage::helper('payline')->__('Mr. or Mrs. (Lawyer)')),
//            array('value' => 11, 'label' => Mage::helper('payline')->__('Mr. or Mrs. (Lawyers)')),
//            array('value' => 12, 'label' => Mage::helper('payline')->__('His Eminence'))
        );
    }
}
