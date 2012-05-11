<?php
class OrderHarmony_LogOrder_Model_Observer
{
    public function logOrder($observer)
    {
		//CUSTOMER DETAILS
		$_customerId = Mage::getSingleton('customer/session')->getCustomerId();
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$_name = $customer->getName();		
		$_email = $customer->getEmail();
		
		//ORDER DETAILS
		$order = $observer->getEvent()->getOrder(); 
		
		//get details of the items ordered		
		$items = $order->getAllVisibleItems();
		$ids=array();
		$qty=array();
		foreach ($items as $itemId => $item)
		{
			$qty[]=$item->getQtyToInvoice();
			$ids[]=$item->getProductId();
		}
		$productDetails = array_combine($ids, $qty);
		$showProducts = "";
		foreach ($productDetails as $key => $value)
			$showProducts .= "$key/$value, ";
		$showProducts = rtrim($showProducts, ', ');
		
		//INVOICE ADDRESS
		$billingAddress = $order->getBillingAddress();
		$_billingStreet = $billingAddress->getStreetFull();
		$_billingCity = $billingAddress->getCity();
		$_billingCounty = $billingAddress->getRegion_id();
		$_billingPostcode = $billingAddress->getPostcode();
		
		//DELIVERY ADDRESS
		$shippingAddress = $order->getShippingAddress();
		$_shippingStreet = $shippingAddress->getStreetFull();
		$_shippingCity = $shippingAddress->getCity();
		$_shippingCounty = $shippingAddress->getRegion_id();
		$_shippingPostcode = $shippingAddress->getPostcode();
        
        Mage::log("Name: $_name, Email: $_email, CustomerId: $_customerId, billingAddress: $_billingStreet, $_billingCity, $_billingCounty, $_billingPostcode, shippingAddress: $_shippingStreet, $_shippingCity, $_shippingCounty, $_shippingPostcode  product: $showProducts", null, 'logged-orders.log');
    }
}