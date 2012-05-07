<?php
/**
 * Our class name should follow the directory structure of
 * our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores.
 * i.e. app/code/local/OrderHarmony/LogOrder/Model/Observer.php
 */
class OrderHarmony_LogOrder_Model_Observer
{
    /**
     * Magento passes a Varien_Event_Observer object as
     * the first parameter of dispatched events.
     */
    public function logOrder($observer)
    {
        // Retrieve the order being updated from the event observer
        //$order = $observer->getEvent()->getOrder();

        // Write a new line to var/log/product-updates.log
        //$var = $order->getVar();
        Mage::log("An order has been made", null, 'logged-orders.log');
    }
}