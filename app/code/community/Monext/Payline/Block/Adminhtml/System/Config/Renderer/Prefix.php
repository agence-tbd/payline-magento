<?php

class Monext_Payline_Block_Adminhtml_System_Config_Renderer_Prefix extends Mage_Adminhtml_Block_Html_Select
{
    /**
     * Retrieve select options
     *
     * @return array
     */
    protected function _getOptions()
    {
        return $this->helper('customer')->getNamePrefixOptions($this->getStore());
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $options = $this->_getOptions();
        if(!empty($options) && is_array($options)) {
            foreach ($options as $value => $label) {
                $this->addOption($value, addslashes($label));
            }
        }

        return parent::_toHtml();
    }
}