<?php

class Monext_Payline_Block_Adminhtml_System_Config_Delivery extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Monext_Payline_Block_Adminhtml_System_Config_Renderer_Select
     */
    protected $selectRenderer;

    /**
     * @var Monext_Payline_Block_Adminhtml_System_Config_Renderer_Shippingmethod
     */
    protected $shippingMethodRenderer;

    /**
     * Retrieve select renderer
     *
     * @param null $class
     * @return Monext_Payline_Block_Adminhtml_System_Config_Renderer_Select
     */
    protected function _getSelectRenderer($class = null)
    {
        if (!isset($this->selectRenderer[$class])) {
            $this->selectRenderer[$class] = $this->getLayout()->createBlock(
                'payline/adminhtml_system_config_renderer_select', $class,
                array('is_render_to_js_template' => true)
            );
            $this->selectRenderer[$class]->setClass($class);
            $this->selectRenderer[$class]->setExtraParams('style="width:120px"');
        }
        return $this->selectRenderer[$class];
    }

    /**
     * Retrieve shipping method renderer
     *
     * @return Monext_Payline_Block_Adminhtml_System_Config_Renderer_Select
     */
    protected function _getShippingMethodRenderer()
    {
        if (!$this->shippingMethodRenderer) {
            $this->shippingMethodRenderer = $this->getLayout()->createBlock(
                'payline/adminhtml_system_config_renderer_shippingmethod', '',
                array('is_render_to_js_template' => true)
            );
            $this->shippingMethodRenderer->setClass('shipping_method');
        }
        return $this->shippingMethodRenderer;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('shipping_method', array(
            'label' => Mage::helper('payline')->__('Shipping method'),
            'style' => 'width:50px',
            'renderer' => $this->_getShippingMethodRenderer()
        ));

        $this->addColumn('deliverytime', array(
            'label' => Mage::helper('payline')->__('Delivery time'),
            'style' => 'width:100px',
            'renderer' => $this->_getSelectRenderer('deliverytime')
        ));

        $this->addColumn('deliverymode', array(
            'label' => Mage::helper('payline')->__('Delivery mode'),
            'style' => 'width:100px',
            'renderer' => $this->_getSelectRenderer('deliverymode')
        ));

        $this->addColumn('delivery_expected_delay', array(
            'label' => Mage::helper('payline')->__('Delivery expected delay'),
            'style' => 'width:100px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('payline')->__('Add Configuration');
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getShippingMethodRenderer()->calcOptionHash($row->getData('shipping_method')),
            'selected="selected"'
        );

        $row->setData(
            'option_extra_attr_' . $this->_getSelectRenderer('deliverytime')->calcOptionHash($row->getData('deliverytime')),
            'selected="selected"'
        );

        $row->setData(
            'option_extra_attr_' . $this->_getSelectRenderer('deliverymode')->calcOptionHash($row->getData('deliverymode')),
            'selected="selected"'
        );
    }
}