<?php

class Monext_Payline_Model_Contract_Klarnapay
{
    const KLARNA_ALLOWED_COUNTRY = array('UK', 'SE', 'NO', 'NL', 'AT', 'BE', 'DE', 'SZ', 'DK', 'FI');
    const KLARNA_ALLOWED_CURRENCY = array('SEK', 'NOK', 'EUR', 'DKK');

    /**
     * Check if contract is eligible for current quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isEligibleForQuote(Mage_Sales_Model_Quote $quote)
    {
        $store = $quote->getStore();
        $origCountryId = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $store);
        $shippingCountryId = $quote->getShippingAddress()->getCountryId();
        $currency = $quote->getQuoteCurrencyCode();

        /* @see Monext_Payline_Helper_Payment::_init */
        $paylineOrderAmount = round($quote->getBaseGrandTotal() * 100);

        /* @see Monext_Payline_Helper_Payment::getDirectActionHeader */
        $items = $quote->getAllItems();
        $itemsPriceCount = 0;
        foreach ($items as $item) {
            /* we must use the same code than getDirectActionHeader */
            $itemsPriceCount += round($item->getPrice() * $item->getQty() * 100);
        }

        if ($itemsPriceCount == $paylineOrderAmount
        && in_array($shippingCountryId, self::KLARNA_ALLOWED_COUNTRY)
        && $origCountryId == $shippingCountryId
        && in_array($currency, self::KLARNA_ALLOWED_CURRENCY)) {
            return true;
        }

        return false;
    }
}