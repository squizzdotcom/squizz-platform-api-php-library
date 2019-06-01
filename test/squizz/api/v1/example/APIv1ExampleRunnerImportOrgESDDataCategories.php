<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Import Category Organisation Data API Example</h1>
			
			<p style="text-align: left; max-width: 607px; margin: 0 auto;">
				Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to import category data of an organisation into the SQUIZZ.com platform. Each category record imported may represent an overall collection of products of a similar type, brand, or theme.
			</p>
			<br/>
			<ul style="text-align: left; max-width: 607px; margin: 0 auto;">
				<li>Ensure product data has been imported against an organisation first to allow products to be assigned against categories being imported, otherwise products will be ignored from being assigned against catogories where products don't exist in the platform yet.</li>
			</ul>
			<br/>
			
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
					use squizz\api\v1\endpoint\APIv1EndpointOrgImportESDocument;
					use squizz\api\v1\APIv1OrgSession;
					use squizz\api\v1\APIv1Constants;
					use EcommerceStandardsDocuments\ESDocumentCategory;
					use EcommerceStandardsDocuments\ESDRecordCategory;
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
					
					//import organisation category data if the API session was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create category records
						$categoryRecords = array();
						
						//create first category record (a top tier category)
						$categoryRecord = new ESDRecordCategory();
						$categoryRecord->keyCategoryID = "2";
						$categoryRecord->categoryCode = "Home and Stationery";
						
						//add category record to the list of categories
						array_push($categoryRecords, $categoryRecord);
						
						//create 2nd category record (child of the Home and Stationery category above)
						$categoryRecord = new ESDRecordCategory();
						$categoryRecord->keyCategoryID = "123";
						$categoryRecord->categoryCode = "tables-chairs";
						$categoryRecord->keyCategoryParentID = "2";
						$categoryRecord->name = "Tables and Chairs";
						$categoryRecord->description1 = "View our extensive range of tables and chairs";
						$categoryRecord->description2 = "Range includes products from the ESD designers";
						$categoryRecord->description3 = "";
						$categoryRecord->description4 = "";
						$categoryRecord->metaTitle = "Tables and Chairs From ESD Designers";
						$categoryRecord->metaKeywords = "tables chairs esd furniture designers";
						$categoryRecord->metaDescription = "Tables and chairs from the ESD designers";
						$categoryRecord->ordering = 2;
						$categoryRecord->keyProductIDs = array();
						array_push($categoryRecord->keyProductIDs, "TAB-1");
						array_push($categoryRecord->keyProductIDs, "53432");
						array_push($categoryRecord->keyProductIDs, "CHAIR-5");
						
						//add 2nd category record to the list of categories
						array_push($categoryRecords, $categoryRecord);
						
						//create 3rd category record (also a child of the Home and Stationery category above)
						$categoryRecord = new ESDRecordCategory();
						$categoryRecord->keyCategoryID = "124";
						$categoryRecord->categoryCode = "paper";
						$categoryRecord->keyCategoryParentID = "2";
						$categoryRecord->name = "Paper Products";
						$categoryRecord->description1 = "View our extensive range of paper";
						$categoryRecord->description2 = "Range includes paper only sources from sustainable environments";
						$categoryRecord->description3 = "";
						$categoryRecord->description4 = "";
						$categoryRecord->metaTitle = "Paper Products";
						$categoryRecord->metaKeywords = "paper products environmental";
						$categoryRecord->metaDescription = "Paper products from sustainable environments";
						$categoryRecord->ordering = 1;
						$categoryRecord->keyProductIDs = array();
						array_push($categoryRecord->keyProductIDs, "PROD-001");
						array_push($categoryRecord->keyProductIDs, "PROD-002");
						
						//create 4th category record (used for make/model)
						$categoryRecord = new ESDRecordCategory();
						$categoryRecord->keyCategoryID = "CAR-TYRE";
						$categoryRecord->categoryCode = "car-tyres";
						$categoryRecord->name = "Car Tyres";
						$categoryRecord->description1 = "View our extensive range of car types";
						$categoryRecord->description2 = "Range includes car types of all types";
						$categoryRecord->description3 = "";
						$categoryRecord->description4 = "";
						$categoryRecord->metaTitle = "Car Tyres";
						$categoryRecord->metaKeywords = "Car Tyres Rubber Premium";
						$categoryRecord->metaDescription = "Premium rubber car tyres";
						$categoryRecord->ordering = 4;
						$categoryRecord->keyProductIDs = array();
						array_push($categoryRecord->keyProductIDs, "PROD-001");
						array_push($categoryRecord->keyProductIDs, "PROD-002");
						
						//add 3rd category record to the list of categories
						array_push($categoryRecords, $categoryRecord);
						
						//after 60 seconds give up on waiting for a response from the API when importing the organisation data
						$timeoutMilliseconds = 60000;
						
						//add a dataFields attribute that contains a comma delimited list of category record fields that the API is allowed to insert or update in the platform
						$configs = array();
						$configs['dataFields'] = 'keyCategoryID,categoryCode,keyCategoryParentID,name,description1,description2,description3,description4,metaTitle,metaKeywords,metaDescription,ordering,keyProductIDs';
						
						//create category Ecommerce Standards document and add category records to the document
						$categoryESD = new ESDocumentCategory(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $categoryRecords, $configs);
						
						//output the JSON serialised ESD document that will be sent in the API server request
						echo '<h3>Request Data Serialised:<h3><textarea style="width: 90%; margin: 0 auto;" rows="4">'.htmlentities(json_encode($categoryESD)).'</textarea><br/>';

						//send the category document to the API to be imported against the organisation logged into the API
						$endpointResponseESD = APIv1EndpointOrgImportESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgImportESDocument::IMPORT_TYPE_ID_CATEGORY, $categoryESD);
						
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