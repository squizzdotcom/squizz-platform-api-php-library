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
			<h1>Retrieve Product Pricing Organisation Data API Example</h1>
			<p style="text-align: left; max-width: 607px; margin: 0 auto;">
				Tests making a request to the SQUIZZ.com API to create a session for an organisation, then makes a call to the API to retrieve Product Pricing data from a chosen organisation.<br/><br/>
			</p>
			
			<div style="max-width: 607px; background-color: #2b2b2b; color: #cacaca; text-align: center; margin: auto; padding-top: 15px;">
				<?php
					/**
					* Copyright (C) Squizz PTY LTD
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
					use squizz\api\v1\APIv1Util;
					use EcommerceStandardsDocuments\ESDocumentConstants;
					use EcommerceStandardsDocuments\ESDocument;
					use EcommerceStandardsDocuments\ESDRecordProduct;
					use EcommerceStandardsDocuments\ESDocumentProduct;
					use EcommerceStandardsDocuments\ESDRecordPrice;
					use EcommerceStandardsDocuments\ESDocumentPrice;
					
					//obtain or load in an organisation's API credentials, in this example from command line arguments
					$orgID = $_GET["orgID"];
					$orgAPIKey = $_GET["orgAPIKey"];
					$orgAPIPass = $_GET["orgAPIPass"];
					$supplierOrgID = $_GET["supplierOrgID"];
					$customerAccountCode = $_GET["customerAccountCode"];
					$priceType = $_GET["priceType"];
					$sessionTimeoutMilliseconds = 60000;
					$recordsMaxAmount = 5000;
					$recordsStartIndex = 0;
					$getMoreRecords = true;
					$recordNumber = 1;
					$resultMessage = "";
					$productsRecordIndex = array();
					$productRRPricingRecordIndex = array();
					$recordsUpdatedAfterDateTimeMilliseconds = APIv1EndpointOrgRetrieveESDocument::RETRIEVE_ALL_RECORDS_DATE_TIME_MILLISECONDS;

					//to limit only retrieving records updated after a specific date time then uncomment this line and set date
					//$filterRecordsAfterDateTime = new DateTimeImmutable("2023-02-07 02:58:58", new DateTimeZone("Australia/Melbourne"));
					//$recordsUpdatedAfterDateTimeMilliseconds = $filterRecordsAfterDateTime->getTimestamp() * 1000;
					
					echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
					
					//create an API session instance
					$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
					
					//call the platform's API to request that a session is created
					$endpointResponse = $apiOrgSession->createOrgSession();
					
					//check if the organisation's credentials were correct and that a session was created in the platform's API
					$result = "FAIL";
					$resultMessage = "";
					if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
					}else{
						//session failed to be created
						$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
					}
					
					//retrieve organisation product data first
					if($apiOrgSession->sessionExists())
					{
						$result = "FAIL";
						$recordsStartIndex = 0;
						$getMoreRecords = true;
						
						//after 120 seconds give up on waiting for a response from the API
						$timeoutMilliseconds = 120000;
						
						//get the next page of records if needed
						while($getMoreRecords)
						{
							//call the platform's API to retrieve the organisation's product data
							$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRODUCTS, $supplierOrgID, $customerAccountCode, $recordsMaxAmount, $recordsStartIndex, $recordsUpdatedAfterDateTimeMilliseconds);
							$getMoreRecords = false;
							$esDocument = $endpointResponseESD->esDocument;
				
							//check that the data successfully retrieved
							if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
							{
								//check that records have been placed into the standards document
								if($esDocument->dataRecords != null)
								{
									//iterate through each product record stored within the standards document and index
									foreach ($esDocument->dataRecords as $productRecord){
										$productsRecordIndex[$productRecord->keyProductID] = $productRecord;
									}
									
									//check if there are more records to retrieve
									if(sizeof($esDocument->dataRecords) >= $recordsMaxAmount){
										$recordsStartIndex = $recordsStartIndex + $recordsMaxAmount;
										$getMoreRecords = true;
									}else{
										$result = "SUCCESS";
									}
								}else{
									$result = "SUCCESS";
								}
							}else{
								$result = "FAIL";
								$resultMessage = "Organisation Product data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							}
						}

						//next retrieve and index product recommended retail pricing (RRP) data if the product data was successfully retrieved
						if($result=="SUCCESS")
						{
							$result = "FAIL";
							$recordsStartIndex = 0;
							$getMoreRecords = true;
							$getRecommendedRetailPricing = true;
							
							//after 120 seconds give up on waiting for a response from the API
							$timeoutMilliseconds = 120000;
							
							//get the next page of records if needed
							while($getMoreRecords)
							{
								//call the platform's API to retrieve the organisation's category data
								$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRICING, $supplierOrgID, $customerAccountCode, $recordsMaxAmount, $recordsStartIndex, $recordsUpdatedAfterDateTimeMilliseconds, $getRecommendedRetailPricing);
								$getMoreRecords = false;
								$esDocument = $endpointResponseESD->esDocument;
								
								//check that the data successfully retrieved
								if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
								{
									//check that records have been placed into the standards document
									if($esDocument->dataRecords != null)
									{
										//iterate through each pricing record stored within the standards document and index
										foreach ($esDocument->dataRecords as $pricingRecord){
											//index the pricing record using both the product and sell unit's key identifier since pricing may be set for multiple sell units of a product
											$productRRPricingRecordIndex[$pricingRecord->keyProductID.$pricingRecord->keySellUnitID] = $pricingRecord;
										}
										
										//check if there are more records to retrieve
										if(sizeof($esDocument->dataRecords) >= $recordsMaxAmount){
											$recordsStartIndex = $recordsStartIndex + $recordsMaxAmount;
											$getMoreRecords = true;
										}else{
											$result = "SUCCESS";
										}
									}else{
										$result = "SUCCESS";
									}
								}else{
									// the requesting organisation may not have permission to obtain rrp pricing, if no rrp price-level has been set by the supplying organisation, if so continue on
									if($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_PERMISSION_DENIED){
										$result = "SUCCESS";
									}else{
										$result = "FAIL";
										$resultMessage = "Organisation Product Recommended Retail Pricing data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
									}
								}
							}
						}

						//retrieve organisation product pricing data and display pricing records with core product data
						if($result=="SUCCESS")
						{
							$result = "FAIL";
							$recordsStartIndex = 0;
							$getMoreRecords = true;
							$getRecommendedRetailPricing = false;
							
							//after 120 seconds give up on waiting for a response from the API
							$timeoutMilliseconds = 120000;
							
							//get the next page of records if needed
							while($getMoreRecords)
							{
								//call the platform's API to retrieve the organisation's product pricing data
								$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRICING, $supplierOrgID, $customerAccountCode, $recordsMaxAmount, $recordsStartIndex, $recordsUpdatedAfterDateTimeMilliseconds, $getRecommendedRetailPricing, $priceType);
								$getMoreRecords = false;
								$esDocument = $endpointResponseESD->esDocument;
					
								//check that the data successfully retrieved
								if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
								{
									//check that records have been placed into the standards document
									if($esDocument->dataRecords != null)
									{
										//output headers for the first page of records
										if($recordsStartIndex == 0){
											$resultMessage = $resultMessage.
												"Product Pricing data successfully obtained from the platform".
												"<br/>Product Pricing Records:".
												'<table style="width: 100%; display: block; overflow-x: auto;white-space: nowrap;">'.
													"<tr>".
														"<th>#</th>".
														"<th>Key Product ID</th>".
														"<th>Product Code</th>".
														"<th>Quantity</th>".
														"<th>Price</th>".
														"<th>RRP Price</th>".
														"<th>Sell Unit Label</th>".
														"<th>Key Sell Unit ID</th>".
														"<th>Pricing Inclusive Of Tax</th>".
													"</tr>";
										}
										
										//iterate through each price record stored within the standards document
										foreach ($esDocument->dataRecords as $priceRecord)
										{
											$productCode = '';
											$sellUnitLabel = '';
											$rrpPrice = 'NA';
											$isPriceTaxInclusive = 'N';

											//find the product that the price is associated to and get the product name and code
											if(array_key_exists($priceRecord->keyProductID, $productsRecordIndex)){
												$productRecord = $productsRecordIndex[$priceRecord->keyProductID];
												$productCode = $productRecord->productCode;
												$isPriceTaxInclusive = $productRecord->isPriceTaxInclusive;

												//find the sell unit assigned to the price record for the product
												foreach($productRecord->sellUnits as $sellUnit){
													if($sellUnit->keySellUnitID == $productRecord->keySellUnitID){
														$sellUnitLabel = $sellUnit->sellUnitLabel;
														break;
													}
												}
											}

											//check if rrp pricing exists for the product and it's sell unit
											if(array_key_exists($priceRecord->keyProductID.$priceRecord->keySellUnitID, $productRRPricingRecordIndex)){
												$productRRPPricingRecord = $productRRPricingRecordIndex[$priceRecord->keyProductID.$priceRecord->keySellUnitID];
												$rrpPrice = money_format('%.2n', $productRRPPricingRecord->price);
											}

											//output details of the record
											$resultMessage = $resultMessage."<tr>".
												"<td>".$recordNumber."</td>".
												"<td>".htmlentities($priceRecord->keyProductID)."</td>".
												"<td>".htmlentities($productCode)."</td>".
												"<td>".$priceRecord->quantity."</td>".
												"<td>".money_format('%.2n', $priceRecord->price)."</td>".
												"<td>".$rrpPrice."</td>".
												"<td>".htmlentities($sellUnitLabel)."</td>".
												"<td>".htmlentities($priceRecord->keySellUnitID)."</td>".
												"<td>".($isPriceTaxInclusive == 'Y'? 'Yes': 'No')."</td>".
											"</tr>";
											
											$recordNumber++;
										}
										
										//check if there are more records to retrieve
										if(sizeof($esDocument->dataRecords) >= $recordsMaxAmount){
											$recordsStartIndex = $recordsStartIndex + $recordsMaxAmount;
											$getMoreRecords = true;
										}else{
											$resultMessage = $resultMessage."</table>";
											$result = "SUCCESS";
										}
									}
								}else{
									$result = "FAIL";
									$resultMessage = "Organisation Product Pricing data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
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
					echo "<div>Records Returned: <b>$recordNumber</b></div>";
					echo "<div>Message:<div>";
					echo "<div><b>$resultMessage</b><div><br/>";
				?>
			</div>
		</div>
	</body>
</html>