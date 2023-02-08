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
			<h1>Retrieve Category Organisation Data API Example</h1>
			<p style="text-align: left; max-width: 607px; margin: 0 auto;">
				Tests making a request to the SQUIZZ.com API to create a session for an organisation, then makes a call to the API to retrieve Category data from a chosen organisation.<br/><br/>
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
					use squizz\api\v1\endpoint\APIv1EndpointOrgRetrieveESDocument;
					use squizz\api\v1\APIv1OrgSession;
					use squizz\api\v1\APIv1Constants;
					use EcommerceStandardsDocuments\ESDocumentConstants;
					use EcommerceStandardsDocuments\ESDRecordProduct;
					use EcommerceStandardsDocuments\ESDRecordCategory;
					use EcommerceStandardsDocuments\ESDocumentProduct;
					use EcommerceStandardsDocuments\ESDocumentCategory;
					
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
					$productsRecordIndex = array();
					$categoriesRecordIndex = array();
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
					if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
					{
					}
					else
					{
						//session failed to be created
						$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
					}
					
					//first retrieve and index organisation product data if the API session was successfully created
					if($apiOrgSession->sessionExists())
					{
						$result = "FAIL";
						$recordsStartIndex = 0;
						
						//after 120 seconds give up on waiting for a response from the API
						$timeoutMilliseconds = 120000;
						
						//get the next page of records if needed
						while($getMoreRecords)
						{
							//call the platform's API to retrieve the organisation's product data
							$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_PRODUCTS, $supplierOrgID, '',$recordsMaxAmount, $recordsStartIndex, $recordsUpdatedAfterDateTimeMilliseconds);
							
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
								}
							}else{
								$result = "FAIL";
								$resultMessage = "Organisation Product data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							}
						}
					}
					
					//next retrieve and index organisation category data if the product data was retrieved
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
							$endpointResponseESD = APIv1EndpointOrgRetrieveESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgRetrieveESDocument::RETRIEVE_TYPE_ID_CATEGORIES, $supplierOrgID, '',$recordsMaxAmount, $recordsStartIndex, $recordsUpdatedAfterDateTimeMilliseconds);
							
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
								}
							}else{
								$result = "FAIL";
								$resultMessage = "Organisation Category data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							}
						}
					}
					
					//output category data if it was successfully obtained
					if($result=="SUCCESS")
					{					
						$result = "FAIL";
						
						//output table headers
						$resultMessage = $resultMessage.
							"Organisation data successfully obtained from the platform".
							"<br/>Category Records:".
							'<table style="width: 100%; display: block; overflow-x: auto;white-space: nowrap;">'.
								"<tr>".
									"<th>#</th>".
									"<th>Key Category ID</th>".
									"<th>Category Code</th>".
									"<th>Name</th>".
									"<th>Key Category Parent ID</th>".
									"<th>Parent Category Code</th>".
									"<th>Description 1</th>".
									"<th>Description 2</th>".
									"<th>Description 3</th>".
									"<th>Description 4</th>".
									"<th>Meta Title</th>".
									"<th>Meta Keywords</th>".
									"<th>Meta Description</th>".
									"<th>Ordering</th>".
								"</tr>";
						
						//iterate through and output products assigned to the category
						foreach (array_keys($categoriesRecordIndex) as $keyCategoryID)
						{
							$categoryRecord = $categoriesRecordIndex[$keyCategoryID];
							$parentCategoryCode = "";
						
							//find the parent category that the category may be assigned to
							if(array_key_exists($categoryRecord->keyCategoryID, $categoriesRecordIndex)){
								$parentCategoryCode = $categoriesRecordIndex[$categoryRecord->keyCategoryID]->categoryCode;
							}
						
							//output details of the record
							$resultMessage = $resultMessage."<tr>".
								"<td>".$recordNumber."</td>".
								"<td>".htmlentities($categoryRecord->keyCategoryID)."</td>".
								"<td>".htmlentities($categoryRecord->categoryCode)."</td>".
								"<td>".htmlentities($categoryRecord->name)."</td>".
								"<td>".htmlentities($categoryRecord->KeyCategoryParentID)."</td>".
								"<td>".htmlentities($parentCategoryCode)."</td>".
								"<td>".htmlentities($categoryRecord->description1)."</td>".
								"<td>".htmlentities($categoryRecord->description2)."</td>".
								"<td>".htmlentities($categoryRecord->description3)."</td>".
								"<td>".htmlentities($categoryRecord->description4)."</td>".
								"<td>".htmlentities($categoryRecord->metaTitle)."</td>".
								"<td>".htmlentities($categoryRecord->metaKeywords)."</td>".
								"<td>".htmlentities($categoryRecord->metaDescription)."</td>".
								"<td>".htmlentities($categoryRecord->ordering)."</td>".
							"</tr>";
							
							//check if the category contains any products
							if(sizeof($categoryRecord->keyProductIDs) > 0)
							{
								//output product heaader row
								$resultMessage = $resultMessage."<tr>".
									"<th>Products</th>".
									"<th>Key Product ID</th>".
									"<th>Product Code</th>".
									"<th>Product Name</th>".
								"</tr>";
								
								//output each product assigned to the category
								foreach ($categoryRecord->keyProductIDs as $keyProductID)
								{											
									//check that the product has been previously obtained
									if(array_key_exists($keyProductID, $productsRecordIndex)){
									
										$productRecord = $productsRecordIndex[$keyProductID];
										
										$resultMessage = $resultMessage."<tr>".
											"<td></td>".
											"<td>".htmlentities($keyProductID)."</td>".
											"<td>".htmlentities($productRecord->productCode)."</td>".
											"<td>".htmlentities($productRecord->name)."</td>".
										"</tr>";
									}
								}
							}
							
							$recordNumber++;
						}
						
						$result = "SUCCESS";
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