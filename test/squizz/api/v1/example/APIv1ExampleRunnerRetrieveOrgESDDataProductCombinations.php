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
			<h1>Retrieve Product Combination Organisation Data API Example</h1>
			<p style="text-align: left; max-width: 607px; margin: 0 auto;">
				Tests making a request to the SQUIZZ.com API to create a session for an organisation, then makes a call to the API to retrieve Product Combination data from a chosen organisation.<br/><br/>
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
						//$esdInstallPath = "/path/to/esd-php-library/src/";
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
					use EcommerceStandardsDocuments\ESDocumentConstants;
					use EcommerceStandardsDocuments\ESDRecordCombinationProfile;
					use EcommerceStandardsDocuments\ESDRecordCombinationProfileField;
					use EcommerceStandardsDocuments\ESDRecordProductCombination;
					use EcommerceStandardsDocuments\ESDRecordProductCombinationParent;
					use EcommerceStandardsDocuments\ESDRecordProduct;
					use EcommerceStandardsDocuments\ESDocumentProduct;
					use EcommerceStandardsDocuments\ESDocumentProductCombination;
					
					//obtain or load in an organisation's API credentials, in this example from command line arguments
					$orgID = $_GET["orgID"];
					$orgAPIKey = $_GET["orgAPIKey"];
					$orgAPIPass = $_GET["orgAPIPass"];
					$supplierOrgID = $_GET["supplierOrgID"];
					$sessionTimeoutMilliseconds = 60000;
					$recordsMaxAmount = 5000;
					$combinationRecordsMaxAmount = 500;
					$recordsStartIndex = 0;
					$getMoreRecords = true;
					$recordNumber = 1;
					$combinationProfileRecordNumber = 1;
					$combinationProfileFieldRecordNumber = 1;
					$combinationProfileFieldValueRecordNumber = 1;
					$parentCombinationsRecordNumber = 1;
					$resultMessage = "";
					$productsRecordIndex = array();
					$combinationProfilesRecordIndex = array();
					$combinationProfileFieldsRecordIndex = array();
					$combinationProfileFieldValuesRecordIndex = array();
					$combinationsRecordIndex = array();
					$productCombinationsArray = array();
					$productCombinationParentsArray = array();
					
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
					
					//retrieve and index organisation product data if the API session was successfully created
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
							$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRODUCTS, $supplierOrgID, '',$recordsMaxAmount, $recordsStartIndex);
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
									if(sizeof($esDocument->dataRecords) >= $recordsMaxAmount)
									{
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
					}
					
					//retrieve and output organisation product combination data if the product data was sucecssfully obtained
					if($result == "SUCCESS")
					{
						$result = "FAIL";
						$recordsStartIndex = 0;
						$getMoreRecords = true;
						
						//after 120 seconds give up on waiting for a response from the API
						$timeoutMilliseconds = 120000;
						
						//get the next page of records if needed
						while($getMoreRecords)
						{
							//call the platform's API to retrieve the organisation's combination data
							$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRODUCT_COMBINATIONS, $supplierOrgID, '',$combinationRecordsMaxAmount, $recordsStartIndex);
							$getMoreRecords = false;
							$esDocument = $endpointResponseESD->esDocument;
				
							//check that the data successfully retrieved
							if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
							{
								//store combination profiles if the first page of data is being obtained
								if($recordsStartIndex == 0 && $esDocument->combinationProfiles != null)
								{
									$resultMessage = $resultMessage.
										"Organisation data successfully obtained from the platform".
										"<br/>Combination Profile Records:".
										'<table style="width: 100%; display: block; overflow-x: auto;white-space: nowrap;">'.
											"<tr>".
												"<th>Key Combo Profile ID</th>".
												"<th>Profile Name</th>".
												"<th>Key Combo Profile Field ID</th>".
												"<th>Field Name</th>".
												"<th>Key Combo Profile Field Value ID</th>".
												"<th>Field Value</th>".
											"</tr>";
								
									//iterate through each combination profile record stored within the standards document
									foreach ($esDocument->combinationProfiles as $combinationProfileRecord){
										$combinationProfilesRecordIndex[$combinationProfileRecord->keyComboProfileID] = $combinationProfileRecord;
										$combinationProfileRecordNumber++;
										
										//output details of the combination profile record
										$resultMessage = $resultMessage."<tr>".
											"<td>".htmlentities($combinationProfileRecord->keyComboProfileID)."</td>".
											"<td>".htmlentities($combinationProfileRecord->profileName)."</td>".
										"</tr>";
										
										//iterate through each combination field assigned to the combination profile
										foreach ($combinationProfileRecord->combinationFields as $combinationFieldRecord){
											$combinationProfileFieldsRecordIndex[$combinationFieldRecord->keyComboProfileFieldID] = $combinationFieldRecord;
											$combinationProfileFieldRecordNumber++;
											
											//output details of the combination profile field value record
											$resultMessage = $resultMessage."<tr>".
												"<td></td>".
												"<td></td>".
												"<td>".htmlentities($combinationFieldRecord->keyComboProfileFieldID)."</td>".
												"<td>".htmlentities($combinationFieldRecord->fieldName)."</td>".
											"</tr>";

											//iterate through each combination field value assigned to the combination profile field
											for ($i = 0; $i < count($combinationFieldRecord->fieldValues) && $i < count($combinationFieldRecord->fieldValueIDs); $i++){
												//obtain key identifer and label of each field value
												$keyComboProfileFieldValueID = $combinationFieldRecord->fieldValueIDs[$i];
												$fieldValueLabel = $combinationFieldRecord->fieldValues[$i];

												//add the field value to the record index for later lookups
												$combinationProfileFieldValuesRecordIndex[$keyComboProfileFieldValueID] = $fieldValueLabel;
												$combinationProfileFieldValueRecordNumber++;
												
												//output details of the combination profile field value record
												$resultMessage = $resultMessage."<tr>".
													"<td></td>".
													"<td></td>".
													"<td></td>".
													"<td></td>".
													"<td>".htmlentities($keyComboProfileFieldValueID)."</td>".
													"<td>".htmlentities($fieldValueLabel)."</td>".
												"</tr>";
											}
										}
									}
									
									$resultMessage = $resultMessage."</table>";
								}
							
								//check that parent combination product records have been placed into the standards document
								if($esDocument->dataRecords != null)
								{
									//output headers for the first page of records
									if($recordsStartIndex == 0){
										$resultMessage = $resultMessage.
											"<br/><br/>Product Combination Records:".
											'<table style="width: 100%; display: block; overflow-x: auto;white-space: nowrap;">'.
												"<tr>".
													"<th>#</th>".
													"<th>Parent - Key Product ID</th>".
													"<th>Parent - Product Code</th>".
													"<th>Parent - Product Name</th>".
													"<th>Key Combo Profile ID</th>".
													"<th>Combination Profile Name</th>".
												"</tr>";
									}
									
									//iterate through each parent combination product record stored within the standards document
									foreach ($esDocument->dataRecords as $parentCombinationProductRecord)
									{
										$parentProductName = '';
										$parentProductCode = '';
										$combinationProfileName = '';
										
										//lookup the parent product assigned to the combination and gets its name (or any other product details you wish)
										if(array_key_exists($parentCombinationProductRecord->keyProductID, $productsRecordIndex)){
											$parentProductCode = $productsRecordIndex[$parentCombinationProductRecord->keyProductID]->productCode;
											$parentProductName = $productsRecordIndex[$parentCombinationProductRecord->keyProductID]->name;
										}
										
										//get details of the combination profile the parent product is assigned to
										if(array_key_exists($parentCombinationProductRecord->keyComboProfileID, $combinationProfilesRecordIndex)){
											$combinationProfileName = $combinationProfilesRecordIndex[$parentCombinationProductRecord->keyComboProfileID]->profileName;
										}
									
										//output details of the record
										$resultMessage = $resultMessage."<tr>".
											"<td>".$parentCombinationsRecordNumber."</td>".
											"<td>".htmlentities($parentCombinationProductRecord->keyProductID)."</td>".
											"<td>".htmlentities($parentProductCode)."</td>".
											"<td>".htmlentities($parentProductName)."</td>".
											"<td>".htmlentities($parentCombinationProductRecord->keyComboProfileID)."</td>".
											"<td>".htmlentities($combinationProfileName)."</td>".
										"</tr>";

										//output table header row for child products
										$resultMessage = $resultMessage."<tr>".
											"<th></th>".
											"<th>Child - Key Product ID</th>".
											"<th>Child - Product Code</th>".
											"<th>Child - Product Name</th>".
											"<th>Combo Field Name</th>".
											"<th>Combo Field Value</th>".
										"</tr>";

										//iterate through the child product records assigned to the parent combination record
										foreach ($parentCombinationProductRecord->productCombinations as $productCombinationRecord)
										{
											$childProductCode = '';
											$childProductName = '';

											//lookup the child product assigned to the combination and gets its name (or any other product details you wish)
											if(array_key_exists($productCombinationRecord->keyProductID, $productsRecordIndex)){
												$childProductCode = $productsRecordIndex[$productCombinationRecord->keyProductID]->productCode;
												$childProductName = $productsRecordIndex[$productCombinationRecord->keyProductID]->name;
											}

											//output child product details
											$resultMessage = $resultMessage."<tr>".
												"<td></td>".
												"<td>".htmlentities($productCombinationRecord->keyProductID)."</td>".
												"<td>".htmlentities($childProductCode)."</td>".
												"<td>".htmlentities($childProductName)."</td>".
												"<td></td>".
												"<td></td>".
											"</tr>";

											//iterate through the combination fields and values assigned to the child product
											foreach ($productCombinationRecord->fieldValueCombinations as $fieldValueCombinationRecord)
											{
												//check that each combination field and value has been given in an array
												if(count($fieldValueCombinationRecord) == 2){
													$keyComboProfileFieldID = $fieldValueCombinationRecord[0];
													$keyComboProfileFieldValueID = $fieldValueCombinationRecord[1];
													$fieldName = '';
													$fieldValueLabel = '';

													//lookup the combination field and obtain field name
													if(array_key_exists($keyComboProfileFieldID, $combinationProfileFieldsRecordIndex)){
														$fieldName = $combinationProfileFieldsRecordIndex[$keyComboProfileFieldID]->fieldName;
													}

													//lookup the combination field value and obtain name of value
													if(array_key_exists($keyComboProfileFieldValueID, $combinationProfileFieldValuesRecordIndex)){
														$fieldValueLabel = $combinationProfileFieldValuesRecordIndex[$keyComboProfileFieldValueID];
													}


													//output child product details
													$resultMessage = $resultMessage."<tr>".
													"<td></td>".
													"<td></td>".
													"<td></td>".
													"<td></td>".
													"<td>".htmlentities($fieldName)."</td>".
													"<td>".htmlentities($fieldValueLabel)."</td>".
													
												"</tr>";
												}
											}
										}

										//place an empty row to separate parent products
										$resultMessage = $resultMessage."<tr>".
											"<th>#</th>".
											"<th></th>".
											"<th></th>".
											"<th></th>".
											"<th></th>".
											"<th></th>".
										"</tr>";
										
										$parentCombinationsRecordNumber++;
									}
									
									//check if there are more records to retrieve
									if(sizeof($esDocument->dataRecords) >= $combinationRecordsMaxAmount)
									{
										$recordsStartIndex = $recordsStartIndex + $combinationRecordsMaxAmount;
										$getMoreRecords = true;
									}else{
										$resultMessage = $resultMessage."</table>";
										$result = "SUCCESS";
									}
								}else{
									$result = "SUCCESS";
								}
							}else{
								$result = "FAIL";
								$resultMessage = "Organisation Product Combination data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							}
						}					
					}
					
					
					//next steps
					//call other API endpoints...
					//destroy API session when done...
					$apiOrgSession->destroyOrgSession();
					
					echo "<div>Result:<div>";
					echo "<div><b>$result</b><div><br/>";
					echo "<div>Combination Profiles Returned: <b>".($combinationProfileRecordNumber-1)."</b></div>";
					echo "<div>Parent Product Combinations Returned: <b>".($parentCombinationsRecordNumber-1)."</b></div>";
					echo "<div>Message:<div>";
					echo "<div><b>$resultMessage</b><div><br/>";
				?>
			</div>
		</div>
	</body>
</html>