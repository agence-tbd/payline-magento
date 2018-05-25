<?php
class Monext_Payline_Model_Datasource_Contracts
{
    protected $_options;

    public function toOptionArray()
    {
        if ($this->_options === null) {
            $options = array();

            $contracts = Mage::getModel('payline/contract')->getCollection();
            foreach ($contracts as $contract) {
                $options[] = array(
                    'value' => $contract->getNumber(),
                    'label' => $contract->getName() . ' - ' .$contract->getNumber()
                );
            }
            $this->_options = $options;
        }

        return $this->_options;
    }
}