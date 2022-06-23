


![alt tag](https://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png)

# SQUIZZ.com Platform API PHP Library

The [SQUIZZ.com](https://www.squizz.com) Platform API PHP Library can be used by PHP applications to access the SQUIZZ.com platform's Application Programming Interface (API), allowing data to be pushed and pulled from the API's endpoints in a clean and elegant way. The kinds of data pushed and pulled from the API using the library can include organisational data such as products, sales orders, purchase orders, customer accounts, supplier accounts, notifications, and other data that the platform supports.

This library removes the need for PHP software developers to write boilerplate code for connecting and accessing the platform's API, allowing PHP software using the platform's API to be writen faster and simpler. The library provides classes and objects that can be directly referenced within a PHP application, making it easy to manipulate data retreived from the platform, or create and send data to platform.

If you are a software developer writing a PHP application then we recommend that you use this library instead of directly calling the platform's APIs, since it will simplify your development times and allow you to easily incorporate new functionality from the API by simply updating this library.

- You can find more information about the SQUIZZ.com platform by visiting [https://www.squizz.com/docs/squizz](https://www.squizz.com/docs/squizz)
- To find more information about developing software for the SQUIZZ.com visit [https://www.squizz.com/docs/squizz/Integrate-Software-Into-SQUIZZ.com-Platform.html](https://www.squizz.com/docs/squizz/Integrate-Software-Into-SQUIZZ.com-Platform.html)
- To find more information about the platform's API visit [https://www.squizz.com/docs/squizz/Platform-API.html](https://www.squizz.com/docs/squizz/Platform-API.html)
- Examples on how use this library can be found within the files in the [example](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/) directory or in the sections below.

## Contents

  * [Getting Started](#getting-started)
    * [Dependencies](#dependencies)
    * [Setup](#setup)
  * [Example Usages](#example-usages)
    * [Create Organisation API Session Endpoint](#create-organisation-api-session-endpoint)
    * [Retrieve Organisation Data Endpoint](#retrieve-organisation-data-endpoint)
    * [Search Customer Account Records Endpoint](#search-customer-account-records-endpoint)
    * [Retrieve Customer Account Record Endpoint](#retrieve-customer-account-record-endpoint)
    * [Send and Procure Purchase Order From Supplier Endpoint](#send-and-procure-purchase-order-from-supplier-endpoint)
    * [Send Customer Invoice To Customer Endpoint](#send-customer-invoice-to-customer-endpoint)
	* [Send Delivery Notice to Customer Endpoint](#send-delivery-notice-to-customer-endpoint)
    * [Create Organisation Notification Endpoint](#create-organisation-notification-endpoint)
    * [Import Organisation Data Endpoint](#import-organisation-data-endpoint)
    * [Import Organisation Sales Order Endpoint](#import-organisation-sales-order-endpoint)
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

	//obtain or load in an organisation's API credentials, in this example from URL parameters
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
The organisational data that can be retrieved includes products, product stock quantities, product pricing, product attributes, product images, categories, make/model data and more.
The data retrieved can be used to allow an organisation to set additional information about products being bought or sold, as well as being used in many other ways.
Each kind of data retrieved from endpoint is formatted as JSON data conforming to the "Ecommerce Standards Document" standards, with each document containing an array of zero or more records. Use the Ecommerce Standards library to easily read through these documents and records, to find data natively using PHP classes.
The SQUIZZ.com platform API returns collections of records enclosed in an Ecommerce Standards Document. The number of records returned with each request is limited, and may require multiple requests to build a full record set. See example files on how this is done using pagination request parameters.
Read [https://www.squizz.com/docs/squizz/Platform-API-Endpoint:-Retrieve-Organisation-Data.html](https://www.squizz.com/docs/squizz/Platform-API-Endpoint:-Retrieve-Organisation-Data.html) for more documentation about the endpoint and its requirements.
See the example below on how the call the Retrieve Organisation ESD Data endpoint. Note that a session must first be created in the API before calling the endpoint.

Other examples exist in this repository's examples folder on how to retrieve serveral different types of data. Note that some of these examples show how to different types of data can be retrieved and combined together, showing the interconnected nature of several data types:
 - Retrieve Categories [APIv1ExampleRunnerRetrieveOrgESDDataCategories.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerRetrieveOrgESDDataCategories.php)
 - Retrieve Attributes [APIv1ExampleRunnerRetrieveOrgESDDataAttributes.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerRetrieveOrgESDDataAttributes.php)
 - Retrieve Makers [APIv1ExampleRunnerRetrieveOrgESDDataMakers.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerRetrieveOrgESDDataMakers.php)
 - Retrieve Maker Models [APIv1ExampleRunnerRetrieveOrgESDDataMakerModels.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerRetrieveOrgESDDataMakerModels.php)
 - Retrieve Maker Model Mappings [APIv1ExampleRunnerRetrieveOrgESDDataMakerModelMappings.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerRetrieveOrgESDDataMakerModelMappings.php)
 - Retrieve Products [APIv1ExampleRunnerRetrieveOrgESDDataProducts.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerRetrieveOrgESDDataProducts.php)
 - Retrieve Product Images [APIv1ExampleRunnerRetrieveOrgESDDataProductImages.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerRetrieveOrgESDDataProductImages.php)

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
	$retrieveTypeID = $_GET["retrieveTypeID"];
	$customerAccountCode = $_GET["customerAccountCode"];
	$sessionTimeoutMilliseconds = 60000;
	
	//get the first 5000 records. Change the starting index to get the next page of records if 5000 records is returned
	$recordsMaxAmount = 5000;
	$recordsStartIndex = 0;

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

	//retrieve organisation data if the API was successfully created
	if($apiOrgSession->sessionExists())
	{
		//after 120 seconds give up on waiting for a response from the API
		$timeoutMilliseconds = 120000;
		
		//call the platform's API to retrieve the organisation's data
		$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, $retrieveTypeID, $supplierOrgID, $customerAccountCode, $recordsMaxAmount, $recordsStartIndex);
		
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
									"<th>Quantity Available</th>".
									"<th>Quantity Orderable</th>".
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
								"<td>".$stockRecord->qtyOrderable."</td>".
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

### Search Customer Account Records Endpoint
The SQUIZZ.com platform's API has an endpoint that allows an organisation to search for records within another connected organisation's business sytem, based on records associated to an assigned customer account.
This endpoint allows an organisation to securely search for invoice, sales order, back order, transactions. credit and payment records, retrieved in realtime from a supplier organisation's connected business system.
The records returned from endpoint is formatted as JSON data conforming to the "Ecommerce Standards Document" standards, with each document containing an array of zero or more records. Use the Ecommerce Standards library to easily read through these documents and records, to find data natively using PHP classes.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section1473](https://www.squizz.com/docs/squizz/Platform-API.html#section1473) for more documentation about the endpoint and its requirements.
See the example below on how the call the Search Customer Account Records endpoint. Note that a session must first be created in the API before calling the endpoint.

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
	use squizz\api\v1\endpoint\APIv1EndpointOrgSearchCustomerAccountRecords;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryBackOrder;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryBackOrderLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryCredit;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryCreditLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryInvoice;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryInvoiceLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryOrderSale;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryOrderSaleLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryPayment;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryPaymentLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryQuote;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryQuoteLine;
	use EcommerceStandardsDocuments\ESDocumentConstants;
	use EcommerceStandardsDocuments\ESDocumentCustomerAccountEnquiry;
	
	//obtain or load in an organisation's API credentials, in this example from URL parameters
	
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$supplierOrgID = $_GET["supplierOrgID"];
	$sessionTimeoutMilliseconds = 60000;
	$customerAccountCode = $_GET["customerAccountCode"];
	$recordType = $_GET["recordType"];
	
	switch($recordType)
	{
		case ESDocumentConstants::RECORD_TYPE_ORDER_SALE:
			$recordType = ESDocumentConstants::RECORD_TYPE_ORDER_SALE;
			break;
		case ESDocumentConstants::RECORD_TYPE_BACKORDER:
			$recordType = ESDocumentConstants::RECORD_TYPE_BACKORDER;
			break;
		case ESDocumentConstants::RECORD_TYPE_TRANSACTION:
			$recordType = ESDocumentConstants::RECORD_TYPE_TRANSACTION;
			break;
		case ESDocumentConstants::RECORD_TYPE_CREDIT:
			$recordType = ESDocumentConstants::RECORD_TYPE_CREDIT;
			break;
		case ESDocumentConstants::RECORD_TYPE_PAYMENT:
			$recordType = ESDocumentConstants::RECORD_TYPE_PAYMENT;
			break;
		default:
			$recordType = ESDocumentConstants::RECORD_TYPE_INVOICE;
	}
	
	$searchBeginMonths = $_GET["searchBeginMonths"];
	$searchString = $_GET["searchString"];
	$searchType = $_GET["searchType"];
	$keyRecordIDs = $_GET["keyRecordIDs"];
	$beginDateTime = strtotime(date('Y-m-d', strtotime("-".$searchBeginMonths." months", strtotime(date("Y-m-d")))))* 1000;
	$endDateTime = round(microtime(true) * 1000);
	$pageNumber = 1;
	$recordsMaxAmount = 100;
	$outstandingRecordsOnly = false;
	
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
	
	//search for account records if the API was successfully created
	if($apiOrgSession->sessionExists())
	{
		//after 30 seconds give up on waiting for a response from the API
		$timeoutMilliseconds = 30000;
		
		//call the platform's API to search for customer account records from the supplier organiation's connected business system
		$endpointResponseESD = APIv1EndpointOrgSearchCustomerAccountRecords::call(
			$apiOrgSession,
			$timeoutMilliseconds,
			$recordType,
			$supplierOrgID,
			$customerAccountCode,
			$beginDateTime,
			$endDateTime,
			$pageNumber,
			$recordsMaxAmount,
			$outstandingRecordsOnly,
			$searchString,
			$keyRecordIDs,
			$searchType
		);
		
		$esDocument = $endpointResponseESD->esDocument;

		//check that the data successfully imported
		if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
			
			$result = "SUCCESS";
			$resultMessage = "Account record data successfully obtained from the platform.<br/>Price records returned:".$esDocument->totalDataRecords;
			
			//output records based on the record type
			switch($recordType){
				case ESDocumentConstants::RECORD_TYPE_INVOICE:
					$resultMessage = 
						"SUCCESS - account invoice record data successfully obtained from the platform<br/>".
						"Invoice Records Returned: ".$esDocument->totalDataRecords;

					//check that invoice records have been placed into the standards document
					if ($esDocument->invoiceRecords != null)
					{	
						$resultMessage = $resultMessage."<br/>Invoice Records:".
							'<table style="width: 100%;">'.
								"<tr>".
									"<th>#</th>".
									"<th>Key Invoice ID</th>".
									"<th>Invoice ID</th>".
									"<th>Invoice Number</th>".
									"<th>Invoice Date</th>".
									"<th>Total Price (Inc Tax)</th>".
									"<th>Total Paid</th>".
									"<th>Total Owed</th>".
								"</tr>";

						//iterate through each invoice record stored within the standards document
						$recordNumber=1;
						foreach ($esDocument->invoiceRecords as $invoiceRecord)
						{
							//output details of the invoice record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".$invoiceRecord->keyInvoiceID."</td>".
								"<td>".$invoiceRecord->invoiceID."</td>".
								"<td>".$invoiceRecord->invoiceNumber."</td>".
								"<td>".date("d/m/Y", ($invoiceRecord->invoiceDate/1000))."</td>".
								"<td>".money_format('%.2n', $invoiceRecord->totalIncTax)." ".$invoiceRecord->currencyCode."</td>".
								"<td>".money_format('%.2n', $invoiceRecord->totalPaid)." ".$invoiceRecord->currencyCode."</td>".
								"<td>".money_format('%.2n', $invoiceRecord->balance)." ".$invoiceRecord->currencyCode."</td>".
							"</tr>";
							
							$recordNumber++;
						}
					}
					break;
				case ESDocumentConstants::RECORD_TYPE_ORDER_SALE:
					
					$resultMessage = 
						"SUCCESS - account sales order record data successfully obtained from the platform<br/>".
						"Sales Order Records Returned: ".$esDocument->totalDataRecords;

					//check that sales order records have been placed into the standards document
					if ($esDocument->orderSaleRecords != null)
					{
						$resultMessage = $resultMessage."<br/>Sales Order Records:".
						'<table style="width: 100%;">'.
							"<tr>".
								"<th>#</th>".
								"<th>Key Order Sale ID</th>".
								"<th>Order ID</th>".
								"<th>Order Number</th>".
								"<th>Order Date</th>".
								"<th>Total Price (Inc Tax)</th>".
								"<th>Total Paid</th>".
								"<th>Total Owed</th>".
							"</tr>";

						//iterate through each sales order record stored within the standards document
						$recordNumber=1;
						foreach ($esDocument->orderSaleRecords as $orderSaleRecord)
						{
							//output details of the sales order record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".$orderSaleRecord->keyOrderSaleID."</td>".
								"<td>".$orderSaleRecord->orderID."</td>".
								"<td>".$orderSaleRecord->orderNumber."</td>".
								"<td>".date("d/m/Y", ($orderSaleRecord->orderDate/1000))."</td>".
								"<td>".money_format('%.2n', $orderSaleRecord->totalIncTax)." ".$orderSaleRecord->currencyCode."</td>".
								"<td>".money_format('%.2n', $orderSaleRecord->totalPaid)." ".$orderSaleRecord->currencyCode."</td>".
								"<td>".money_format('%.2n', $orderSaleRecord->balance)." ".$orderSaleRecord->currencyCode."</td>".
							"</tr>";
							
							$recordNumber++;
						}
					}
					break;
				case ESDocumentConstants::RECORD_TYPE_BACKORDER:
					
					$resultMessage = 
						"SUCCESS - account back order record data successfully obtained from the platform<br/>".
						"Back Order Records Returned: ".$esDocument->totalDataRecords;

					//check that back order records have been placed into the standards document
					if ($esDocument->backOrderRecords != null)
					{
						$resultMessage = $resultMessage."<br/>Back Order Records:".
						'<table style="width: 100%;">'.
							"<tr>".
								"<th>#</th>".
								"<th>Key Back Order ID</th>".
								"<th>Order ID</th>".
								"<th>Back Order Number</th>".
								"<th>Order Date</th>".
								"<th>Total Price (Inc Tax)</th>".
							"</tr>";

						//iterate through each back order record stored within the standards document
						$recordNumber=1;
						foreach ($esDocument->backOrderRecords as $backOrderRecord)
						{
							//output details of the back order record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".$backOrderRecord->keyBackOrderID."</td>".
								"<td>".$backOrderRecord->backOrderID."</td>".
								"<td>".$backOrderRecord->backOrderNumber."</td>".
								"<td>".date("d/m/Y", ($backOrderRecord->backOrderDate/1000))."</td>".
								"<td>".money_format('%.2n', $backOrderRecord->totalIncTax)." ".$backOrderRecord->currencyCode."</td>".
							"</tr>";
							
							$recordNumber++;
						}
					}
					break;
				case ESDocumentConstants::RECORD_TYPE_TRANSACTION:
					
					$resultMessage = 
						"SUCCESS - account transaction record data successfully obtained from the platform<br/>".
						"Transaction Returned: ".$esDocument->totalDataRecords;

					//check that transaction records have been placed into the standards document
					if ($esDocument->transactionRecords != null)
					{
						$resultMessage = $resultMessage."<br/>Transaction Records:".
						'<table style="width: 100%;">'.
							"<tr>".
								"<th>#</th>".
								"<th>Key Transaction ID</th>".
								"<th>Transaction ID</th>".
								"<th>Transaction Number</th>".
								"<th>Transaction Date</th>".
								"<th>Amount Debited</th>".
								"<th>Amount Credited</th>".
								"<th>Balance</th>".
							"</tr>";

						//iterate through each transaction record stored within the standards document
						$recordNumber=1;
						foreach ($esDocument->backOrderRecords as $transactionRecord)
						{
							//output details of the transaction record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".$transactionRecord->keyTransactionID."</td>".
								"<td>".$transactionRecord->transactionID."</td>".
								"<td>".$transactionRecord->transactionNumber."</td>".
								"<td>".date("d/m/Y", ($transactionRecord->transactionDate/1000))."</td>".
								"<td>".money_format('%.2n', $transactionRecord->debitAmount)." ".$transactionRecord->currencyCode."</td>".
								"<td>".money_format('%.2n', $transactionRecord->creditAmount)." ".$transactionRecord->currencyCode."</td>".
								"<td>".money_format('%.2n', $transactionRecord->balance)." ".$transactionRecord->currencyCode."</td>".
							"</tr>";
							
							$recordNumber++;
						}
					}
					break;
				case ESDocumentConstants::RECORD_TYPE_CREDIT:
					$resultMessage = 
						"SUCCESS - account credit record data successfully obtained from the platform<br/>".
						"Credit Records Returned: ".$esDocument->totalDataRecords;

					//check that credit records have been placed into the standards document
					if ($esDocument->creditRecords != null)
					{
						$resultMessage = $resultMessage."<br/>Transaction Records:".
						'<table style="width: 100%;">'.
							"<tr>".
								"<th>#</th>".
								"<th>Key Credit ID</th>".
								"<th>Credit ID</th>".
								"<th>Credit Number</th>".
								"<th>Credit Date</th>".
								"<th>Amount Credited</th>".
							"</tr>";

						//iterate through each credit record stored within the standards document
						$recordNumber=1;
						foreach ($esDocument->creditRecords as $creditRecord)
						{
							//output details of the credit record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".$creditRecord->keyCreditID."</td>".
								"<td>".$creditRecord->creditID."</td>".
								"<td>".$creditRecord->creditNumber."</td>".
								"<td>".date("d/m/Y", ($creditRecord->creditDate/1000))."</td>".
								"<td>".money_format('%.2n', $creditRecord->appliedAmount)." ".$creditRecord->currencyCode."</td>".
							"</tr>";
							
							$recordNumber++;
						}
					}
					break;
				case ESDocumentConstants::RECORD_TYPE_PAYMENT:
					$resultMessage = 
						"SUCCESS - account payment record data successfully obtained from the platform<br/>".
						"Payment Records Returned: ".$esDocument->totalDataRecords;

					//check that payment records have been placed into the standards document
					if ($esDocument->paymentRecords != null)
					{
						$resultMessage = $resultMessage."<br/>Transaction Records:".
						'<table style="width: 100%;">'.
							"<tr>".
								"<th>#</th>".
								"<th>Key Payment ID</th>".
								"<th>Payment ID</th>".
								"<th>Payment Number</th>".
								"<th>Payment Date</th>".
								"<th>Total Amount Paid</th>".
							"</tr>";

						//iterate through each payment record stored within the standards document
						$recordNumber=1;
						foreach ($esDocument->paymentRecords as $paymentRecord)
						{
							//output details of the payment record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".$paymentRecord->keyPaymentID."</td>".
								"<td>".$paymentRecord->paymentID."</td>".
								"<td>".$paymentRecord->paymentNumber."</td>".
								"<td>".date("d/m/Y", ($paymentRecord->paymentDate/1000))."</td>".
								"<td>".money_format('%.2n', $paymentRecord->totalAmount)." ".$paymentRecord->currencyCode."</td>".
							"</tr>";
							
							$recordNumber++;
						}
					}
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
	echo "<div>$resultMessage<div><br/>";
?>
```

### Retrieve Customer Account Record Endpoint
The SQUIZZ.com platform's API has an endpoint that allows an organisation to retrieve the details and lines for a record from another connected organisation's business sytem, based on a record associated to an assigned customer account.
This endpoint allows an organisation to securely get the details for a invoice, sales order, back order, transactions. credit or payment record, retrieved in realtime from a supplier organisation's connected business system.
The record returned from endpoint is formatted as JSON data conforming to the "Ecommerce Standards Document" standards, with the document containing an array of zero or one records. Use the Ecommerce Standards library to easily read through the documents and records, to find data natively using PHP classes.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section1474](https://www.squizz.com/docs/squizz/Platform-API.html#section1474) for more documentation about the endpoint and its requirements.
See the example below on how the call the Retrieve Customer Account Records endpoint. Note that a session must first be created in the API before calling the endpoint.

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
	use squizz\api\v1\endpoint\APIv1EndpointOrgRetrieveCustomerAccountRecord;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryBackOrder;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryBackOrderLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryCredit;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryCreditLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryInvoice;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryInvoiceLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryOrderSale;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryOrderSaleLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryPayment;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryPaymentLine;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryQuote;
	use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryQuoteLine;
	use EcommerceStandardsDocuments\ESDocumentConstants;
	use EcommerceStandardsDocuments\ESDocumentCustomerAccountEnquiry;
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$supplierOrgID = $_GET["supplierOrgID"];
	$sessionTimeoutMilliseconds = 60000;
	$customerAccountCode = $_GET["customerAccountCode"];
	$keyRecordID = $_GET["keyRecordID"];
	$recordType = $_GET["recordType"];
	
	switch($recordType)
	{
		case ESDocumentConstants::RECORD_TYPE_ORDER_SALE:
			$recordType = ESDocumentConstants::RECORD_TYPE_ORDER_SALE;
			break;
		case ESDocumentConstants::RECORD_TYPE_BACKORDER:
			$recordType = ESDocumentConstants::RECORD_TYPE_BACKORDER;
			break;
		case ESDocumentConstants::RECORD_TYPE_CREDIT:
			$recordType = ESDocumentConstants::RECORD_TYPE_CREDIT;
			break;
		case ESDocumentConstants::RECORD_TYPE_PAYMENT:
			$recordType = ESDocumentConstants::RECORD_TYPE_PAYMENT;
			break;
		default:
			$recordType = ESDocumentConstants::RECORD_TYPE_INVOICE;
	}
	
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
	
	//search for account records if the API was successfully created
	if($apiOrgSession->sessionExists())
	{
		//after 30 seconds give up on waiting for a response from the API
		$timeoutMilliseconds = 30000;
		
		//call the platform's API to search for customer account records from the supplier organiation's connected business system
		$endpointResponseESD = APIv1EndpointOrgRetrieveCustomerAccountRecord::call($apiOrgSession, $timeoutMilliseconds, $recordType, $supplierOrgID, $customerAccountCode, $keyRecordID);
		
		$esDocument = $endpointResponseESD->esDocument;

		//check that the data successfully imported
		if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
			
			$result = "SUCCESS";
			$resultMessage = "Account record data successfully obtained from the platform.<br/>Records returned:".$esDocument->totalDataRecords;
			
			//output records based on the record type
			switch($recordType){
				case ESDocumentConstants::RECORD_TYPE_INVOICE:
					$resultMessage = 
						"SUCCESS - account invoice record data successfully obtained from the platform<br/>".
						"Invoice Records Returned: ".$esDocument->totalDataRecords;

					//check that invoice records have been placed into the standards document
					if ($esDocument->invoiceRecords != null)
					{	
						//display the details of the record stored within the standards document
						foreach ($esDocument->invoiceRecords as $invoiceRecord)
						{
							//output details of the invoice record
							$resultMessage = $resultMessage."<br/>Invoice Details:".
								'<table style="width: 100%;">'.
								"<tr>".
									"<th>Invoice Field</th>".
									"<th>Value</th>".
								"</tr>".
								"<tr><td>Key Invoice ID:</td><td>" . $invoiceRecord->keyInvoiceID."</td></tr>".
								"<tr><td>Invoice ID:</td><td>" . $invoiceRecord->invoiceID."</td></tr>".
								"<tr><td>Invoice Number:</td><td>" . $invoiceRecord->invoiceNumber."</td></tr>".
								"<tr><td>Invoice Date:</td><td>" . date("d/m/Y", ($invoiceRecord->invoiceDate/1000))."</td></tr>".
								"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $invoiceRecord->totalIncTax)." ".$invoiceRecord->currencyCode."</td></tr>".
								"<tr><td>Total Paid:</td><td>" . money_format('%.2n', $invoiceRecord->totalPaid)." ".$invoiceRecord->currencyCode."</td></tr>".
								"<tr><td>Total Owed:</td><td>" . money_format('%.2n', $invoiceRecord->balance)." ".$invoiceRecord->currencyCode."</td></tr>".
								"<tr><td>Description:</td><td>" . $invoiceRecord->description."</td></tr>".
								"<tr><td>Comment:</td><td>" . $invoiceRecord->comment."</td></tr>".
								"<tr><td>Reference Number:</td><td>" . $invoiceRecord->referenceNumber."</td></tr>".
								"<tr><td>Reference Type:</td><td>" . $invoiceRecord->referenceType."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Delivery Address: </td></tr>".
								"<tr><td>Organisation Name:</td><td>" . $invoiceRecord->deliveryOrgName."</td></tr>".
								"<tr><td>Contact:</td><td>" . $invoiceRecord->deliveryContact."</td></tr>".
								"<tr><td>Address 1:</td><td>" . $invoiceRecord->deliveryAddress1."</td></tr>".
								"<tr><td>Address 2:</td><td>" . $invoiceRecord->deliveryAddress2."</td></tr>".
								"<tr><td>Address 3:</td><td>" . $invoiceRecord->deliveryAddress3."</td></tr>".
								"<tr><td>State/Province/Region:</td><td>" . $invoiceRecord->deliveryStateProvince."</td></tr>".
								"<tr><td>Country:</td><td>" . $invoiceRecord->deliveryCountry."</td></tr>".
								"<tr><td>Postcode/Zipcode:</td><td>" . $invoiceRecord->deliveryPostcode."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Billing Address:</td></tr>".
								"<tr><td>Organisation Name:</td><td>" . $invoiceRecord->billingOrgName."</td></tr>".
								"<tr><td>Contact:</td><td>" . $invoiceRecord->billingContact."</td></tr>".
								"<tr><td>Address 1:</td><td>" . $invoiceRecord->billingAddress1."</td></tr>".
								"<tr><td>Address 2:</td><td>" . $invoiceRecord->billingAddress2."</td></tr>".
								"<tr><td>Address 3:</td><td>" . $invoiceRecord->billingAddress3."</td></tr>".
								"<tr><td>State/Province/Region:</td><td>" . $invoiceRecord->billingStateProvince."</td></tr>".
								"<tr><td>Country:</td><td>" . $invoiceRecord->billingCountry."</td></tr>".
								"<tr><td>Postcode/Zipcode:</td><td>" . $invoiceRecord->billingPostcode."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Freight Details: </td></tr>".
								"<tr><td>Consignment Code:</td><td>" . $invoiceRecord->freightCarrierConsignCode."</td></tr>".
								"<tr><td>Tracking Code:</td><td>" . $invoiceRecord->freightCarrierTrackingCode."</td></tr>".
								"<tr><td>Carrier Name:</td><td>" . $invoiceRecord->freightCarrierName."</td></tr>";

							//output the details of each line
							if ($invoiceRecord->lines != null)
							{
								$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
								$lineNumber=0;
								foreach ($invoiceRecord->lines as $invoiceLineRecord)
								{
									$lineNumber++;
									$resultMessage = $resultMessage.
										"<tr><td colspan=\"2\"><hr/></td></tr>".
										"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

									if ($invoiceLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
									{
										$resultMessage = $resultMessage.
											"<tr><td>Line Type::</td><td>ITEM".
											"<tr><td>Line Item ID::</td><td>" . $invoiceLineRecord->lineItemID."</td></tr>".
											"<tr><td>Line Item Code::</td><td>" . $invoiceLineRecord->lineItemCode."</td></tr>".
											"<tr><td>Description::</td><td>" . $invoiceLineRecord->description."</td></tr>".
											"<tr><td>Quantity Ordered::</td><td>" . $invoiceLineRecord->quantityOrdered . " " . $invoiceLineRecord->unit."</td></tr>".
											"<tr><td>Quantity Delivered::</td><td>" . $invoiceLineRecord->quantityDelivered . " " . $invoiceLineRecord->unit."</td></tr>".
											"<tr><td>Quantity Back Ordered:</td><td>" . $invoiceLineRecord->quantityBackordered . " " . $invoiceLineRecord->unit."</td></tr>".
											"<tr><td>Unit Price (Ex Tax):</td><td>". money_format('%.2n', $invoiceLineRecord->priceExTax)." ".$invoiceRecord->currencyCode ."</td></tr>".
											"<tr><td>Total Price (Ex Tax):</td><td>" . money_format('%.2n', $invoiceLineRecord->totalPriceExTax)." ".$invoiceRecord->currencyCode."</td></tr>".
											"<tr><td>Total Tax:</td><td>" . money_format('%.2n', $invoiceLineRecord->totalPriceTax) . " Inclusive of " . $invoiceLineRecord->taxCode . " " . $invoiceLineRecord->taxCodeRatePercent . "%"."</td></tr>".
											"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $invoiceLineRecord->totalPriceIncTax)." ".$invoiceRecord->currencyCode."</td></tr>";
									}
									else if ($invoiceLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
									{
										$resultMessage = 
											$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
											"<tr><td>Description:</td><td>".$invoiceLineRecord.description."</td></tr>";
									}
								}
							}
							
							$resultMessage = $resultMessage.'<table style="width: 100%;">';
							break;
						}
					}
					break;
				case ESDocumentConstants::RECORD_TYPE_ORDER_SALE:
					$resultMessage = 
						"SUCCESS - account sales order record data successfully obtained from the platform<br/>".
						"Sales Order Records Returned: ".$esDocument->totalDataRecords;

					//check that sales order records have been placed into the standards document
					if ($esDocument->orderSaleRecords != null)
					{	
						//display the details of the record stored within the standards document
						foreach ($esDocument->orderSaleRecords as $orderSaleRecord)
						{
							//output details of the sales order record
							$resultMessage = $resultMessage."<br/>Sales Order Details:".
								'<table style="width: 100%;">'.
								"<tr>".
									"<th>Sales Order Field</th>".
									"<th>Value</th>".
								"</tr>".
								"<tr><td>Key Order Sale ID:</td><td>" . $orderSaleRecord->keyOrderSaleID."</td></tr>".
								"<tr><td>Order ID:</td><td>" . $orderSaleRecord->orderID."</td></tr>".
								"<tr><td>Order Number:</td><td>" . $orderSaleRecord->orderNumber."</td></tr>".
								"<tr><td>Order Date:</td><td>" . date("d/m/Y", ($orderSaleRecord->orderDate/1000))."</td></tr>".
								"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $orderSaleRecord->totalIncTax)." ".$orderSaleRecord->currencyCode."</td></tr>".
								"<tr><td>Total Paid:</td><td>" . money_format('%.2n', $orderSaleRecord->totalPaid)." ".$orderSaleRecord->currencyCode."</td></tr>".
								"<tr><td>Total Owed:</td><td>" . money_format('%.2n', $orderSaleRecord->balance)." ".$orderSaleRecord->currencyCode."</td></tr>".
								"<tr><td>Description:</td><td>" . $orderSaleRecord->description."</td></tr>".
								"<tr><td>Comment:</td><td>" . $orderSaleRecord->comment."</td></tr>".
								"<tr><td>Reference Number:</td><td>" . $orderSaleRecord->referenceNumber."</td></tr>".
								"<tr><td>Reference Type:</td><td>" . $orderSaleRecord->referenceType."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Delivery Address: </td></tr>".
								"<tr><td>Organisation Name:</td><td>" . $orderSaleRecord->deliveryOrgName."</td></tr>".
								"<tr><td>Contact:</td><td>" . $orderSaleRecord->deliveryContact."</td></tr>".
								"<tr><td>Address 1:</td><td>" . $orderSaleRecord->deliveryAddress1."</td></tr>".
								"<tr><td>Address 2:</td><td>" . $orderSaleRecord->deliveryAddress2."</td></tr>".
								"<tr><td>Address 3:</td><td>" . $orderSaleRecord->deliveryAddress3."</td></tr>".
								"<tr><td>State/Province/Region:</td><td>" . $orderSaleRecord->deliveryStateProvince."</td></tr>".
								"<tr><td>Country:</td><td>" . $orderSaleRecord->deliveryCountry."</td></tr>".
								"<tr><td>Postcode/Zipcode:</td><td>" . $orderSaleRecord->deliveryPostcode."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Billing Address:</td></tr>".
								"<tr><td>Organisation Name:</td><td>" . $orderSaleRecord->billingOrgName."</td></tr>".
								"<tr><td>Contact:</td><td>" . $orderSaleRecord->billingContact."</td></tr>".
								"<tr><td>Address 1:</td><td>" . $orderSaleRecord->billingAddress1."</td></tr>".
								"<tr><td>Address 2:</td><td>" . $orderSaleRecord->billingAddress2."</td></tr>".
								"<tr><td>Address 3:</td><td>" . $orderSaleRecord->billingAddress3."</td></tr>".
								"<tr><td>State/Province/Region:</td><td>" . $orderSaleRecord->billingStateProvince."</td></tr>".
								"<tr><td>Country:</td><td>" . $orderSaleRecord->billingCountry."</td></tr>".
								"<tr><td>Postcode/Zipcode:</td><td>" . $orderSaleRecord->billingPostcode."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Freight Details: </td></tr>".
								"<tr><td>Consignment Code:</td><td>" . $orderSaleRecord->freightCarrierConsignCode."</td></tr>".
								"<tr><td>Tracking Code:</td><td>" . $orderSaleRecord->freightCarrierTrackingCode."</td></tr>".
								"<tr><td>Carrier Name:</td><td>" . $orderSaleRecord->freightCarrierName."</td></tr>";

							//output the details of each line
							if ($orderSaleRecord->lines != null)
							{
								$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
								$lineNumber=0;
								foreach ($orderSaleRecord->lines as $orderSaleLineRecord)
								{
									$lineNumber++;
									$resultMessage = $resultMessage.
										"<tr><td colspan=\"2\"><hr/></td></tr>".
										"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

									if ($orderSaleLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
									{
										$resultMessage = $resultMessage.
											"<tr><td>Line Type::</td><td>ITEM".
											"<tr><td>Line Item ID::</td><td>" . $orderSaleLineRecord->lineItemID."</td></tr>".
											"<tr><td>Line Item Code::</td><td>" . $orderSaleLineRecord->lineItemCode."</td></tr>".
											"<tr><td>Description::</td><td>" . $orderSaleLineRecord->description."</td></tr>".
											"<tr><td>Quantity Ordered::</td><td>" . $orderSaleLineRecord->quantityOrdered . " " . $orderSaleLineRecord->unit."</td></tr>".
											"<tr><td>Quantity Delivered::</td><td>" . $orderSaleLineRecord->quantityDelivered . " " . $orderSaleLineRecord->unit."</td></tr>".
											"<tr><td>Quantity Back Ordered:</td><td>" . $orderSaleLineRecord->quantityBackordered . " " . $orderSaleLineRecord->unit."</td></tr>".
											"<tr><td>Unit Price (Ex Tax):</td><td>". money_format('%.2n', $orderSaleLineRecord->priceExTax)." ".$orderSaleRecord->currencyCode ."</td></tr>".
											"<tr><td>Total Price (Ex Tax):</td><td>" . money_format('%.2n', $orderSaleLineRecord->totalPriceExTax)." ".$orderSaleRecord->currencyCode."</td></tr>".
											"<tr><td>Total Tax:</td><td>" . money_format('%.2n', $orderSaleLineRecord->totalPriceTax) . " Inclusive of " . $orderSaleLineRecord->taxCode . " " . $orderSaleLineRecord->taxCodeRatePercent . "%"."</td></tr>".
											"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $orderSaleLineRecord->totalPriceIncTax)." ".$orderSaleRecord->currencyCode."</td></tr>";
									}
									else if ($orderSaleLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
									{
										$resultMessage = 
											$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
											"<tr><td>Description:</td><td>".$orderSaleLineRecord.description."</td></tr>";
									}
								}
							}
							
							$resultMessage = $resultMessage.'<table style="width: 100%;">';
							break;
						}
					}
					break;
				case ESDocumentConstants::RECORD_TYPE_BACKORDER:
					$resultMessage = 
						"SUCCESS - account back order record data successfully obtained from the platform<br/>".
						"Sales Order Records Returned: ".$esDocument->totalDataRecords;

					//check that back order records have been placed into the standards document
					if ($esDocument->backOrderRecords != null)
					{	
						//display the details of the record stored within the standards document
						foreach ($esDocument->backOrderRecords as $backOrderRecord)
						{
							//output details of the back order record
							$resultMessage = $resultMessage."<br/>Back Order Details:".
								'<table style="width: 100%;">'.
								"<tr>".
									"<th>Back Order Field</th>".
									"<th>Value</th>".
								"</tr>".
								"<tr><td>Key Back Order ID:</td><td>" . $backOrderRecord->keyBackOrderID."</td></tr>".
								"<tr><td>Back Order ID:</td><td>" . $backOrderRecord->backOrderID."</td></tr>".
								"<tr><td>Back Order Number:</td><td>" . $backOrderRecord->backOrderNumber."</td></tr>".
								"<tr><td>Back Order Date:</td><td>" . date("d/m/Y", ($backOrderRecord->backOrderDate/1000))."</td></tr>".
								"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $backOrderRecord->totalIncTax)." ".$backOrderRecord->currencyCode."</td></tr>".
								"<tr><td>Total Paid:</td><td>" . money_format('%.2n', $backOrderRecord->totalPaid)." ".$backOrderRecord->currencyCode."</td></tr>".
								"<tr><td>Total Owed:</td><td>" . money_format('%.2n', $backOrderRecord->balance)." ".$backOrderRecord->currencyCode."</td></tr>".
								"<tr><td>Description:</td><td>" . $backOrderRecord->description."</td></tr>".
								"<tr><td>Comment:</td><td>" . $backOrderRecord->comment."</td></tr>".
								"<tr><td>Reference Number:</td><td>" . $backOrderRecord->referenceNumber."</td></tr>".
								"<tr><td>Reference Type:</td><td>" . $backOrderRecord->referenceType."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Delivery Address: </td></tr>".
								"<tr><td>Organisation Name:</td><td>" . $backOrderRecord->deliveryOrgName."</td></tr>".
								"<tr><td>Contact:</td><td>" . $backOrderRecord->deliveryContact."</td></tr>".
								"<tr><td>Address 1:</td><td>" . $backOrderRecord->deliveryAddress1."</td></tr>".
								"<tr><td>Address 2:</td><td>" . $backOrderRecord->deliveryAddress2."</td></tr>".
								"<tr><td>Address 3:</td><td>" . $backOrderRecord->deliveryAddress3."</td></tr>".
								"<tr><td>State/Province/Region:</td><td>" . $backOrderRecord->deliveryStateProvince."</td></tr>".
								"<tr><td>Country:</td><td>" . $backOrderRecord->deliveryCountry."</td></tr>".
								"<tr><td>Postcode/Zipcode:</td><td>" . $backOrderRecord->deliveryPostcode."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Billing Address:</td></tr>".
								"<tr><td>Organisation Name:</td><td>" . $backOrderRecord->billingOrgName."</td></tr>".
								"<tr><td>Contact:</td><td>" . $backOrderRecord->billingContact."</td></tr>".
								"<tr><td>Address 1:</td><td>" . $backOrderRecord->billingAddress1."</td></tr>".
								"<tr><td>Address 2:</td><td>" . $backOrderRecord->billingAddress2."</td></tr>".
								"<tr><td>Address 3:</td><td>" . $backOrderRecord->billingAddress3."</td></tr>".
								"<tr><td>State/Province/Region:</td><td>" . $backOrderRecord->billingStateProvince."</td></tr>".
								"<tr><td>Country:</td><td>" . $backOrderRecord->billingCountry."</td></tr>".
								"<tr><td>Postcode/Zipcode:</td><td>" . $backOrderRecord->billingPostcode."</td></tr>";

							//output the details of each line
							if ($backOrderRecord->lines != null)
							{
								$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
								$lineNumber=0;
								foreach ($backOrderRecord->lines as $backOrderLineRecord)
								{
									$lineNumber++;
									$resultMessage = $resultMessage.
										"<tr><td colspan=\"2\"><hr/></td></tr>".
										"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

									if ($backOrderLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
									{
										$resultMessage = $resultMessage.
											"<tr><td>Line Type::</td><td>ITEM".
											"<tr><td>Line Item ID::</td><td>" . $backOrderLineRecord->lineItemID."</td></tr>".
											"<tr><td>Line Item Code::</td><td>" . $backOrderLineRecord->lineItemCode."</td></tr>".
											"<tr><td>Description::</td><td>" . $backOrderLineRecord->description."</td></tr>".
											"<tr><td>Quantity Ordered::</td><td>" . $backOrderLineRecord->quantityOrdered . " " . $backOrderLineRecord->unit."</td></tr>".
											"<tr><td>Quantity Delivered::</td><td>" . $backOrderLineRecord->quantityDelivered . " " . $backOrderLineRecord->unit."</td></tr>".
											"<tr><td>Quantity Back Ordered:</td><td>" . $backOrderLineRecord->quantityBackordered . " " . $backOrderLineRecord->unit."</td></tr>".
											"<tr><td>Unit Price (Ex Tax):</td><td>". money_format('%.2n', $backOrderLineRecord->priceExTax)." ".$backOrderRecord->currencyCode ."</td></tr>".
											"<tr><td>Total Price (Ex Tax):</td><td>" . money_format('%.2n', $backOrderLineRecord->totalPriceExTax)." ".$backOrderRecord->currencyCode."</td></tr>".
											"<tr><td>Total Tax:</td><td>" . money_format('%.2n', $backOrderLineRecord->totalPriceTax) . " Inclusive of " . $backOrderLineRecord->taxCode . " " . $backOrderLineRecord->taxCodeRatePercent . "%"."</td></tr>".
											"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $backOrderLineRecord->totalPriceIncTax)." ".$backOrderRecord->currencyCode."</td></tr>";
									}
									else if ($backOrderLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
									{
										$resultMessage = 
											$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
											"<tr><td>Description:</td><td>".$backOrderLineRecord.description."</td></tr>";
									}
								}
							}
							
							$resultMessage = $resultMessage.'<table style="width: 100%;">';
							break;
						}
					}
					break;
				case ESDocumentConstants::RECORD_TYPE_CREDIT:
					$resultMessage = 
						"SUCCESS - account credit record data successfully obtained from the platform<br/>".
						"Credit Records Returned: ".$esDocument->totalDataRecords;

					//check that credit records have been placed into the standards document
					if ($esDocument->creditRecords != null)
					{	
						//display the details of the record stored within the standards document
						foreach ($esDocument->creditRecords as $creditRecord)
						{
							//output details of the credit record
							$resultMessage = $resultMessage."<br/>Credit Details:".
								'<table style="width: 100%;">'.
								"<tr>".
									"<th>Credit Field</th>".
									"<th>Value</th>".
								"</tr>".
								"<tr><td>Key Credit ID:</td><td>" . $creditRecord->keyCreditID."</td></tr>".
								"<tr><td>Credit ID:</td><td>" . $creditRecord->creditID."</td></tr>".
								"<tr><td>Credit Number:</td><td>" . $creditRecord->creditNumber."</td></tr>".
								"<tr><td>Credit Date:</td><td>" . date("d/m/Y", ($creditRecord->creditDate/1000))."</td></tr>".
								"<tr><td>Amount Credited:</td><td>" . money_format('%.2n', $creditRecord->appliedAmount)." ".$creditRecord->currencyCode."</td></tr>".
								"<tr><td>Description:</td><td>" . $creditRecord->description."</td></tr>".
								"<tr><td>Comment:</td><td>" . $creditRecord->comment."</td></tr>".
								"<tr><td>Reference Number:</td><td>" . $creditRecord->referenceNumber."</td></tr>".
								"<tr><td>Reference Type:</td><td>" . $creditRecord->referenceType."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Delivery Address: </td></tr>".
								"<tr><td>Organisation Name:</td><td>" . $creditRecord->deliveryOrgName."</td></tr>".
								"<tr><td>Contact:</td><td>" . $creditRecord->deliveryContact."</td></tr>".
								"<tr><td>Address 1:</td><td>" . $creditRecord->deliveryAddress1."</td></tr>".
								"<tr><td>Address 2:</td><td>" . $creditRecord->deliveryAddress2."</td></tr>".
								"<tr><td>Address 3:</td><td>" . $creditRecord->deliveryAddress3."</td></tr>".
								"<tr><td>State/Province/Region:</td><td>" . $creditRecord->deliveryStateProvince."</td></tr>".
								"<tr><td>Country:</td><td>" . $creditRecord->deliveryCountry."</td></tr>".
								"<tr><td>Postcode/Zipcode:</td><td>" . $creditRecord->deliveryPostcode."</td></tr>".
								"<tr><td></td></tr>".
								"<tr><td>Billing Address:</td></tr>".
								"<tr><td>Organisation Name:</td><td>" . $creditRecord->billingOrgName."</td></tr>".
								"<tr><td>Contact:</td><td>" . $creditRecord->billingContact."</td></tr>".
								"<tr><td>Address 1:</td><td>" . $creditRecord->billingAddress1."</td></tr>".
								"<tr><td>Address 2:</td><td>" . $creditRecord->billingAddress2."</td></tr>".
								"<tr><td>Address 3:</td><td>" . $creditRecord->billingAddress3."</td></tr>".
								"<tr><td>State/Province/Region:</td><td>" . $creditRecord->billingStateProvince."</td></tr>".
								"<tr><td>Country:</td><td>" . $creditRecord->billingCountry."</td></tr>".
								"<tr><td>Postcode/Zipcode:</td><td>" . $creditRecord->billingPostcode."</td></tr>";

							//output the details of each line
							if ($creditRecord->lines != null)
							{
								$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
								$lineNumber=0;
								foreach ($creditRecord->lines as $creditLineRecord)
								{
									$lineNumber++;
									$resultMessage = $resultMessage.
										"<tr><td colspan=\"2\"><hr/></td></tr>".
										"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

									if ($creditLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
									{
										$resultMessage = $resultMessage.
											"<tr><td>Line Type:</td><td>ITEM".
											"<tr><td>Line Item ID:</td><td>" . $creditLineRecord->lineItemID."</td></tr>".
											"<tr><td>Line Item Code:</td><td>" . $creditLineRecord->lineItemCode."</td></tr>".
											"<tr><td>Description:</td><td>" . $creditLineRecord->description."</td></tr>".
											"<tr><td>Reference Number:</td><td>" . $creditLineRecord->referenceNumber."</td></tr>".
											"<tr><td>Reference Type:</td><td>" . $creditLineRecord->referenceType ."</td></tr>".
											"<tr><td>Reference Key ID:</td><td>" . $creditLineRecord->referenceKeyID ."</td></tr>".
											"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $creditLineRecord->totalPriceIncTax)." ".$creditLineRecord->currencyCode."</td></tr>";
									}
									else if ($creditLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
									{
										$resultMessage = 
											$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
											"<tr><td>Description:</td><td>".$creditLineRecord.description."</td></tr>";
									}
								}
							}
							
							$resultMessage = $resultMessage.'<table style="width: 100%;">';
							break;
						}
					}
					break;
				case ESDocumentConstants::RECORD_TYPE_PAYMENT:
					$resultMessage = 
						"SUCCESS - account payment record data successfully obtained from the platform<br/>".
						"Payment Records Returned: ".$esDocument->totalDataRecords;

					//check that payment records have been placed into the standards document
					if ($esDocument->paymentRecords != null)
					{	
						//display the details of the record stored within the standards document
						foreach ($esDocument->paymentRecords as $paymentRecord)
						{
							//output details of the payment record
							$resultMessage = $resultMessage."<br/>Payment Details:".
								'<table style="width: 100%;">'.
								"<tr>".
									"<th>Payment Field</th>".
									"<th>Value</th>".
								"</tr>".
								"<tr><td>Key Payment ID:</td><td>" . $paymentRecord->keyPaymentID."</td></tr>".
								"<tr><td>Payment ID:</td><td>" . $paymentRecord->paymentID."</td></tr>".
								"<tr><td>Payment Number:</td><td>" . $paymentRecord->paymentNumber."</td></tr>".
								"<tr><td>Payment Date:</td><td>" . date("d/m/Y", ($paymentRecord->paymentDate/1000))."</td></tr>".
								"<tr><td>Total Amount:</td><td>" . money_format('%.2n', $paymentRecord->totalAmount)." ".$paymentRecord->currencyCode."</td></tr>".
								"<tr><td>Description:</td><td>" . $paymentRecord->description."</td></tr>".
								"<tr><td>Comment:</td><td>" . $paymentRecord->comment."</td></tr>".
								"<tr><td>Reference Number:</td><td>" . $paymentRecord->referenceNumber."</td></tr>".
								"<tr><td>Reference Type:</td><td>" . $paymentRecord->referenceType."</td></tr>".
								"<tr><td>Reference Key ID:</td><td>" . $paymentRecord->referenceKeyID."</td></tr>";

							//output the details of each line
							if ($paymentRecord->lines != null)
							{
								$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
								$lineNumber=0;
								foreach ($paymentRecord->lines as $paymentOrderLineRecord)
								{
									$lineNumber++;
									$resultMessage = $resultMessage.
										"<tr><td colspan=\"2\"><hr/></td></tr>".
										"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

									if ($paymentOrderLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
									{
										$resultMessage = $resultMessage.
											"<tr><td>Line Type:</td><td>ITEM".
											"<tr><td>Line Item ID:</td><td>" . $paymentOrderLineRecord->lineItemID."</td></tr>".
											"<tr><td>Line Item Code:</td><td>" . $paymentOrderLineRecord->lineItemCode."</td></tr>".
											"<tr><td>Description:</td><td>" . $paymentOrderLineRecord->description."</td></tr>".
											"<tr><td>Reference Number:</td><td>" . $paymentOrderLineRecord->referenceNumber."</td></tr>".
											"<tr><td>Reference Type:</td><td>" . $paymentOrderLineRecord->referenceType ."</td></tr>".
											"<tr><td>Reference Key ID:</td><td>" . $paymentOrderLineRecord->referenceKeyID ."</td></tr>".
											"<tr><td>Payment Amount:</td><td>" . money_format('%.2n', $paymentOrderLineRecord->amount)." ".$paymentRecord->currencyCode."</td></tr>";
									}
									else if ($paymentOrderLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
									{
										$resultMessage = 
											$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
											"<tr><td>Description:</td><td>".$paymentOrderLineRecord.description."</td></tr>";
									}
								}
							}
							
							$resultMessage = $resultMessage.'<table style="width: 100%;">';
							break;
						}
					}
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
	echo "<div>$resultMessage<div><br/>";
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
	$customerAccountCode = $_GET["customerAccountCode"];
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

	//send and procure purchase order if the API was successfully created
	if($apiOrgSession->sessionExists())
	{
		//create purchase order record to import
		$purchaseOrderRecord = new ESDRecordOrderPurchase();
		
		//set data within the purchase order (each of these 6 fields are optional)
		$purchaseOrderRecord->keyPurchaseOrderID = "111";
		$purchaseOrderRecord->purchaseOrderCode = "POEXAMPLE-345";
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
		//set mandatory data in line fields
		$orderProduct->productCode = "TEA-TOWEL-GREEN";
		$orderProduct->quantity = 4;
		
		//set the supplier's product code in this field if the supplier's product code is different from the customer org's product code
		$orderProduct->salesOrderProductCode = "TEA-TOWEL-GREEN";
		
		//set optional data in line fields
		$orderProduct->productName = "Green tea towel - 30 x 6 centimetres";
		$orderProduct->keySellUnitID = "2";
		$orderProduct->unitName = "EACH";
		$orderProduct->sellUnitBaseQuantity = 4;
		$orderProduct->priceExTax = 5.00;
		$orderProduct->priceIncTax = 5.50;
		$orderProduct->priceTax = 0.50;
		$orderProduct->priceTotalIncTax = 22.00;
		$orderProduct->priceTotalExTax = 20.00;
		$orderProduct->priceTotalTax = 2.00;
		
		//add order line to lines list
		array_push($orderLines, $orderProduct);
		
		//add a 2nd purchase order line record that is a text line
		$orderProduct = new ESDRecordOrderPurchaseLine();
		$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_TEXT;
		$orderProduct->textDescription = "Please bundle tea towels into a box";
		array_push($orderLines, $orderProduct);
		
		//add a 3rd purhase order line record
		$orderProduct = new ESDRecordOrderPurchaseLine();
		$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_PRODUCT;
		$orderProduct->productCode = "TEA-TOWEL-BLUE";
		$orderProduct->productName = "Blue tea towel - 30 x 6 centimetres";
		$orderProduct->quantity = 2;
		$orderProduct->salesOrderProductCode = "ACME-TTBLUE";
		array_push($orderLines, $orderProduct);
		
		//add order lines to the order
		$purchaseOrderRecord->lines = $orderLines;

		//create purchase order records list and add purchase order to it
		$purchaseOrderRecords = array();
		array_push($purchaseOrderRecords, $purchaseOrderRecord);
		
		//after 60 seconds give up on waiting for a response from the API when procuring the order
		$timeoutMilliseconds = 60000;
		
		//create purchase order Ecommerce Standards document and add purchase order records to the document
		$orderPurchaseESD = new ESDocumentOrderPurchase(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $purchaseOrderRecords, array());

		//send purchase order document to the API for procurement by the supplier organisation
		$endpointResponseESD = APIv1EndpointOrgProcurePurchaseOrderFromSupplier::call($apiOrgSession, $timeoutMilliseconds, $supplierOrgID, $customerAccountCode, $orderPurchaseESD);
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

### Send Customer Invoice To Customer Endpoint

The SQUIZZ.com platform's API has an endpoint that allows an orgnisation to send an invoice that it has raised against its customer to a designated customer organisation that is registered on the SQUIZZ.com platform. The invoice can then be imported into the customer's own system.
This endpoint allows an organisation sends invoices out to customers, enabling customers to have the invoices land in their system, with minimal or no data entry required, automating invoicing processes.
The endpoint relies upon a connection first being made between organisations within the SQUIZZ.com platform.
The endpoint may rely upon the invoice's products and taxes matching the customer's data, if the customer requires this validation.
The endpoint has a number of other requirements. See the endpoint documentation for more details on these requirements.

Each customer invoice needs to be imported within a "Ecommerce Standards Document" that contains a record for each customer invoice. Use the Ecommerce Standards library to easily create these documents and records.
It is recommended to only import one customer invoice at a time, since if an array of customer invoices is sent and one invoice fails, then no other invoices in the list will be attempted to import.
Read [https://www.squizz.com/docs/squizz/Web-Service-Endpoint:-Send-Customer-Invoice-To-Customer.html](https://www.squizz.com/docs/squizz/Web-Service-Endpoint:-Send-Customer-Invoice-To-Customer.html) for more documentation about the endpoint and its requirements.
See the example below on how the call the Send Customer Invoice To Customer endpoint. Note that a session must first be created in the API before calling the endpoint.

```php
	<?php
		/**
		* Copyright (C) 2019 Squizz PTY LTD
		* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
		* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
		* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
		*/
		
		require_once __DIR__ . '/../../../../../3rd-party/jsonmapper/JsonMapper/Exception.php';
		
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
		use squizz\api\v1\endpoint\APIv1EndpointOrgSendCustomerInvoiceToCustomer;
		use squizz\api\v1\APIv1OrgSession;
		use squizz\api\v1\APIv1Constants;
		use EcommerceStandardsDocuments\ESDRecordCustomerInvoice;
		use EcommerceStandardsDocuments\ESDRecordCustomerInvoiceLine;
		use EcommerceStandardsDocuments\ESDocumentConstants;
		use EcommerceStandardsDocuments\ESDocumentCustomerInvoice;
		use EcommerceStandardsDocuments\ESDocumentSupplierInvoice;
		use EcommerceStandardsDocuments\ESDRecordInvoiceLineTax;
		
		//obtain or load in an organisation's API credentials, in this example from command line arguments
		$orgID = $_GET["orgID"];
		$orgAPIKey = $_GET["orgAPIKey"];
		$orgAPIPass = $_GET["orgAPIPass"];
		$customerOrgID = $_GET["customerOrgID"];
		$supplierAccountCode = $_GET["supplierAccountCode"];
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
		
		//send the customer invoice if the API was successfully created
		if($apiOrgSession->sessionExists())
		{
			//create customer invoice record to import
			$customerInvoiceRecord = new ESDRecordCustomerInvoice();

			//set data within the customer invoice
			$customerInvoiceRecord->keyCustomerInvoiceID = "111";
			$customerInvoiceRecord->customerInvoiceCode = "CINV-22";
			$customerInvoiceRecord->customerInvoiceNumber = "22";
			$customerInvoiceRecord->salesOrderCode = "SO-332";
			$customerInvoiceRecord->purchaseOrderNumber = "PO-345";
			$customerInvoiceRecord->instructions = "Please pay within 30 days";
			$customerInvoiceRecord->keyCustomerAccountID = "2";
			$customerInvoiceRecord->customerAccountCode = "ACM-002";

			//set dates within the invoice, having the payment date set 30 days from now, and dispatch date 2 days ago
			$customerInvoiceRecord->paymentDueDate = time() + (30 * 24 * 60 * 60 * 1000);
			$customerInvoiceRecord->createdDate = time();
			$customerInvoiceRecord->dispatchedDate = time() + (-2 * 24 * 60 * 60 * 1000);
			
			//set delivery address that invoice goods were delivered to
			$customerInvoiceRecord->deliveryAddress1 = "32";
			$customerInvoiceRecord->deliveryAddress2 = "Main Street";
			$customerInvoiceRecord->deliveryAddress3 = "Melbourne";
			$customerInvoiceRecord->deliveryRegionName = "Victoria";
			$customerInvoiceRecord->deliveryCountryName = "Australia";
			$customerInvoiceRecord->deliveryPostcode = "3000";
			$customerInvoiceRecord->deliveryOrgName = "Acme Industries";
			$customerInvoiceRecord->deliveryContact = "Jane Doe";

			//set billing address that the invoice is billed to for payment
			$customerInvoiceRecord->billingAddress1 = "43";
			$customerInvoiceRecord->billingAddress2 = " Smith Street";
			$customerInvoiceRecord->billingAddress3 = "Melbourne";
			$customerInvoiceRecord->billingRegionName = "Victoria";
			$customerInvoiceRecord->billingCountryName = "Australia";
			$customerInvoiceRecord->billingPostcode = "3000";
			$customerInvoiceRecord->billingOrgName = "Supplier Industries International";
			$customerInvoiceRecord->billingContact = "Lee Lang";
			
			echo "<div>teste<div>";

			//create an array of customer invoice lines
			$invoiceLines = array();

			//create invoice line record 1
			$invoicedProduct = new ESDRecordCustomerInvoiceLine();
			$invoicedProduct->lineType = ESDocumentConstants::INVOICE_LINE_TYPE_PRODUCT;
			$invoicedProduct->productCode = "ACME-SUPPLIER-TTGREEN";
			$invoicedProduct->productName = "Green tea towel - 30 x 6 centimetres";
			$invoicedProduct->keySellUnitID = "2";
			$invoicedProduct->unitName = "EACH";
			$invoicedProduct->quantityInvoiced = 4;
			$invoicedProduct->sellUnitBaseQuantity = 4;
			$invoicedProduct->priceExTax = 5.00;
			$invoicedProduct->priceIncTax = 5.50;
			$invoicedProduct->priceTax = 0.50;
			$invoicedProduct->priceTotalIncTax = 22.00;
			$invoicedProduct->priceTotalExTax = 20.00;
			$invoicedProduct->priceTotalTax = 2.00;
			//optionally specify customer's product code in purchaseOrderProductCode if it is different to the line's productCode field and the supplier org. knows the customer's codes
			$invoicedProduct->purchaseOrderProductCode = "TEA-TOWEL-GREEN";

			//add tax details to the product invoice line
			$productTax = new ESDRecordInvoiceLineTax();
			$productTax->priceTax = $invoicedProduct->priceTax;
			$productTax->priceTotalTax = $invoicedProduct->priceTotalTax;
			$productTax->quantity = $invoicedProduct->quantityInvoiced;
			$productTax->taxRate = 10.00;
			$productTax->taxcode = "GST";
			$productTax->taxcodeLabel = "Goods And Services Tax";
			$invoicedProduct->taxes = array();
			array_push($invoicedProduct->taxes, $productTax);
			
			//add 1st invoice line to lines list
			array_push($invoiceLines, $invoicedProduct);

			//add a 2nd invoice line record that is a text line
			$invoicedProduct = new ESDRecordCustomerInvoiceLine();
			$invoicedProduct->lineType = ESDocumentConstants::INVOICE_LINE_TYPE_TEXT;
			$invoicedProduct->textDescription = "Please bundle tea towels into a box";
			array_push($invoiceLines, $invoicedProduct);

			//add a 3rd invoice line product record to the invoice
			$invoicedProduct = new ESDRecordCustomerInvoiceLine();
			$invoicedProduct->lineType = ESDocumentConstants::INVOICE_LINE_TYPE_PRODUCT;
			$invoicedProduct->productCode = "ACME-TTBLUE";
			$invoicedProduct->quantityInvoiced = 10;
			$invoicedProduct->priceExTax = 10.00;
			$invoicedProduct->priceIncTax = 1.10;
			$invoicedProduct->priceTax = 1.00;
			$invoicedProduct->priceTotalIncTax = 110.00;
			$invoicedProduct->priceTotalExTax = 100.00;
			$invoicedProduct->priceTotalTax = 10.00;
			array_push($invoiceLines, $invoicedProduct);

			//add lines to the invoice
			$customerInvoiceRecord->lines = $invoiceLines;

			//set invoice totals
			$customerInvoiceRecord->totalPriceIncTax = 132.00;
			$customerInvoiceRecord->totalPriceExTax = 120.00;
			$customerInvoiceRecord->totalTax = 12.00;
			$customerInvoiceRecord->totalLines = count($invoiceLines);
			$customerInvoiceRecord->totalProducts = 2;

			//create customer invoices records list and add customer invoice to it
			$customerInvoiceRecords = array();
			array_push($customerInvoiceRecords, $customerInvoiceRecord);
			
			//after 60 seconds give up on waiting for a response from the API when sending the invoice
			$timeoutMilliseconds = 60000;
			
			//create customer invocie Ecommerce Standards document and add customer invoice records to the document
			$customerInvoiceESD = new ESDocumentCustomerInvoice(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $customerInvoiceRecords, array());

			//send customer invoice document to the API for sending onto the customer organisation
			$endpointResponseESD = APIv1EndpointOrgSendCustomerInvoiceToCustomer::call($apiOrgSession, $timeoutMilliseconds, $customerOrgID, $supplierAccountCode, $customerInvoiceESD);
			
			$esDocumentSupplierInvoice = $endpointResponseESD->esDocument;
			
			//check the result of sending the invoices
			if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
				$result = "SUCCESS";
				$resultMessage = "Organisation customer invocies have successfully been sent to customer organisation.";
				
				//iterate through each of the returned supplier invoices and output the details of the supplier invoices
				if($esDocumentSupplierInvoice->dataRecords != null){
					foreach($esDocumentSupplierInvoice->dataRecords as &$supplierInvoiceRecord)
					{								
						$resultMessage = $resultMessage . "<br/><br/>Supplier Invoice Returned, Invoice Details: <br/>";
						$resultMessage = $resultMessage . "Supplier Invoice Code: " . $supplierInvoiceRecord->supplierInvoiceCode . "<br/>";
						$resultMessage = $resultMessage . "Supplier Invoice Total Cost: " . $supplierInvoiceRecord->totalPriceIncTax . " (" . $supplierInvoiceRecord->currencyISOCode . ")" . "<br/>";
						$resultMessage = $resultMessage . "Supplier Invoice Total Taxes: " . $supplierInvoiceRecord->totalTax . " (" . $supplierInvoiceRecord->currencyISOCode . ")" . "<br/>";
						$resultMessage = $resultMessage . "Supplier Invoice Supplier Account: " . $supplierInvoiceRecord->supplierAccountCode . "<br/>";
						$resultMessage = $resultMessage . "Supplier Invoice Total Lines: " . $supplierInvoiceRecord->totalLines;
					}
				}
			}else{
				$result = "FAIL";
				$resultMessage = "Organisation customer invoices failed to be processed. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
				
				//if one or more lines in the customer invoice could not match a product for the customer organisation then find out the invoice lines caused the problem
				if($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INVOICE_LINE_NOT_MAPPED && $esDocumentSupplierInvoice != null)
				{
					//get a list of invoice lines that could not be mapped
					$unmappedLines = APIv1EndpointOrgSendCustomerInvoiceToCustomer::getUnmappedInvoiceLines($esDocumentSupplierInvoice);
					
					//iterate through each unmapped invoice line
					foreach($unmappedLines as $invoiceIndex => $lineIndex)
					{								
						//check that the invoice can be found that contains the problematic line
						if($invoiceIndex < count($customerInvoiceESD->dataRecords) && $lineIndex < count($customerInvoiceESD->dataRecords[$invoiceIndex]->lines)){
							$resultMessage = $resultMessage . "<br/>For customer invoice: " . $customerInvoiceESD->dataRecords[$invoiceIndex]->customerInvoiceCode . " a matching customer product for line number: " . ($lineIndex+1) . " could not be found.";
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

### Send Delivery Notice to Customer Endpoint

The SQUIZZ.com platform's API has an endpoint that allows an orgnisation to send delivery notices (also known as shipping notices, freight notices, advanced shipping notices) for goods it having delivered to a customer, notifying where the ordered goods are being handled in the dispatch and delivery/shipping process. 
This endpoint allows a supplier organisation to automate the sending out of delivery notices to its customers, allowing either individuals ordering in squizz to receive these notices, as well as allow customer organisations to automate the receiving of delivery notices and importing them back into their own systems.
Many delivery notices may be sent for the same delivery of ordered goods, containing a status and message outlining where the goods are currently located. This can allow customers to receive many notifications as it progresses. It's up to you to determine how often the customer should be aware of delivery progression.
- The endpoint relies upon a supplier organisations first importing customer accounts within the SQUIZZ.com platform that the delivery notices are associated to.
- If the delivery notices needs to be forwarded onto customer organisations, then endpoint either relies upon a connection first being setup between the supplier and customer organisations within the SQUIZZ.com platform, or the supplying organisation setting up a data adaptor to export the customer delivery notices to the customers external system. The first option is preferred since the supplying org then doesn't need to know what system the customer organisation is running.
- The endpoint has a number of other requirements. See the endpoint documentation for more details on these requirements.

Each delivery notice needs to be imported within a "Ecommerce Standards Document" that contains a record for each delivery notice. Use the Ecommerce Standards library to easily create these documents and records.
It is recommended to only import one delivery notice at a time, since if an array of delivery notice is imported and one notice failed to import, then no other notices in the list will be attempted to import.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section1550](https://www.squizz.com/docs/squizz/Platform-API.html#section1550) for more documentation about the endpoint and its requirements.
See the example below on how the call the Send Delivery Notice To Customer endpoint. Note that a session must first be created in the API before calling the endpoint.

```php
<?php
    require_once __DIR__ . '/../../../../../3rd-party/jsonmapper/JsonMapper/Exception.php';
    
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
        $esdInstallPath = "/opt/squizz/esd-php-library/src/";
        
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
    use squizz\api\v1\endpoint\APIv1EndpointOrgSendDeliveryNoticeToCustomer;
    use squizz\api\v1\APIv1OrgSession;
    use squizz\api\v1\APIv1Constants;
    use EcommerceStandardsDocuments\ESDRecordDeliveryNotice;
    use EcommerceStandardsDocuments\ESDocumentConstants;
    use EcommerceStandardsDocuments\ESDocumentDeliveryNotice;
    
    //obtain or load in an organisation's API credentials, in this example from command line arguments
    $orgID = $_GET["orgID"];
    $orgAPIKey = $_GET["orgAPIKey"];
    $orgAPIPass = $_GET["orgAPIPass"];
    $customerOrgID = $_GET["customerOrgID"];
    $supplierAccountCode = $_GET["supplierAccountCode"];
    $useDeliveryNoticeExport = ($_GET["useDeliveryNoticeExport"] == ESDocumentConstants::ESD_VALUE_YES? true: false);
    
    $sessionTimeoutMilliseconds = 60000;
    
    echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
    
    //create an API session instance
    $apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
    
    //call the platform's API to request that a session is created
    $endpointResponse = $apiOrgSession->createOrgSession();
    
    //check if the organisation's credentials were correct and that a session was created in the platform's API
    $result = "FAIL";
    $resultMessage = "";
    if($endpointResponse->result != APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
    {
        //session failed to be created
        $resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
    }
    
    //send the delivery notice if the API was successfully created
    if($apiOrgSession->sessionExists())
    {
        //create customer invoice record to import
        $deliveryNoticeRecord = new ESDRecordDeliveryNotice();

        //set data within the delivery notice
        $deliveryNoticeRecord->keyDeliveryNoticeID = "DN123";
        $deliveryNoticeRecord->deliveryNoticeCode = "CUSDELNUM-123-A";
        $deliveryNoticeRecord->deliveryStatus = ESDocumentConstants::DELIVERY_STATUS_IN_TRANSIT;
        $deliveryNoticeRecord->deliveryStatusMessage = "Currently en-route to receiver.";

        //set information about the freight carrier currently performing the delivery
        $deliveryNoticeRecord->freightCarrierName = "ACME Freight Logistics Inc.";
        $deliveryNoticeRecord->freightCarrierCode = "ACFLI";
        $deliveryNoticeRecord->freightCarrierTrackingCode = "34320-ACFLI-34324-234";
        $deliveryNoticeRecord->freightCarrierAccountCode = "VIP00012";
        $deliveryNoticeRecord->freightCarrierConsignCode = "42343-242344";
        $deliveryNoticeRecord->freightCarrierServiceCode = "SUPER-SMART-FREIGHT-FACILITATOR";
        $deliveryNoticeRecord->freightSystemRefCode = "SSFF-3421";

        // add references to other records (sales order, customer invoice, purchase order, customer account) that this delivery is associated to
        $deliveryNoticeRecord->keyCustomerInvoiceID = "4";
        $deliveryNoticeRecord->customerInvoiceCode = "CINV-22";
        $deliveryNoticeRecord->customerInvoiceNumber = "22";
        $deliveryNoticeRecord->keySalesOrderID = "121-332";
        $deliveryNoticeRecord->salesOrderCode = "SSO-332-ABC";
        $deliveryNoticeRecord->salesOrderNumber = "332";
        $deliveryNoticeRecord->purchaseOrderCode = "PO-345";
        $deliveryNoticeRecord->purchaseOrderNumber = "345";
        $deliveryNoticeRecord->instructions = "Please leave goods via the back driveway";
        $deliveryNoticeRecord->keyCustomerAccountID = "1";

        // set where the delivery is currently located geographically
        $deliveryNoticeRecord->atGeographicLocation = ESDocumentConstants::ESD_VALUE_YES;
        $deliveryNoticeRecord->locationLatitude = -37.8277324706811;
        $deliveryNoticeRecord->locationLongitude = 144.92382897158126;

        //create delivery notice records list and add delivery notice to it
        $deliveryNoticeRecords = array();
        array_push($deliveryNoticeRecords, $deliveryNoticeRecord);
        
        //after 60 seconds give up on waiting for a response from the API when sending the delivery notice
        $timeoutMilliseconds = 60000;
        
        //create delivery notice Ecommerce Standards document and add delivery notice records to the document
        $deliveryNoticeESD = new ESDocumentDeliveryNotice(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $deliveryNoticeRecords, array());

        //send delivery notice document to the API for sending onto the customer organisation
        $endpointResponseESD = APIv1EndpointOrgSendDeliveryNoticeToCustomer::call($apiOrgSession, $timeoutMilliseconds, $customerOrgID, $supplierAccountCode, $useDeliveryNoticeExport, $deliveryNoticeESD);
        
        //check the result of sending the delivery notice
        if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
            $result = "SUCCESS";
            $resultMessage = "Organisation delivery notice(s) have successfully been sent to customer.";
            
        }else{
            $result = "FAIL";
            $resultMessage = "Organisation delivery notice failed to be processed. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
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
	
	//obtain or load in an organisation's API credentials, in this example from URL parameters
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
When importing one type of organisational data, it is important to import the full data set, otherwise the platform will deactivate non-imported data.
For example if 3 products are imported, then another products import is run that only imports 2 records, then other 1 product will become deactivated and no longer be able to be sold.
Read [https://www.squizz.com/docs/squizz/Platform-API-Endpoint:-Import-Organisation-Data.html](https://www.squizz.com/docs/squizz/Platform-API-Endpoint:-Import-Organisation-Data.html) for more documentation about the endpoint and its requirements.
See the example below on how the call the Import Organisation ESD Data endpoint. Note that a session must first be created in the API before calling the endpoint.

Other examples exist in this repository's examples folder on how to import serveral different types of data:
 - Import Taxcodes [APIv1ExampleRunnerImportOrgESDDataTaxcodes.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerImportOrgESDDataTaxcodes.php)
 - Import Categories [APIv1ExampleRunnerImportOrgESDDataCategories.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerImportOrgESDDataCategories.php)
 - Import Attributes [APIv1ExampleRunnerImportOrgESDDataAttributes.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerImportOrgESDDataAttributes.php)
 - Import Makers [APIv1ExampleRunnerImportOrgESDDataMakers.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerImportOrgESDDataMakers.php)
 - Import Maker Models [APIv1ExampleRunnerImportOrgESDDataMakerModels.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerImportOrgESDDataMakerModels.php)
 - Import Maker Model Mappings [APIv1ExampleRunnerImportOrgESDDataMakerModelMappings.php](https://github.com/squizzdotcom/squizz-platform-api-php-library/tree/master/test/squizz/api/v1/example/APIv1ExampleRunnerImportOrgESDDataMakerModelMappings.php)

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
	use squizz\api\v1\endpoint\APIv1EndpointOrgImportESDocument;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	use EcommerceStandardsDocuments\ESDocumentTaxcode;
	use EcommerceStandardsDocuments\ESDRecordTaxcode;
	use EcommerceStandardsDocuments\ESDocumentConstants;

	//obtain or load in an organisation's API credentials, in this example from URL parameters
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

## Import Organisation Sales Order Endpoint
The SQUIZZ.com platform's API has an endpoint that allows an organisation to import a sales order into the SQUIZZ.com platform, against its own organisation.
This endpoint is typically used by an organisation to import sales orders from any systems, websites or services it uses to capture sales orders from, including Ecommerce websites, online marketplaces, Customer Relationship Management (CRM) systems, quoting software tools, or any other business systems and software. 
Note that this endpoint should not be used by customer organisations to send orders to supplying organisations. For that use case the [Send and Procure Purchase Order From Supplier Endpoint](#send-and-procure-purchase-order-from-supplier-endpoint) should be called instead.
When calling the endpoint there is an parameter that can optionally allow the order to be re-priced or not. This allows the most up-to-date pricing to be set in the sales order when imported.
Each sales order needs to be imported as an "Ecommerce Standards Document" that contains one or more records. Use the Ecommerce Standards library to easily create the Sales Order documents and Sales Order records.
Read [https://www.squizz.com/docs/squizz/Platform-API-Endpoint:-Import-Organisation-Sales-Order.html](https://www.squizz.com/docs/squizz/Platform-API-Endpoint:-Import-Organisation-Sales-Order.html) for more documentation about the endpoint and its requirements.
See the example below on how the call the Import Organisation Sales Order ESD Data endpoint. Note that a session must first be created in the API before calling the endpoint.

![alt tag](https://attach.squizz.com/doc_centre/1/files/images/masters/squizz-platform-api-import-sales-order-diagram[130].png)

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
	use squizz\api\v1\endpoint\APIv1EndpointOrgImportSalesOrder;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\APIv1Constants;
	use EcommerceStandardsDocuments\ESDRecordOrderSale;
	use EcommerceStandardsDocuments\ESDRecordOrderSaleLine;
	use EcommerceStandardsDocuments\ESDRecordOrderLineTax;
	use EcommerceStandardsDocuments\ESDRecordOrderSurcharge;
	use EcommerceStandardsDocuments\ESDRecordOrderPayment;
	use EcommerceStandardsDocuments\ESDocumentConstants;
	use EcommerceStandardsDocuments\ESDocumentOrderSale;
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$rePriceOrder = $_GET["rePriceOrder"];
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
	
	//create and send sales order if the API was successfully created
	if($apiOrgSession->sessionExists())
	{
		//create sales order record to import
		$salesOrderRecord = new ESDRecordOrderSale();
		
		//set data within the sales order
		$salesOrderRecord->keySalesOrderID = "111";
		$salesOrderRecord->salesOrderCode = "SOEXAMPLE-678";
		$salesOrderRecord->salesOrderNumber = "678";
		$salesOrderRecord->instructions = "Leave goods at the back entrance";
		//$salesOrderRecord->keyCustomerAccountID = "3";
		$salesOrderRecord->keyCustomerAccountID = "1";
		$salesOrderRecord->customerAccountCode = "CUS-003";
		$salesOrderRecord->customerAccountName = "Acme Industries";
		$salesOrderRecord->customerEntity = ESDocumentConstants::ENTITY_TYPE_ORG;
		$salesOrderRecord->customerOrgName = "Acme Industries Pty Ltd";

		//set delivery address that ordered goods will be delivered to
		$salesOrderRecord->deliveryAddress1 = "32";
		$salesOrderRecord->deliveryAddress2 = "Main Street";
		$salesOrderRecord->deliveryAddress3 = "Melbourne";
		$salesOrderRecord->deliveryRegionName = "Victoria";
		$salesOrderRecord->deliveryCountryName = "Australia";
		$salesOrderRecord->deliveryPostcode = "3000";
		$salesOrderRecord->deliveryOrgName = "Acme Industries";
		$salesOrderRecord->deliveryContact = "Jane Doe";

		//set billing address that the order will be billed to for payment
		$salesOrderRecord->billingAddress1 = "43";
		$salesOrderRecord->billingAddress2 = " High Street";
		$salesOrderRecord->billingAddress3 = "Melbourne";
		$salesOrderRecord->billingRegionName = "Victoria";
		$salesOrderRecord->billingCountryName = "Australia";
		$salesOrderRecord->billingPostcode = "3000";
		$salesOrderRecord->billingOrgName = "Acme Industries International";
		$salesOrderRecord->billingContact = "John Citizen";
		
		//create an array of sales order lines
		$orderLines = array();
		
		//create sales order line record
		$orderProduct = new ESDRecordOrderSaleLine();
		$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_PRODUCT;
		//set mandatory data in line fields
		$orderProduct->productCode = "TEA-TOWEL-GREEN";
		$orderProduct->productName = "Green tea towel - 30 x 6 centimetres";
		$orderProduct->quantity = 4;
		$orderProduct->keySellUnitID = "EA";
		$orderProduct->unitName = "EACH";
		$orderProduct->sellUnitBaseQuantity = 4;
		//pricing data only needs to be set if the order isn't being repriced
		$orderProduct->priceExTax = 5.00;
		$orderProduct->priceIncTax = 5.50;
		$orderProduct->priceTax = 0.50;
		$orderProduct->priceTotalIncTax = 22.00;
		$orderProduct->priceTotalExTax = 20.00;
		$orderProduct->priceTotalTax = 2.00;
		
		//add order line to lines list
		array_push($orderLines, $orderProduct);
		
		//add taxes to the line
		$orderLineTaxes = array();
		$orderProduct->taxes = $orderLineTaxes;
		$orderProductTax = new ESDRecordOrderLineTax();
		$orderProductTax->keyTaxcodeID = "TAXCODE-1";
		$orderProductTax->taxcode = "GST";
		$orderProductTax->taxcodeLabel = "Goods And Services Tax";
		//pricing data only needs to be set if the order isn't being repriced
		$orderProductTax->priceTax = 0.50;
		$orderProductTax->taxRate = 10;
		$orderProductTax->quantity = 4;
		$orderProductTax->priceTotalTax = 2.00;
		array_push($orderLineTaxes, $orderProductTax);
		
		//add a 2nd sales order line record that is a text line
		$orderProduct = new ESDRecordOrderSaleLine();
		$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_TEXT;
		$orderProduct->textDescription = "Please bundle tea towels into a box";
		array_push($orderLines, $orderProduct);
		
		//add a 3rd sales order line record
		$orderProduct = new ESDRecordOrderSaleLine();
		$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_PRODUCT;
		$orderProduct->productCode = "TEA-TOWEL-BLUE";
		$orderProduct->productName = "Blue tea towel - 30 x 6 centimetres";
		$orderProduct->unitName = "BOX";
		$orderProduct->keySellUnitID = "BOX-OF-10";
		$orderProduct->quantity = 2;
		$orderProduct->sellUnitBaseQuantity = 20;
		//pricing data only needs to be set if the order isn't being repriced
		$orderProduct->priceExTax = 10.00;
		$orderProduct->priceIncTax = 11.00;
		$orderProduct->priceTax = 1.00;
		$orderProduct->priceTotalIncTax = 22.00;
		$orderProduct->priceTotalExTax = 20.00;
		$orderProduct->priceTotalTax = 2.00;
		array_push($orderLines, $orderProduct);
		
		//add taxes to the line
		$orderLineTaxes = array();
		$orderProduct->taxes = $orderLineTaxes;
		$orderProductTax = new ESDRecordOrderLineTax();
		$orderProductTax->keyTaxcodeID = "TAXCODE-1";
		$orderProductTax->taxcode = "GST";
		$orderProductTax->taxcodeLabel = "Goods And Services Tax";
		//pricing data only needs to be set if the order isn't being repriced
		$orderProductTax->priceTax = 1.00;
		$orderProductTax->taxRate = 10;
		$orderProductTax->quantity = 2;
		$orderProductTax->priceTotalTax = 2.00;
		array_push($orderLineTaxes, $orderProductTax);
		
		//add order lines to the order
		$salesOrderRecord->lines = $orderLines;
		
		//create an array of sales order surcharges
		$orderSurcharges = array();
		
		//create sales order surcharge record
		$orderSurcharge = new ESDRecordOrderSurcharge();
		$orderSurcharge->surchargeCode = "FREIGHT-FEE";
		$orderSurcharge->surchargeLabel = "Freight Surcharge";
		$orderSurcharge->surchargeDescription = "Cost of freight delivery";
		$orderSurcharge->keySurchargeID = "SURCHARGE-1";
		//pricing data only needs to be set if the order isn't being repriced
		$orderSurcharge->priceExTax = 3.00;
		$orderSurcharge->priceIncTax = 3.30;
		$orderSurcharge->priceTax = 0.30;
		array_push($orderSurcharges, $orderSurcharge);
		
		//add taxes to the surcharge
		$orderSurchargeTaxes = array();
		$orderSurcharge->taxes = $orderSurchargeTaxes;
		$orderSurchargeTax = new ESDRecordOrderLineTax();
		$orderSurchargeTax->keyTaxcodeID = "TAXCODE-1";
		$orderSurchargeTax->taxcode = "GST";
		$orderSurchargeTax->taxcodeLabel = "Goods And Services Tax";
		$orderSurchargeTax->quantity = 1;
		//pricing data only needs to be set if the order isn't being repriced
		$orderSurchargeTax->priceTax = 0.30;
		$orderSurchargeTax->taxRate = 10;
		$orderSurchargeTax->priceTotalTax = 0.30;
		array_push($orderSurchargeTaxes, $orderSurchargeTax);
		
		//create 2nd sales order surcharge record
		$orderSurcharge = new ESDRecordOrderSurcharge();
		$orderSurcharge->surchargeCode = "PAYMENT-FEE";
		$orderSurcharge->surchargeLabel = "Credit Card Surcharge";
		$orderSurcharge->surchargeDescription = "Cost of Credit Card Payment";
		$orderSurcharge->keySurchargeID = "SURCHARGE-2";
		//pricing data only needs to be set if the order isn't being repriced
		$orderSurcharge->priceExTax = 5.00;
		$orderSurcharge->priceIncTax = 5.50;
		$orderSurcharge->priceTax = 0.50;
		array_push($orderSurcharges, $orderSurcharge);
		
		//add taxes to the 2nd surcharge
		$orderSurchargeTaxes = array();
		$orderSurcharge->taxes = $orderSurchargeTaxes;
		$orderSurchargeTax = new ESDRecordOrderLineTax();
		$orderSurchargeTax->keyTaxcodeID = "TAXCODE-1";
		$orderSurchargeTax->taxcode = "GST";
		$orderSurchargeTax->taxcodeLabel = "Goods And Services Tax";
		//pricing data only needs to be set if the order isn't being repriced
		$orderSurchargeTax->priceTax = 0.50;
		$orderSurchargeTax->taxRate = 10;
		$orderSurchargeTax->quantity = 1;
		$orderSurchargeTax->priceTotalTax = 5.00;
		array_push($orderSurchargeTaxes, $orderSurchargeTax);
		
		//add surcharges to the order
		$salesOrderRecord->surcharges = $orderSurcharges;
		
		//create an array of sales order payments
		$orderPayments = array();
		
		//create sales order payment record
		$orderPayment = new ESDRecordOrderPayment();
		$orderPayment->paymentMethod = ESDocumentConstants::PAYMENT_METHOD_CREDIT;
		$orderPayment->paymentProprietaryCode = "Freight Surcharge";
		$orderPayment->paymentReceipt = "3422ads2342233";
		$orderPayment->keyPaymentTypeID = "VISA-CREDIT-CARD";
		$orderPayment->paymentAmount = 22.80;
		array_push($orderPayments, $orderPayment);
		
		//create 2nd sales order payment record
		$orderPayment = new ESDRecordOrderPayment();
		$orderPayment->paymentMethod = ESDocumentConstants::PAYMENT_METHOD_PROPRIETARY;
		$orderPayment->paymentProprietaryCode = "PAYPAL";
		$orderPayment->paymentReceipt = "2323432341231";
		$orderPayment->keyPaymentTypeID = "PP";
		$orderPayment->paymentAmount = 30.00;
		array_push($orderPayments, $orderPayment);
		
		//add payments to the order and set overall payment details
		$salesOrderRecord->payments = $orderPayments;
		$salesOrderRecord->paymentAmount = 41.00;
		$salesOrderRecord->paymentStatus = ESDocumentConstants::PAYMENT_STATUS_PAID;
		
		//set order totals, pricing data only needs to be set if the order isn't being repriced
		$salesOrderRecord->totalPriceIncTax = 52.80;
		$salesOrderRecord->totalPriceExTax = 48.00;
		$salesOrderRecord->totalTax = 4.80;
		$salesOrderRecord->totalSurchargeExTax = 8.00;
		$salesOrderRecord->totalSurchargeIncTax = 8.80;
		$salesOrderRecord->totalSurchargeTax = 8.00;
	
		//create sales order records list and add sales order to it
		$salesOrderRecords = array();
		array_push($salesOrderRecords, $salesOrderRecord);
		
		//after 60 seconds give up on waiting for a response from the API when importing the order
		$timeoutMilliseconds = 60000;
		
		//create sales order Ecommerce Standards document and add sales order records to the document
		$orderSaleESD = new ESDocumentOrderSale(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $salesOrderRecords, array());

		//send sales order document to the API for importing agqainst the organisation
		$endpointResponseESD = APIv1EndpointOrgImportSalesOrder::call($apiOrgSession, $timeoutMilliseconds, $orderSaleESD, ($rePriceOrder == ESDocumentConstants::ESD_VALUE_YES));
		$esDocumentOrderSale = $endpointResponseESD->esDocument;
		
		//check the result of importing the sales order
		if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
			$result = "SUCCESS";
			$resultMessage = "Organisation sales order(s) have successfully been imported against the organisation.";
			
			//iterate through each of the returned sales orders and output the details of the sales order(s)
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
			$resultMessage = "Organisation sales order(s) failed to be processed. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
			
			//if one or more products in the sales order could not match a product for the organisation then find out the order lines that caused the problem
			if($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_PRODUCT_NOT_MATCHED && $esDocumentOrderSale != null)
			{
				//get a list of order lines that could not be mapped
				$unmatchedLines = APIv1EndpointOrgImportSalesOrder::getUnmatchedOrderLines($esDocumentOrderSale);
				
				//iterate through each unmatched order line
				foreach($unmatchedLines as $orderIndex => $lineIndex)
				{								
					//check that the order can be found that contains the problematic line
					if($orderIndex < count($orderSaleESD->dataRecords) && $lineIndex < count($orderSaleESD->dataRecords[$orderIndex]->lines)){
						$resultMessage = $resultMessage . "<br/>For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " a matching product for line number: " . ($lineIndex+1) . " could not be found.";
					}
				}
			}
			//if one or more products in the sales order could not be priced for the organisation then find the order line that caused the problem
			elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_LINE_PRICING_MISSING && $esDocumentOrderSale != null)
			{
				//get a list of order lines that could not be priced
				$unpricedLines = APIv1EndpointOrgImportSalesOrder::getUnpricedOrderLines($esDocumentOrderSale);

				//iterate through each unpriced order line
				foreach($unpricedLines as $orderIndex => $lineIndex)
				{
					//check that the order can be found that contains the problematic line
					if($orderIndex < count($orderSaleESD->dataRecords) && $lineIndex < count($orderSaleESD->dataRecords[$orderIndex]->lines)){
						$resultMessage = $resultMessage . "For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " no pricing found for line number: " . ($lineIndex+1);
					}
				}
			}
			//if one or more surcharges in the sales order could not match a surcharge for the organisation then find out the order surcharge that caused the problem
			elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_SURCHARGE_NOT_FOUND && $esDocumentOrderSale != null)
			{
				//get a list of order surcharges that could not be matched
				$unmatchedSurcharges = APIv1EndpointOrgImportSalesOrder::getUnmatchedOrderSurcharges($esDocumentOrderSale);
				
				//iterate through each unmatched order surcharge
				foreach($unmatchedSurcharges as $orderIndex => $surchargeIndex)
				{								
					//check that the order can be found that contains the problematic surcharge
					if($orderIndex < count($orderSaleESD->dataRecords) && $surchargeIndex < count($orderSaleESD->dataRecords[$orderIndex]->surcharges)){
						$resultMessage = $resultMessage . "<br/>For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " a matching surcharge for surcharge number: " . ($surchargeIndex+1) . " could not be found.";
					}
				}
			}
			//if one or more surcharges in the sales order could not be priced then find the order surcharge that caused the problem
			elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_SURCHARGE_PRICING_MISSING && $esDocumentOrderSale != null)
			{
				//get a list of order lines that could not be priced
				$unpricedSurcharges = APIv1EndpointOrgImportSalesOrder::getUnpricedOrderSurcharges($esDocumentOrderSale);

				//iterate through each unpriced order surcharge
				foreach($unpricedSurcharges as $orderIndex => $surchargeIndex)
				{
					//check that the order can be found that contains the problematic surcharge
					if($orderIndex < count($orderSaleESD->dataRecords) && $surchargeIndex < count($orderSaleESD->dataRecords[$orderIndex]->surcharges)){
						$resultMessage = $resultMessage . "For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " no pricing found for surcharge number: " . ($surchargeIndex+1);
					}
				}
			}
			//if one or more surcharges in the sales order could not match a surcharge for the organisation then find out the order surcharge that caused the problem
			elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_PAYMENT_NOT_MATCHED && $esDocumentOrderSale != null)
			{
				//get a list of order payments that could not be matched
				$unmatchedPayments = APIv1EndpointOrgImportSalesOrder::getUnmatchedOrderPayments($esDocumentOrderSale);
				
				//iterate through each unmatched order payment
				foreach($unmatchedPayments as $orderIndex => $paymentIndex)
				{								
					//check that the order can be found that contains the problematic payment
					if($orderIndex < count($orderSaleESD->dataRecords) && $paymentIndex < count($orderSaleESD->dataRecords[$orderIndex]->payments)){
						$resultMessage = $resultMessage . "<br/>For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " a matching payment type for payment number: " . ($paymentIndex+1) . " could not be found.";
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
	
	
	//obtain or load in an organisation's API credentials, in this example from URL parameters
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
	
	
	//obtain or load in an organisation's API credentials, in this example from URL parameters
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
	
	
	//obtain or load in an organisation's API credentials, in this example from URL parameters
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
	
	
	//obtain or load in an organisation's API credentials, in this example from URL parameters
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