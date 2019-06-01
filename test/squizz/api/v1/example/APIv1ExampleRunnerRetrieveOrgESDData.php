<!DOCTYPE html>
<html>
	<head>
		<style>
			td{
				background: #545454;
				text-align: right;
				padding-right: 5px;
			}
			th{
				background: #1d1d1d;
			}
		</style>
	</head>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Retrieve Organisation Data API Example</h1>
			<p>Tests making a request to the SQUIZZ.com API to create a session for an organisation, then makes a call to the API to retrieve Ecommerce data from a chosen organisation
			<div style="max-width: 607px; background-color: #2b2b2b; color: #cacaca; text-align: center; margin: auto; padding-top: 15px;">
				<?php
					/**
					* Copyright (C) 2019 Squizz PTY LTD
					* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
					* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
					* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
					*/
					
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
			</div>
		</div>
	</body>
</html>