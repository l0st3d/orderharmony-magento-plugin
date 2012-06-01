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
		$items = $order->getAllVisibleItems();
		$ids=array();
		$qty=array();
		foreach ($items as $itemId => $item)
		{
			$qty[]=$item->getQtyToInvoice();
			//$ids[]=$item->getProductId();
			$ids[] = 'P-004'; //test product id
		}
		$productDetails = array_combine($ids, $qty);
		$_orderLine = "";
		foreach ($productDetails as $key => $value)
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
   		
   		
		
		//compare ordered products to oh product list 
		$products_url = $this->OrderHarmonyAuthentication('/api/1/products/list?');
		$client = new Zend_Http_Client($products_url); //connect to order harmony
			$client->setConfig(array('keepalive' => 1));
		$productExists = array(); //create array to record if product already exists or not
		foreach($items as $itemId) //go through ordered products to check if exists, record result in array
			{
			$client->setParameterGet(array('id' => $itemId)); //attach order details to request
			$response = $client->request(); //send post request
			$id = $itemId;
			if ($response->isSuccessful())
				$productExists[] = array($itemId, 1);
			else
				$productExists[] = array($itemId, 0);
			}
		
				
		//check if ordered products exists
		foreach($productExists as $product)
			foreach($product as $id => $exists)
			{
			if($exists = 1) //if yes - insert order
				{
				$order_url = $this->OrderHarmonyAuthentication('/api/1/orders?');
				$params = array('order' => $orderData); //set the parameter
				$client = new Zend_Http_Client($order_url); //connect to order harmony
					$client->setConfig(array('keepalive' => 1));
					$client->setParameterPost($params); //attach order details to request
					$response = $client->request('POST'); //send post request
				}
			else //if no - create product
				{
				//get magento product details
				//create product in order harmony
				//insert order
				}
			}
		
			
			//$OH_product_list = json_decode($response);

		//create variables to output to log to test post result and response.
		$requestResult = $client->getLastRequest();
		$responseResponse = $client->getLastResponse();
  
       
        //check result by writing to the log
        Mage::log("1.$requestResult 2. $responseResponse END", null, 'logged-orders.log');
    }
    
    function OrderHarmonyAuthentication($path)
   		{
   			//set variables to Order Harmony for authentication
   			$host = Mage::getStoreConfig('orderharmony/orderharmony_group/orderharmony_url');
   			$token = Mage::getStoreConfig('orderharmony/orderharmony_group/orderharmony_token');
   			$serial = time();
   			$secret = Mage::getStoreConfig('orderharmony/orderharmony_group/orderharmony_secret');
   			$signature = $path.'token='.$token.'&serial='.$serial.'&secret='.$secret;
			$signature = sha1($signature);
			$url = $host.$path.'token='.$token.'&serial='.$serial.'&signature='.$signature;
			return $url;
		}
}