<?php

class Monext_Payline_Block_Adminhtml_System_Config_Renderer_Title extends Mage_Adminhtml_Block_Html_Select
{
    /**
     * Retrieve select options
     *
     * @return array
     */
    protected function _getOptions()
    {
        return Mage::getModel('payline/datasource_customer_title')->toOptionArray();
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
        foreach ($this->_getOptions() as $optionId => $option) {
            $this->addOption($option['value'], addslashes($option['value'] . ' - ' . $option['label']));
        }
        return parent::_toHtml();
    }
}