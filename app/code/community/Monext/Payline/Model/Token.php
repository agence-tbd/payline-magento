<?php

/**
 * Payline token model 
 */

class Monext_Payline_Model_Token extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('payline/token');
    }
}
