<?php

class Monext_Payline_Block_Checkout_Widget_Shortcut_Button extends Mage_Core_Block_Template
{
    public function hasToDisplay()
    {
        $shortcutEnable = Mage::getStoreConfig('payline/PaylineSHORTCUT/active');
        return $shortcutEnable;
    }
}
