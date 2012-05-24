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
			//$ids[]=$item->getProductId();
			
			//test product id
			$ids = 'P-004';
		}
		$productDetails = array_combine($ids, $qty);
		$_orderLine = "";
		foreach ($productDetails as $key => $value)
			//$_products .= "$key/$value, ";
			$_orderLine .= '{"order-line": {"product-variant":{"product-code":"'.$key.'"}, "quantity":'.$value.'}},';
		$_orderLine = rtrim($_orderLine, ', ');
		
		//INVOICE ADDRESS
		$billingAddress = $order->getBillingAddress();
		$_billingStreet1 = $billingAddress->getStreet1();
		$_billingStreet2 = $billingAddress->getStreet2();
		$_billingCity = $billingAddress->getCity();
		$_billingCounty = $billingAddress->getRegion();
		//$_billingCountry = $billingAddress->getCountryModel()->getName();		
		$_billingPostcode = $billingAddress->getPostcode();
		
		//DELIVERY ADDRESS
		$shippingAddress = $order->getShippingAddress();
		$_shippingStreet1 = $shippingAddress->getStreet1();
		$_shippingStreet2 = $shippingAddress->getStreet2();
		$_shippingCity = $shippingAddress->getCity();
		$_shippingCounty = $shippingAddress->getRegion();
		//$_shippingCountry = $shippingAddress->getCountryModel()->getName();
		$_shippingPostcode = $shippingAddress->getPostcode();
        
        //create the order details as a JSON string
		$orderData = '{"order":{"customer":{"name":"'.$_name.'", "email":"'.$_email.'"}, "customer-reference":"'.$_customerId.'", "order-lines":['.$_orderLine.'], "invoice-address":[{"line":"'.$_billingStreet1.'"}, {"line":"'.$_billingStreet2.'"}, {"line":"'.$_billingCity.'"}, {"line":"'.$_billingCounty.'"}, {"postcode":"'.$_billingPostcode.'"}], "delivery-address":[{"line":"'.$_shippingStreet1.'"}, {"line":"'.$_shippingStreet2.'"}, {"line":"'.$_shippingCity.'"}, {"line":"'.$_shippingCounty.'"}, {"postcode":"'.$_shippingPostcode.'"}]}}';
   		
   		//set variables for request to Order Harmony
   		$host = Mage::getStoreConfig('orderharmony/orderharmony_group/orderharmony_url');
   		$path = '/api/1/orders?';
		$token = Mage::getStoreConfig('orderharmony/orderharmony_group/orderharmony_token');
   		$serial = time();
   		$secret = Mage::getStoreConfig('orderharmony/orderharmony_group/orderharmony_secret');
   		
		$signature = $path.'token='.$token.'&serial='.$serial.'&secret='.$secret;
		$signature = sha1($signature);
		$url = $host.$path.'token='.$token.'&serial='.$serial.'&signature='.$signature;
		
		//set the parameter
		$params = array('order' => $orderData);

		$client = new Zend_Http_Client($url);
		$client->setParameterPost($params);
		$response = $client->request('POST');
		
		//create variables to output to log to test post result and response.
		$requestResult = $client->getLastRequest();
		$responseResponse = $client->getLastResponse();
  
       
        //check result by writing to the log
        Mage::log("$requestResult $responseResponse // $orderData", null, 'logged-orders.log');
    }
}