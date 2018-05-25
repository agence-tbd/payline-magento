<?php

class Monext_Payline_Block_Checkout_Widget_Shortcut_Shipping_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    public function getShippingRates()
    {
        $excludeMethods = Mage::helper('payline/widget')->getAllowedShippingMethodForWidgetShortcut();
        $shippingRateGroups = parent::getShippingRates();
        foreach ($shippingRateGroups as $code => $_rates) {
            foreach ($_rates as $_rateKey=>$_rate) {
                if(!in_array($_rate->getCode(), $excludeMethods)) {
                    unset($shippingRateGroups[$code][$_rateKey]);
                }
            }
            if (empty($shippingRateGroups[$code])) {
                unset($shippingRateGroups[$code]);
            }

        }


        return $shippingRateGroups;
    }


}
