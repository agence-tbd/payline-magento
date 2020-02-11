<?php

class Monext_Payline_Block_Adminhtml_System_Config_Prefix extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Monext_Payline_Block_Adminhtml_System_Config_Renderer_Prefix
     */
    protected $prefixRenderer;

    /**
     * @var Monext_Payline_Block_Adminhtml_System_Config_Renderer_Title
     */
    protected $titleRenderer;

    /**
     * Retrieve prefix renderer
     *
     * @return Monext_Payline_Block_Adminhtml_System_Config_Renderer_Prefix
     */
    protected function _getPrefixRenderer()
    {
        if (!$this->prefixRenderer) {
            $this->prefixRenderer = $this->getLayout()->createBlock(
                'payline/adminhtml_system_config_renderer_prefix', '',
                array('is_render_to_js_template' => true)
            );
            $this->prefixRenderer->setClass('prefix');
        }
        return $this->prefixRenderer;
    }

    /**
     * Retrieve title renderer
     *
     * @return Monext_Payline_Block_Adminhtml_System_Config_Renderer_Title
     */
    protected function _getTitleRenderer()
    {
        if (!$this->titleRenderer) {
            $this->titleRenderer = $this->getLayout()->createBlock(
                'payline/adminhtml_system_config_renderer_title', '',
                array('is_render_to_js_template' => true)
            );
            $this->titleRenderer->setClass('title');
        }
        return $this->titleRenderer;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('customer_prefix', array(
            'label' => Mage::helper('payline')->__('Prefix'),
            'renderer' => $this->_getPrefixRenderer()
        ));

        $this->addColumn('customer_title', array(
            'label' => Mage::helper('payline')->__('Title'),
            'renderer' => $this->_getTitleRenderer()
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('payline')->__('Add Configuration');
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getPrefixRenderer()->calcOptionHash($row->getData('customer_prefix')),
            'selected="selected"'
        );

        $row->setData(
            'option_extra_attr_' . $this->_getTitleRenderer()->calcOptionHash($row->getData('customer_title')),
            'selected="selected"'
        );
    }
}