<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Import Customer Contracts Organisation Data API Example</h1>
			
			<p style="text-align: left; max-width: 607px; margin: 0 auto;">
				Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to import customer contract data of an organisation into the SQUIZZ.com platform. Each customer account contract record imported may contain a collection of products that are available to a number customer accounts for a fixed period of time. Contracts may be used to lock in pricing for customers.
			</p>
			<br/>
			<ul style="text-align: left; max-width: 607px; margin: 0 auto;">
				<li>Ensure that customer account and product data has been imported against an organisation first to allow customer accounts and products to be assigned against contracts being imported, otherwise customer accounts and products will be ignored from being assigned against contracts where customer accounts or products don't exist in the platform yet.</li>
			</ul>
			<br/>
			
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
					use squizz\api\v1\endpoint\APIv1EndpointOrgImportESDocument;
					use squizz\api\v1\APIv1OrgSession;
					use squizz\api\v1\APIv1Constants;
					use EcommerceStandardsDocuments\ESDocumentCustomerAccountContract;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountContract;
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
					
					//import organisation customer contract data if the API session was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create contract records
						$customerAccountContractRecords = array();
						
						//create first contract record
						$contractRecord = new ESDRecordCustomerAccountContract();
						$contractRecord->keyContractID = "1";
						$contractRecord->contractCode = "Customer ABCF Contract";
						
						//add contract record to the list of contracts
						array_push($customerAccountContractRecords, $contractRecord);
						
						//create 2nd contract record
						$contractRecord = new ESDRecordCustomerAccountContract();
						$contractRecord->keyContractID = "123";
						$contractRecord->contractCode = "VIP1";
						$contractRecord->description = "Contract pricing for VIP customers";
						$contractRecord->forceContractPrice = ESDocumentConstants::ESD_VALUE_YES;
						$contractRecord->expireDate = strtotime("10 September 2030") * 1000;
						$contractRecord->keyProductIDs = array();
						array_push($contractRecord->keyProductIDs, "TAB-1");
						array_push($contractRecord->keyProductIDs, "53432");
						array_push($contractRecord->keyProductIDs, "CHAIR-5");
						$contractRecord->keyAccountIDs = array();
						array_push($contractRecord->keyAccountIDs, "CUST-0005");
						array_push($contractRecord->keyAccountIDs, "CUST-0007");
						
						//add 2nd contract record to the list of contracts
						array_push($customerAccountContractRecords, $contractRecord);
						
						//create 3rd contract record
						$contractRecord = new ESDRecordCustomerAccountContract();
						$contractRecord->keyContractID = "PRJ-33248";
						$contractRecord->contractCode = "PROJECT-33248";
						$contractRecord->description = "Project Building 33248, 44 Main Street";
						$contractRecord->forceContractPrice = ESDocumentConstants::ESD_VALUE_NO;
						$contractRecord->expireDate = strtotime("20 November 2040") * 1000;
						$contractRecord->keyProductIDs = array();
						array_push($contractRecord->keyProductIDs, "PROD-001");
						array_push($contractRecord->keyProductIDs, "PROD-002");
						array_push($contractRecord->keyProductIDs, "CHAIR-5");
						$contractRecord->keyAccountIDs = array();
						array_push($contractRecord->keyAccountIDs, "CUST-0005");
						array_push($contractRecord->keyAccountIDs, "CUST-0023");
						array_push($contractRecord->keyAccountIDs, "1231412");

						//add 3rd contract record to the list of contracts
						array_push($customerAccountContractRecords, $contractRecord);
						
						//create 4th contract record
						$contractRecord = new ESDRecordCustomerAccountContract();
						$contractRecord->keyContractID = "MONTHLY-SPECIAL";
						$contractRecord->contractCode = "MONTHLY-SPECIAL";
						$contractRecord->description = "Monthly specials products";
						$contractRecord->forceContractPrice = ESDocumentConstants::ESD_VALUE_NO;
						$contractRecord->expireDate = strtotime("1 January 9999") * 1000;
						$contractRecord->keyProductIDs = array();
						array_push($contractRecord->keyProductIDs, "PROD-002");
						array_push($contractRecord->keyProductIDs, "PROD-005");
						$contractRecord->keyAccountIDs = array();
						array_push($contractRecord->keyAccountIDs, "CUST-0042");
						array_push($contractRecord->keyAccountIDs, "CUST-2425");
						
						//add 4th contract record to the list of contracts
						array_push($customerAccountContractRecords, $contractRecord);
						
						//after 60 seconds give up on waiting for a response from the API when importing the organisation data
						$timeoutMilliseconds = 60000;
						
						//add a dataFields attribute that contains a comma delimited list of customer account contract record fields that the API is allowed to insert or update in the platform
						$configs = array();
						$configs['dataFields'] = 'keyContractID,contractCode,description,forceContractPrice,expireDate,keyProductIDs,keyAccountIDs';
						
						//create customer account contract Ecommerce Standards document and add contract records to the document
						$customerAccountContractESD = new ESDocumentCustomerAccountContract(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $customerAccountContractRecords, $configs);
						
						//output the JSON serialised ESD document that will be sent in the API server request
						echo '<h3>Request Data Serialised:<h3><textarea style="width: 90%; margin: 0 auto;" rows="4">'.htmlentities(json_encode($customerAccountContractESD)).'</textarea><br/>';

						//send the customer account contract document to the API to be imported against the organisation logged into the API
						$endpointResponseESD = APIv1EndpointOrgImportESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgImportESDocument::IMPORT_TYPE_ID_CUSTOMER_ACCOUNT_CONTRACTS, $customerAccountContractESD);
						
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
			</div>
		</div>
	</body>
</html>