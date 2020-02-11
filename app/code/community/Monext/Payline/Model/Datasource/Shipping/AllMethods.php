<?php

/**
 * Class used as a datasource to display available shipping methods
 *
 */
class Monext_Payline_Model_Datasource_Shipping_AllMethods
{
    public function toOptionArray()
    {
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();

        $options = array();

        foreach ($carriers as $_ccode => $_carrier) {
            $_methodOptions = array();
            if ($_methods = $_carrier->getAllowedMethods()) {
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    $_methodOptions[] = array('value' => $_code, 'label' => $_method);
                }

                if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                    $_title = $_ccode;

                $options[] = array('value' => $_methodOptions, 'label' => $_title);
            }
        }

        return $options;
    }
}