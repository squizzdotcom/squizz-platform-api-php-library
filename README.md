


![alt tag](https://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png)

# SQUIZZ.com Platform API PHP Library

The [SQUIZZ.com](https://www.squizz.com) Platform API PHP Library can be used by PHP applications to access the SQUIZZ.com platform's Application Programming Interface (API), allowing data to be pushed and pulled from the API's endpoints in a clean and elegant way. The kinds of data pushed and pulled from the API using the library can include organisational data such as products, sales orders, purchase orders, customer accounts, supplier accounts, notifications, and other data that the platform supports.

This library removes the need for PHP software developers to write boilerplate code for connecting and accessing the platform's API, allowing PHP software using the platform's API to be writen faster and simpler. The library provides classes and objects that can be directly referenced within a PHP application, making it easy to manipulate data retreived from the platform, or create and send data to platform.

If you are a software developer writing a PHP application then we recommend that you use this library instead of directly calling the platform's APIs, since it will simplify your development times and allow you to easily incorporate new functionality from the API by simply updating this library.

- You can find more information about the SQUIZZ.com platform by visiting [https://www.squizz.com/docs/squizz](https://www.squizz.com/docs/squizz)
- To find more information about developing software for the SQUIZZ.com visit [https://www.squizz.com/docs/squizz/Integrate-Software-Into-SQUIZZ.com-Platform.html](https://www.squizz.com/docs/squizz/Integrate-Software-Into-SQUIZZ.com-Platform.html)
- To find more information about the platform's API visit [https://www.squizz.com/docs/squizz/Platform-API.html](https://www.squizz.com/docs/squizz/Platform-API.html)

## Contents

  * [Getting Started](#getting-started)
    * [Dependencies](#dependencies)
    * [Setup](#setup)
  * [Example Usages](#example-usages)
    * [Create Organisation API Session Endpoint](#create-organisation-api-session-endpoint)
    * [Retrieve Organisation Data Endpoint](#retrieve-organisation-data-endpoint)
    * [Send and Procure Purchase Order From Supplier Endpoint](#send-and-procure-purchase-order-from-supplier-endpoint)
    * [Create Organisation Notification Endpoint](#create-organisation-notification-endpoint)
    * [Import Organisation Data Endpoint](#import-organisation-data-endpoint)
    * [Validate Organisation API Session Endpoint](#validate-organisation-api-session-endpoint)
    * [Validate/Create Organisation API Session Endpoint](#validatecreate-organisation-api-session-endpoint)
    * [Destroy Organisation API Session Endpoint](#destroy-organisation-api-session-endpoint)
    * [Validate Organisation Security Certificate API Session Endpoint](#validate-organisation-security-certificate-api-session-endpoint)
    
## Getting Started

### Dependencies

To get started using the library within PHP applications it relies on a number of dependencies being installed on your machine running your PHP application:
 * [CURL](https://curl.haxx.se) needs to be installed on the operating system that is running your PHP code. CURL is used by this API library to make HTTP requests to the platform's API. For Debian linux based operating systems CURL can be downloaded using its package manager. To install CURL for PHP5 run the command: apt-get install php5-curl . For later versions of PHP CURL can be installed with the command: apt-get install php-curl
 * The [Ecommerce Standards Documents PHP Library](https://github.com/squizzdotcom/ecommerce-standards-documents-php-library) needs to be placed into a location on the operating system that is running your PHP code. This API library uses the Ecommerce Standards Documents to serialize and deserialize Ecommerce data coming from the SQUIZZ.com platform's API and store the data in PHP class objects.
 * Clone or download the PHP API library and its dependent libraries from the [Release page](https://github.com/squizzdotcom/squizz-platform-api-php-library/releases) and add references to the PHP libraries in your application using your most preferred way.
 * This PHP library depends on the JsonSerializable class made available in PHP versions 5.4 and later. If you are running an earlier version of PHP you may need to add the JsonSerializable class to your autoloader, such as one found at [php5.3-compatibility](https://github.com/packfire/php5.3-compatibility)
 
### Setup

Once CURL, the Ecommerce Standards Document PHP library, and this library have been placed onto the computer running your PHP code, to use this API library add references to it in your autoloader class, or any other way you load php classes included in your PHP application. Ensure that both the Ecommerce Standards Document classes and this API's library php classes can be loaded in your application. See examples below on how this could be done, and how to start using this library to call the SQUIZZ.com API's various endpoints to pass, or receive data.

## Example Usages
### Create Organisation API Session Endpoint
To start using the SQUIZZ.com platform's API a session must first be created. A session can only be created after credentials for a specified organisation have been given to the API and have been verified.
Once the session has been created then all other endpoints in the API can be called.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section840](https://www.squizz.com/docs/squizz/Platform-API.html#section840) for more documentation about the endpoint.

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "squizz\\api\\v1";
		$esdNamespace = "EcommerceStandardsDocuments";
		$esdInstallPath = "/path/to/esd-php-library/src/";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = $esdInstallPath . $fileName;
		}
		
		require $fileName;
	});
	
	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;

	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$sessionTimeoutMilliseconds = 20000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
		//session has been created so now can call other API endpoints
		$result = "SUCCESS";
		$resultMessage = "API session has successfully been created.";
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//next steps
	//call API endpoints...
	//destroy API session when done...
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

### Retrieve Organisation Data Endpoint
The SQUIZZ.com platform's API has an endpoint that allows a variety of different types of data to be retrieved from another organisation stored on the platform.
The organisational data that can be retrieved includes products, product stock quantities, and product pricing.
The data retrieved can be used to allow an organisation to set additional information about products being bought or sold, as well as being used in many other ways.
Each kind of data retrieved from endpoint is formatted as JSON data conforming to the "Ecommerce Standards Document" standards, with each document containing an array of zero or more records. Use the Ecommerce Standards library to easily read through these documents and records, to find data natively using PHP classes.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section969](https://www.squizz.com/docs/squizz/Platform-API.html#section969) for more documentation about the endpoint and its requirements.
See the example below on how the call the Retrieve Organisation ESD Data endpoint. Note that a session must first be created in the API before calling the endpoint.

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "squizz\\api\\v1";
		$esdNamespace = "EcommerceStandardsDocuments";
		$esdInstallPath = "/path/to/esd-php-library/src/";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = $esdInstallPath . $fileName;
		}
		
		require $fileName;
	});

	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\endpoint\APIv1EndpointResponseESD;
	use squizz\api\v1\endpoint\APIv1EndpointOrgRetrieveESDocument;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	use EcommerceStandardsDocuments\ESDRecordOrderPurchase;
	use EcommerceStandardsDocuments\ESDRecordOrderPurchaseLine;
	use EcommerceStandardsDocuments\ESDocumentConstants;
	use EcommerceStandardsDocuments\ESDocumentProduct;

	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$supplierOrgID = $_GET["supplierOrgID"];
	$retrieveTypeID = APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRICING;
	$sessionTimeoutMilliseconds = 60000;

	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";

	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);

	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();

	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}

	//sand and procure purchsae order if the API was successfully created
	if($apiOrgSession->sessionExists())
	{
		//after 120 seconds give up on waiting for a response from the API
		$timeoutMilliseconds = 120000;
		
		//call the platform's API to import in the organisation's data, which for this example is product pricing
		$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, $retrieveTypeID, $supplierOrgID, "");
		
		$esDocument = $endpointResponseESD->esDocument;

		//check that the data successfully imported
		if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
			
			$result = "SUCCESS";
			$resultMessage = "Organisation data successfully obtained from the platform.<br/>Price records returned:".$esDocument->totalDataRecords;
			
			switch($retrieveTypeID){
				case APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRODUCTS:
					
					//check that records have been placed into the standards document
					if($esDocument->dataRecords != null){
						$resultMessage = $resultMessage."<br/>Product Records:".
							'<table style="width: 100%;">'.
								"<tr>".
									"<th>#</th>".
									"<th>Key Product ID</th>".
									"<th>Product Code</th>".
									"<th>Barcode</th>".
									"<th>Name</th>".
									"<th>Key Taxcode ID</th>".
									"<th>Stock Available</th>".
								"</tr>";
						
						//iterate through each product record stored within the standards document
						$recordNumber=1;
						
						foreach ($esDocument->dataRecords as $productRecord)
						{    
							//output details of the price record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".$productRecord->keyProductID."</td>".
								"<td>".$productRecord->productCode."</td>".
								"<td>".$productRecord->barcode."</td>".
								"<td>".$productRecord->name."</td>".
								"<td>".$productRecord->keyTaxcodeID."</td>".
								"<td>".$productRecord->stockQuantity."</td>".
							"</tr>";
							
							$recordNumber++;
						}
					}
					
					break;
				case APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRICING:
					//check that records have been placed into the standards document
					if($esDocument->dataRecords != null){
						$resultMessage = $resultMessage."<br/>Price Records:".
							'<table style="width: 100%;">'.
								"<tr>".
									"<th>#</th>".
									"<th>Key Product ID</th>".
									"<th>Key Sell Unit ID</th>".
									"<th>Quantity</th>".
									"<th>Tax Rate</th>".
									"<th>Price</th>".
								"</tr>";
						
						//iterate through each price record stored within the standards document
						$recordNumber=1;
						
						foreach ($esDocument->dataRecords as $priceRecord)
						{    
							//output details of the price record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".$priceRecord->keyProductID."</td>".
								"<td>".$priceRecord->keySellUnitID."</td>".
								"<td>".$priceRecord->quantity."</td>".
								"<td>".$priceRecord->taxRate."</td>".
								"<td>".money_format ('%.2n', $priceRecord->price)."</td>".
							"</tr>";
							
							$recordNumber++;
						}
					}
					
					break;
				case APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRODUCT_STOCK:
					//check that records have been placed into the standards document
					if($esDocument->dataRecords != null){
						$resultMessage = $resultMessage."<br/>Price Records:".
							'<table style="width: 100%;">'.
								"<tr>".
									"<th>#</th>".
									"<th>Key Product ID</th>".
									"<th>Quantity</th>".
								"</tr>";
						
						//iterate through each stock record stored within the standards document
						$recordNumber=1;
						
						foreach ($esDocument->dataRecords as $stockRecord)
						{    
							//output details of the price record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".$stockRecord->keyProductID."</td>".
								"<td>".$stockRecord->qtyAvailable."</td>".
							"</tr>";
							
							$recordNumber++;
						}
					}
					
					break;
				default:
					$callEndpoint = false;
					$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
					$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INCORRECT_DATA_TYPE;
					break;
			}
		}else{
			$result = "FAIL";
			$resultMessage = "Organisation data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
		}
	}

	//next steps
	//call other API endpoints...
	//destroy API session when done...
	$apiOrgSession->destroyOrgSession();

	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

### Send and Procure Purchase Order From Supplier Endpoint

The SQUIZZ.com platform's API has an endpoint that allows an orgnisation to import a purchase order. and have it procured/converted into a sales order of a designated supplier organisation. 
This endpoint allows a customer organisation to commit to buy goods and services of an organisation, and have the order processed, and delivered by the supplier organisation.
The endpoint relies upon a connection first being made between organisations within the SQUIZZ.com platform.
The endpoint relies upon being able to find matching supplier products as what has been ordered.
The endpoint has a number of other requirements. See the endpoint documentation for more details on these requirements.

Each purchase order needs to be imported within a "Ecommerce Standards Document" that contains a record for each purchase order. Use the Ecommerce Standards library to easily create these documents and records.
It is recommended to only import one purchase order at a time, since if an array of purchase orders is imported and one order failed to be procured, then no other orders in the list will be attempted to import.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section961](https://www.squizz.com/docs/squizz/Platform-API.html#section961) for more documentation about the endpoint and its requirements.
See the example below on how the call the Send and Procure Purchase order From Supplier endpoint. Note that a session must first be created in the API before calling the endpoint.

![alt tag](https://attach.squizz.com/doc_centre/1/files/images/masters/SQUIZZ-Customer-Purchase-Order-Procurement-Supplier[124].png)

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "squizz\\api\\v1";
		$esdNamespace = "EcommerceStandardsDocuments";
		$esdInstallPath = "/path/to/esd-php-library/src/";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = $esdInstallPath . $fileName;
		}
		
		require $fileName;
	});
	
	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\endpoint\APIv1EndpointResponseESD;
	use squizz\api\v1\endpoint\APIv1EndpointOrgProcurePurchaseOrderFromSupplier;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	use EcommerceStandardsDocuments\ESDRecordOrderPurchase;
	use EcommerceStandardsDocuments\ESDRecordOrderPurchaseLine;
	use EcommerceStandardsDocuments\ESDocumentConstants;
	use EcommerceStandardsDocuments\ESDocumentOrderSale;
	use EcommerceStandardsDocuments\ESDocumentOrderPurchase;
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$supplierOrgID = $_GET["supplierOrgID"];
	$sessionTimeoutMilliseconds = 60000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//sand and procure purchsae order if the API was successfully created
	if($apiOrgSession->sessionExists())
	{
		//create purchase order record to import
		$purchaseOrderRecord = new ESDRecordOrderPurchase();
		
		//set data within the purchase order
		$purchaseOrderRecord->keyPurchaseOrderID = "111";
		$purchaseOrderRecord->purchaseOrderCode = "POEXAMPLE-345";
		$purchaseOrderRecord->purchaseOrderNumber = "345";
		$purchaseOrderRecord->purchaseOrderNumber = "345";
		$purchaseOrderRecord->instructions = "Leave goods at the back entrance";
		$purchaseOrderRecord->keySupplierAccountID = "2";
		$purchaseOrderRecord->supplierAccountCode = "ACM-002";
		
		//set delivery address that ordered goods will be delivered to
		$purchaseOrderRecord->deliveryAddress1 = "32";
		$purchaseOrderRecord->deliveryAddress2 = "Main Street";
		$purchaseOrderRecord->deliveryAddress3 = "Melbourne";
		$purchaseOrderRecord->deliveryRegionName = "Victoria";
		$purchaseOrderRecord->deliveryCountryName = "Australia";
		$purchaseOrderRecord->deliveryPostcode = "3000";
		$purchaseOrderRecord->deliveryOrgName = "Acme Industries";
		$purchaseOrderRecord->deliveryContact = "Jane Doe";
		
		//set billing address that the order will be billed to for payment
		$purchaseOrderRecord->billingAddress1 = "43";
		$purchaseOrderRecord->billingAddress2 = " High Street";
		$purchaseOrderRecord->billingAddress3 = "Melbourne";
		$purchaseOrderRecord->billingRegionName = "Victoria";
		$purchaseOrderRecord->billingCountryName = "Australia";
		$purchaseOrderRecord->billingPostcode = "3000";
		$purchaseOrderRecord->billingOrgName = "Acme Industries International";
		$purchaseOrderRecord->billingContact = "John Citizen";
		
		//create an array of purchase order lines
		$orderLines = array();
		
		//create purchase order line record
		$orderProduct = new ESDRecordOrderPurchaseLine();
		$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_PRODUCT;
		$orderProduct->productCode = "TEA-TOWEL-GREEN";
		$orderProduct->productName = "Green tea towel - 30 x 6 centimetres";
		$orderProduct->keySellUnitID = "2";
		$orderProduct->unitName = "EACH";
		$orderProduct->quantity = 4;
		$orderProduct->sellUnitBaseQuantity = 4;
		$orderProduct->salesOrderLineCode = "ACME-TTGREEN"; 
		$orderProduct->priceExTax = 5.00;
		$orderProduct->priceIncTax = 5.50;
		$orderProduct->priceTax = 0.50;
		$orderProduct->priceTotalIncTax = 22.00;
		$orderProduct->priceTotalExTax = 20.00;
		$orderProduct->priceTotalTax = 2.00;
		
		//add order line to lines list
		array_push($orderLines, $orderProduct);
		
		//add order lines to the order
		$purchaseOrderRecord->lines = $orderLines;
	
		//create purchase order records list and add purchase order to it
		$purchaseOrderRecords = array();
		array_push($purchaseOrderRecords, $purchaseOrderRecord);
		
		//after 60 seconds give up on waiting for a response from the API when creating the notification
		$timeoutMilliseconds = 60000;
		
		//create purchase order Ecommerce Standards document and add purchse order records to the document
		$orderPurchaseESD = new ESDocumentOrderPurchase(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $purchaseOrderRecords, array());

		//send purchase order document to the API for procurement by the supplier organisation
		$endpointResponseESD = APIv1EndpointOrgProcurePurchaseOrderFromSupplier::call($apiOrgSession, $timeoutMilliseconds, $supplierOrgID, "", $orderPurchaseESD);
		//$esDocumentOrderSale = (ESDocumentOrderSale) $endpointResponseESD->esDocument;
		$esDocumentOrderSale = $endpointResponseESD->esDocument;
		
		//check the result of procuring the purchase orders
		if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
			$result = "SUCCESS";
			$resultMessage = "Organisation purchase orders have successfully been sent to supplier organisation.";
			
			//iterate through each of the returned sales orders and output the details of the sales orders
			if($esDocumentOrderSale->dataRecords != null){
				foreach($esDocumentOrderSale->dataRecords as &$salesOrderRecord)
				{								
					$resultMessage = $resultMessage . "<br/><br/>Sales Order Returned, Order Details: <br/>";
					$resultMessage = $resultMessage . "Sales Order Code: " . $salesOrderRecord->salesOrderCode . "<br/>";
					$resultMessage = $resultMessage . "Sales Order Total Cost: " . $salesOrderRecord->totalPriceIncTax . " (" . $salesOrderRecord->currencyISOCode . ")" . "<br/>";
					$resultMessage = $resultMessage . "Sales Order Total Taxes: " . $salesOrderRecord->totalTax . " (" . $salesOrderRecord->currencyISOCode . ")" . "<br/>";
					$resultMessage = $resultMessage . "Sales Order Customer Account: " . $salesOrderRecord->customerAccountCode . "<br/>";
					$resultMessage = $resultMessage . "Sales Order Total Lines: " . $salesOrderRecord->totalLines;
				}
			}
		}else{
			$result = "FAIL";
			$resultMessage = "Organisation purchase orders failed to be processed. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
			
			//if one or more products in the purchase order could not match a product for the supplier organisation then find out the order lines caused the problem
			if($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_PRODUCT_NOT_MAPPED && $esDocumentOrderSale != null)
			{
				//get a list of order lines that could not be mapped
				$unmappedLines = APIv1EndpointOrgProcurePurchaseOrderFromSupplier::getUnmappedOrderLines($esDocumentOrderSale);
				
				//iterate through each unmapped order line
				foreach($unmappedLines as $orderIndex => $lineIndex)
				{								
					//check that the order can be found that contains the problematic line
					if($orderIndex < count($orderPurchaseESD->dataRecords) && $lineIndex < count($orderPurchaseESD->dataRecords[$orderIndex]->lines)){
						$resultMessage = $resultMessage . "<br/>For purchase order: " . $orderPurchaseESD->dataRecords[$orderIndex]->purchaseOrderCode . " a matching supplier product for line number: " . ($lineIndex+1) . " could not be found.";
					}
				}
			}
			//if one or more products in the purchase order could not be priced by the supplier organisation then find the order line that caused the problem
			elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_MAPPED_PRODUCT_PRICE_NOT_FOUND && $esDocumentOrderSale != null)
			{
				if(array_key_exists(APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNPRICED_LINES, $esDocumentOrderSale->configs))
				{
					//get a list of order lines that could not be priced
					$unpricedLines = APIv1EndpointOrgProcurePurchaseOrderFromSupplier::getUnpricedOrderLines($esDocumentOrderSale);

					//iterate through each unpriced order line
					foreach($unpricedLines as $orderIndex => $lineIndex)
					{
						//check that the order can be found that contains the problematic line
						if($orderIndex < count($orderPurchaseESD->dataRecords) && $lineIndex < count($orderPurchaseESD->dataRecords[$orderIndex]->lines)){
							$resultMessage = $resultMessage . "For purchase order: " . $orderPurchaseESD->dataRecords[$orderIndex]->purchaseOrderCode . " the supplier has not set pricing for line number: " . ($lineIndex+1);
						}
					}
				}
			}
		}
	}
	
	//next steps
	//call other API endpoints...
	//destroy API session when done...
	$apiOrgSession->destroyOrgSession();
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

### Create Organisation Notification Endpoint
The SQUIZZ.com platform's API has an endpoint that allows organisation notifications to be created in the platform. allowing people assigned to an organisation's notification category to receive a notification. 
This can be used to advise such people of events happening external to the platform, such as sales, enquires, tasks completed through websites and other software.
See the example below on how the call the Create Organisation Notification endpoint. Note that a session must first be created in the API before calling the endpoint.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section854](https://www.squizz.com/docs/squizz/Platform-API.html#section854) for more documentation about the endpoint.


```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "squizz\\api\\v1";
		$esdNamespace = "EcommerceStandardsDocuments";
		$esdInstallPath = "/path/to/esd-php-library/src/";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = $esdInstallPath . $fileName;
		}
		
		require $fileName;
	});
	
	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\endpoint\APIv1EndpointResponseESD;
	use squizz\api\v1\endpoint\APIv1EndpointOrgCreateNotification;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	use EcommerceStandardsDocuments\ESDocumentConstants;
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$supplierOrgID = $_GET["supplierOrgID"];
	$sessionTimeoutMilliseconds = 60000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if(!$endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//sand and procure purchsae order if the API was successfully created
	if($apiOrgSession->sessionExists())
	{
		//set the notification category that the organisation will display under in the platform, in this case the sales order category
		$notifyCategory = APIv1EndpointOrgCreateNotification::NOTIFY_CATEGORY_ORDER_SALE;
		
		//after 20 seconds give up on waiting for a response from the API when creating the notification
		$timeoutMilliseconds = 20000;
		
		//set the message that will appear in the notification, note the placeholders {1} and {2} that will be replaced with data values
		$message = "A new {1} was created in {2} Website";
		
		//set labels and links to place within the placeholders of the message
		$linkLabels = array("Sales Order","Acme Industries");
		$linkURLs = array("","http://www.example.com/acmeindustries");
		
		//call the platform's API to create the organistion notification and have people assigned to organisation's notification category receive it
		$endpointResponseESD = APIv1EndpointOrgCreateNotification::call($apiOrgSession, $timeoutMilliseconds, $notifyCategory, $message, $linkURLs, $linkLabels);
		
		//check the result of procuring the purchase orders
		if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
			$result = "SUCCESS";
			$resultMessage = "Organisation notification successfully created in the platform.";
		}else{
			$result = "FAIL";
			$resultMessage = "Organisation notification failed to be created. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code;
		}
	}
	
	//next steps
	//call other API endpoints...
	//destroy API session when done...
	$apiOrgSession->destroyOrgSession();
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

## Import Organisation Data Endpoint
The SQUIZZ.com platform's API has an endpoint that allows a wide variety of different types of data to be imported into the platform against an organisation. 
This organisational data includes taxcodes, products, customer accounts, supplier accounts. pricing, price levels, locations, and many other kinds of data.
This data is used to allow the organisation to buy and sell products, as well manage customers, suppliers, employees, and other people.
Each type of data needs to be imported as an "Ecommerce Standards Document" that contains one or more records. Use the Ecommerce Standards library to easily create these documents and records.
When importing one type of organisational data, it is important to import the full data set, otherwise the platform will deactivate unimported data.
For example if 3 products are imported, then another products import is run that only imports 2 records, then other 1 product will become deactivated and no longer be able to be sold.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section843](https://www.squizz.com/docs/squizz/Platform-API.html#section843) for more documentation about the endpoint and its requirements.
See the example below on how the call the Import Organisation ESD Data endpoint. Note that a session must first be created in the API before calling the endpoint.

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "squizz\\api\\v1";
		$esdNamespace = "EcommerceStandardsDocuments";
		$esdInstallPath = ""/path/to/esd-php-library/src/";";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = $esdInstallPath . $fileName;
		}
		
		require $fileName;
	});

	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\endpoint\APIv1EndpointResponseESD;
	use squizz\api\v1\endpoint\APIv1EndpointOrgImportESDocument;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	use EcommerceStandardsDocuments\ESDocumentTaxcode;
	use EcommerceStandardsDocuments\ESDRecordTaxcode;
	use EcommerceStandardsDocuments\ESDocumentConstants;

	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$sessionTimeoutMilliseconds = 60000;

	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";

	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);

	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();

	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}

	//sand and procure purchsae order if the API session was successfully created
	if($apiOrgSession->sessionExists())
	{
		//create taxcode records
		$taxcodeRecords = array();
		$taxcodeRecord = new ESDRecordTaxcode();
		$taxcodeRecord->keyTaxcodeID = "1";
		$taxcodeRecord->taxcode = "GST";
		$taxcodeRecord->taxcodeLabel = "GST";
		$taxcodeRecord->description = "Goods And Services Tax";
		$taxcodeRecord->taxcodePercentageRate = 10;
		array_push($taxcodeRecords, $taxcodeRecord);
		
		$taxcodeRecord = new ESDRecordTaxcode();
		$taxcodeRecord->keyTaxcodeID = "2";
		$taxcodeRecord->taxcode = "FREE";
		$taxcodeRecord->taxcodeLabel = "Tax Free";
		$taxcodeRecord->description = "Free from Any Taxes";
		$taxcodeRecord->taxcodePercentageRate = 0;
		array_push($taxcodeRecords, $taxcodeRecord);
		
		$taxcodeRecord = new ESDRecordTaxcode();
		$taxcodeRecord->keyTaxcodeID = "3";
		$taxcodeRecord->taxcode = "NZGST";
		$taxcodeRecord->taxcodeLabel = "New Zealand GST Tax";
		$taxcodeRecord->description = "New Zealand Goods and Services Tax";
		$taxcodeRecord->taxcodePercentageRate = 15;
		array_push($taxcodeRecords, $taxcodeRecord);
		
		//after 60 seconds give up on waiting for a response from the API when importing the organisation data
		$timeoutMilliseconds = 60000;
		
		//add a dataFields attribute that contains a comma delimited list of taxcode record fields that the API is allowed to insert or update in the platform
		$configs = array();
		$configs['dataFields'] = 'keyTaxcodeID,taxcode,taxcodeLabel,description,taxcodePercentageRate';
		
		//create taxcode Ecommerce Standards document and add taxcode records to the document
		$taxcodeESD = new ESDocumentTaxcode(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $taxcodeRecords, $configs);

		//send the taxcode document to the API to be imported against the organisation logged into the API
		$endpointResponseESD = APIv1EndpointOrgImportESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgImportESDocument::IMPORT_TYPE_ID_TAXCODES, $taxcodeESD);
		
		//check the result of importing the organisation data
		if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
			$result = "SUCCESS";
			$resultMessage = "Organisation data successfully imported into the platform.";
		}else{
			$result = "FAIL";
			$resultMessage = "Organisation data failed to be imported into the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
		}
	}

	//next steps
	//call other API endpoints...
	//destroy API session when done...
	$apiOrgSession->destroyOrgSession();

	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

### Validate Organisation API Session Endpoint

After a session has been created with SQUIZZ.com platform's API, if the same session is persistently being used over a long period time, then its worth validating that the session has not been destroyed by the API.
The SQUIZZ.com platform's API will automatically expire and destory sessions that have existed for a long period of time.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section842](https://www.squizz.com/docs/squizz/Platform-API.html#section842) for more documentation about the endpoint.

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "squizz\\api\\v1";
		$esdNamespace = "EcommerceStandardsDocuments";
		$esdInstallPath = "/path/to/esd-php-library/src/";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = $esdInstallPath . $fileName;
		}
		
		require $fileName;
	});
	
	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$sessionTimeoutMilliseconds = 20000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//validate the session in the platform's API
	$endpointResponse = $apiOrgSession->validateOrgSession();
	
	//check the result of validating the session
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
		$result = "SUCCESS";
		$resultMessage = "API session has successfully been validated.";
	}else{
		//session failed to be validated
		$resultMessage = "API session failed to be validated. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//next steps
	//call other API endpoints...
	//destroy api session when done
	$apiOrgSession->destroyOrgSession();
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

### Validate/Create Organisation API Session Endpoint

After a session has been created with SQUIZZ.com platform's API, if the same session is persistently being used over a long period time, then a helper method in the library can be used to check if the API session is still valid, then if not have a new session be created.
The SQUIZZ.com platform's API will automatically expire and destory sessions that have existed for a long period of time.

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "squizz\\api\\v1";
		$esdNamespace = "EcommerceStandardsDocuments";
		$esdInstallPath = "/path/to/esd-php-library/src/";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = $esdInstallPath . $fileName;
		}
		
		require $fileName;
	});
	
	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$sessionTimeoutMilliseconds = 20000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//check if the session still is valid, if not have a new session created with the same organisation API credentials
	$endpointResponse = $apiOrgSession->validateCreateOrgSession();
	
	//check the result of validating or creating a new session
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
		$result = "SUCCESS";
		$resultMessage = "API session has successfully been validated.";
	}else{
		//session failed to be validated
		$resultMessage = "API session failed to be validated. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//next steps
	//call other API endpoints...
	//destroy API session when done...
	$apiOrgSession->destroyOrgSession();
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

### Destroy Organisation API Session Endpoint

After a session has been created with SQUIZZ.com platform's API, if after calling other endpoints there no need for the session anymore, then it's advisable to destroy the session as soon as possible.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section841](https://www.squizz.com/docs/squizz/Platform-API.html#section841) for more documentation about the endpoint.

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "squizz\\api\\v1";
		$esdNamespace = "EcommerceStandardsDocuments";
		$esdInstallPath = "/path/to/esd-php-library/src/";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = $esdInstallPath . $fileName;
		}
		
		require $fileName;
	});
	
	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$sessionTimeoutMilliseconds = 20000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//next steps
	//call API endpoints...
	
	//destroy the session in the platform's API
	$endpointResponse = $apiOrgSession->destroyOrgSession();
	
	//check the result of destroying the session
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
		$result = "SUCCESS";
		$resultMessage = "API session has successfully been destroyed.";
	}else{
		//session failed to be created
		$resultMessage = "API session failed to be destroyed. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

### Validate Organisation Security Certificate API Session Endpoint

The SQUIZZ.com platform's API has an endpoint that allows a TLS security certificate created for an organisation in the platform to be validated. 
Before an organisation can download and use a security certificate the certificate must first be validated by a HTTP request calling this API endpoint. 
The endpoint will check that the originating HTTP request's IP address matches the common name set for the certificate, or that a reverse DNS lookup matches the domain set in the certificate with the originating IP address of the endpoint request.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section842](https://www.squizz.com/docs/squizz/Platform-API.html#section843) for more documentation about the endpoint and its requirements.
See the example below on how the call the Validate Organisation Security Certificate endpoint. Note that a session must first be created in the API before calling the endpoint.

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "squizz\\api\\v1";
		$esdNamespace = "EcommerceStandardsDocuments";
		$esdInstallPath = "/path/to/esd-php-library/src/";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = $esdInstallPath . $fileName;
		}
		
		require $fileName;
	});
	
	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\endpoint\APIv1EndpointOrgValidateSecurityCertificate;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$orgSecurityCertificateID = $_GET["orgSecurityCertificateID"];
	$sessionTimeoutMilliseconds = 20000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//if a session was successfully created then call API to validate a certificate with a given ID
	if($apiOrgSession->sessionExists())
	{
		//give up on waiting for a response from the API after 30 seconds
		$timeoutMilliseconds = 30000;
	
		//call endpoint
		$endpointResponseESD = APIv1EndpointOrgValidateSecurityCertificate::call($apiOrgSession, $timeoutMilliseconds, $orgSecurityCertificateID);
		
		//check the result of validating the security certificate
		if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
			$result = "SUCCESS";
			$resultMessage = "Organisation security certificate has successfully been validated and activated.";
		}else{
			$result = "FAIL";
			$resultMessage = "Organisation security certificate failed to validate. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
		}
	}
	
	//next steps
	//call other API endpoints...
	//destroy api session when done
	$apiOrgSession->destroyOrgSession();
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```