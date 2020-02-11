<?php

Mage::app()->getConfig()->saveConfig('payment/PaylineCPT/automate_invoice_creation', 'processing');

$installer = $this;

// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

// Insert statuses
$installer->getConnection()->insertArray(
    $statusTable,
    array(
        'status',
        'label'
    ),
    array(
        array('status' => Monext_Payline_Helper_Oney::STATUS_PENDING_ONEY, 'label' => 'Awaiting acceptance by Oney'),
    )
);

// Insert states and mapping of statuses to states
$installer->getConnection()->insertArray(
    $statusStateTable,
    array(
        'status',
        'state',
        'is_default'
    ),
    array(
        array(
            'status' => Monext_Payline_Helper_Oney::STATUS_PENDING_ONEY,
            'state' => Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            'is_default' => 0
        ),
    )
);

