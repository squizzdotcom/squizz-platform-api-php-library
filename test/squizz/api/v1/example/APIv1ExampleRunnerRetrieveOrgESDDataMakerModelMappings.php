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
			<h1>Retrieve Maker Model Mapping Organisation Data API Example</h1>
			<p style="text-align: left; max-width: 607px; margin: 0 auto;">
				Tests making a request to the SQUIZZ.com API to create a session for an organisation, then makes a call to the API to retrieve Maker Model Mapping data from a chosen organisation.<br/><br/>
			</p>
			
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
					use EcommerceStandardsDocuments\ESDocumentConstants;
					use EcommerceStandardsDocuments\ESDRecordMaker;
					use EcommerceStandardsDocuments\ESDRecordMakerModel;
					use EcommerceStandardsDocuments\ESDRecordMakerModelMapping;
					use EcommerceStandardsDocuments\ESDRecordProduct;
					use EcommerceStandardsDocuments\ESDRecordCategory;
					use EcommerceStandardsDocuments\ESDRecordAttribute;
					use EcommerceStandardsDocuments\ESDRecordAttributeProfile;
					use EcommerceStandardsDocuments\ESDRecordAttributeValue;
					use EcommerceStandardsDocuments\ESDocumentProduct;
					use EcommerceStandardsDocuments\ESDocumentCategory;
					use EcommerceStandardsDocuments\ESDocumentMaker;
					use EcommerceStandardsDocuments\ESDocumentMakerModel;
					use EcommerceStandardsDocuments\ESDocumentMakerModelMapping;
					
					//obtain or load in an organisation's API credentials, in this example from command line arguments
					$orgID = $_GET["orgID"];
					$orgAPIKey = $_GET["orgAPIKey"];
					$orgAPIPass = $_GET["orgAPIPass"];
					$supplierOrgID = $_GET["supplierOrgID"];
					$sessionTimeoutMilliseconds = 60000;
					$recordsMaxAmount = 5000;
					$recordsStartIndex = 0;
					$getMoreRecords = true;
					$recordNumber = 1;
					$resultMessage = "";
					$makerModelsRecordIndex = array();
					$attributeProfilesRecordIndex = array();
					$attributesRecordIndex = array();
					$productsRecordIndex = array();
					$categoriesRecordIndex = array();
					
					
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
					
					//first retrieve and index organisation maker model data if the API session was successfully created
					if($apiOrgSession->sessionExists())
					{
						//after 120 seconds give up on waiting for a response from the API
						$timeoutMilliseconds = 120000;
						
						//get the next page of records if needed
						while($getMoreRecords)
						{
							//call the platform's API to retrieve the organisation's maker model data
							$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_MAKER_MODELS, $supplierOrgID, '',$recordsMaxAmount, $recordsStartIndex);
							
							$getMoreRecords = false;
							$esDocument = $endpointResponseESD->esDocument;
				
							//check that the data successfully retrieved
							if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
							{
								//check that records have been placed into the standards document
								if($esDocument->dataRecords != null)
								{
									//iterate through each maker model record stored within the standards document and index
									foreach ($esDocument->dataRecords as $makerModelRecord){
										$makerModelsRecordIndex[$makerModelRecord->keyMakerModelID] = $makerModelRecord;
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
								$resultMessage = "Organisation Maker Model data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							}
						}
					}
					
					//next retrieve and index organisation attribute data if the maker model data was retrieved
					if($result=="SUCCESS")
					{
						$result = "FAIL";
						$recordsStartIndex = 0;
					
						//after 120 seconds give up on waiting for a response from the API
						$timeoutMilliseconds = 120000;
						
						//call the platform's API to retrieve the organisation's attribute data
						$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_ATTRIBUTES, $supplierOrgID, '', 5000, 0);
						$getMoreRecords = false;
						$esDocument = $endpointResponseESD->esDocument;
			
						//check that the data successfully retrieved
						if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
						{		
							//check that records have been placed into the standards document
							if($esDocument->attributeProfiles != null)
							{
								//iterate through each attribute profile record stored within the standards document
								foreach ($esDocument->attributeProfiles as $attributeProfileRecord){
									$attributeProfilesRecordIndex[$attributeProfileRecord->keyAttributeProfileID] = $attributeProfileRecord;
									
									//iterate through each attribute assigned to the attribute profile
									foreach ($attributeProfileRecord->attributes as $attributeRecord){
										$attributesRecordIndex[$attributeRecord->keyAttributeID] = $attributeRecord;
									}
								}
							}
							
							$result = "SUCCESS";
						}else{
							$result = "FAIL";
							$resultMessage = "Organisation Attribute data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
						}
					}
					
					//next retrieve and index organisation product data if the attribute data was retrieved
					if($result=="SUCCESS")
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
					
					//next retrieve and index organisation category data if the attribute data was retrieved
					if($result=="SUCCESS")
					{
						$result = "FAIL";
						$recordsStartIndex = 0;
						$getMoreRecords = true;
						
						//after 120 seconds give up on waiting for a response from the API
						$timeoutMilliseconds = 120000;
						
						//get the next page of records if needed
						while($getMoreRecords)
						{
							//call the platform's API to retrieve the organisation's category data
							$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_CATEGORIES, $supplierOrgID, '',$recordsMaxAmount, $recordsStartIndex);
							$getMoreRecords = false;
							$esDocument = $endpointResponseESD->esDocument;
							
							//check that the data successfully retrieved
							if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
							{
								//check that records have been placed into the standards document
								if($esDocument->dataRecords != null)
								{
									//iterate through each category record stored within the standards document and index
									foreach ($esDocument->dataRecords as $categoryRecord){
										$categoriesRecordIndex[$categoryRecord->keyCategoryID] = $categoryRecord;
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
								$resultMessage = "Organisation Category data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							}
						}
					}
					
					//retrieve organisation maker model mapping data after attribute profile data has been successfully retrieved
					if($result=="SUCCESS")
					{
						$result = "FAIL";
						$recordsStartIndex = 0;
						$getMoreRecords = true;
						
						//after 120 seconds give up on waiting for a response from the API
						$timeoutMilliseconds = 120000;
						
						//get the next page of records if needed
						while($getMoreRecords)
						{
							//call the platform's API to retrieve the organisation's maker model mapping data
							$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_MAKER_MODEL_MAPPINGS, $supplierOrgID, '',$recordsMaxAmount, $recordsStartIndex);
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
											"Organisation data successfully obtained from the platform".
											"<br/>Maker Model Records:".
											'<table style="width: 100%; display: block; overflow-x: auto;white-space: nowrap;">'.
												"<tr>".
													"<th>#</th>".
													"<th>Key Maker Model ID</th>".
													"<th>Model Code</th>".
													"<th>Model Name</th>".
													"<th>Key Category ID</th>".
													"<th>Category Code</th>".
													"<th>Category Name</th>".
													"<th>Key Product ID</th>".
													"<th>Product Code</th>".
													"<th>Product Name</th>".
													"<th>Quantity</th>".
												"</tr>";
									}
									
									//iterate through each model mapping record stored within the standards document
									foreach ($esDocument->dataRecords as $modelMappingRecord)
									{
										$modelCode = '';
										$modelName = '';
										$categoryCode = '';
										$categoryName = '';
										$productCode = '';
										$productName = '';
										
										//lookup the mapping's model and gets the model name and code
										if(array_key_exists($modelMappingRecord->keyMakerModelID, $makerModelsRecordIndex)){
											$modelCode = $makerModelsRecordIndex[$modelMappingRecord->keyMakerModelID]->modelCode;
											$modelName = $makerModelsRecordIndex[$modelMappingRecord->keyMakerModelID]->name;
										}
										
										//lookup the mapping's category and gets the category name and code
										if(array_key_exists($modelMappingRecord->keyCategoryID, $categoriesRecordIndex)){
											$categoryCode = $categoriesRecordIndex[$modelMappingRecord->keyCategoryID]->categoryCode;
											$categoryName = $categoriesRecordIndex[$modelMappingRecord->keyCategoryID]->name;
										}
										
										//lookup the mapping's product and gets the product name and code
										if(array_key_exists($modelMappingRecord->keyProductID, $productsRecordIndex)){
											$productCode = $productsRecordIndex[$modelMappingRecord->keyProductID]->productCode;
											$productName = $productsRecordIndex[$modelMappingRecord->keyProductID]->name;
										}
									
										//output details of the record
										$resultMessage = $resultMessage."<tr>".
											"<td>$recordNumber</td>".
											"<td>".htmlentities($modelMappingRecord->keyMakerModelID)."</td>".
											"<td>".htmlentities($modelCode)."</td>".
											"<td>".htmlentities($modelName)."</td>".
											"<td>".htmlentities($modelMappingRecord->keyCategoryID)."</td>".
											"<td>".htmlentities($categoryCode)."</td>".
											"<td>".htmlentities($categoryName)."</td>".
											"<td>".htmlentities($modelMappingRecord->keyProductID)."</td>".
											"<td>".htmlentities($productCode)."</td>".
											"<td>".htmlentities($productName)."</td>".
											"<td>".htmlentities($modelMappingRecord->quantity)."</td>".
										"</tr>";
										
										//check if the model contains any attributes
										if(sizeof($modelMappingRecord->attributes) > 0)
										{
											//output attributes
											$resultMessage = $resultMessage."<tr>".
												"<th>Attributes</th>".
												"<th>Profile</th>".
												"<th>Name</th>".
												"<th>Value</th>".
											"</tr>";
											
											//output each attribute value assigned to the model mapping
											foreach ($modelMappingRecord->attributes as $attributeValueRecord)
											{											
												//check that the attribute has been obtained
												if(array_key_exists($attributeValueRecord->keyAttributeID, $attributesRecordIndex)){
												
													$attributeRecord = $attributesRecordIndex[$attributeValueRecord->keyAttributeID];
													$attributeName = $attributeRecord->name;
													$attributeDataType = $attributeRecord->dataType;
													$attributeProfileName = "";
													$attributeValue = "";
													
													if(array_key_exists($attributeValueRecord->keyAttributeProfileID, $attributeProfilesRecordIndex)){
														$attributeProfileName = $attributeProfilesRecordIndex[$attributeValueRecord->keyAttributeProfileID]->name;
													}
													
													// get the model mapping's attribute value based on its data type
													if($attributeDataType == ESDRecordAttribute::DATA_TYPE_NUMBER){
														$attributeValue = $attributeValueRecord->numberValue;
													}else{
														$attributeValue = $attributeValueRecord->stringValue;
													}
													
													$resultMessage = $resultMessage."<tr>".
														"<td></td>".
														"<td>".htmlentities($attributeProfileName)."</td>".
														"<td>".htmlentities($attributeName)."</td>".
														"<td>".htmlentities($attributeValue)."</td>".
													"</tr>";
												}
											}
										}
										
										$recordNumber++;
									}
									
									//check if there are more records to retrieve
									if(sizeof($esDocument->dataRecords) >= $recordsMaxAmount)
									{
										$recordsStartIndex = $recordsStartIndex + $recordsMaxAmount;
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
								$resultMessage = "Organisation Make Model Mapping data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							}
						}
					}
					
					//next steps
					//call other API endpoints...
					//destroy API session when done...
					$apiOrgSession->destroyOrgSession();
					
					echo "<div>Result:<div>";
					echo "<div><b>$result</b><div><br/>";
					echo "<div>Records Returned: <b>".($recordNumber-1)."</b></div>";
					echo "<div>Message:<div>";
					echo "<div><b>$resultMessage</b><div><br/>";
				?>
			</div>
		</div>
	</body>
</html>