<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Import Attribute Organisation Data API Example</h1>
			
			<p style="text-align: left; max-width: 607px; margin: 0 auto;">
				Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to import attribute data of an organisation into the SQUIZZ.com platform. The data import imports both attribute profiles that define attributes against each, as well as product attribute values that provide additional data against each product.
			</p>
			<br/>
			<ul style="text-align: left; max-width: 607px; margin: 0 auto;">
				<li>Ensure product data has been imported against an organisation first to allow attribute values to be assigned to the products, otherwise attribute values will be ignored from being assigned against products where products don't exist in the platform yet.</li>
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
					use EcommerceStandardsDocuments\ESDocumentAttribute;
					use EcommerceStandardsDocuments\ESDRecordAttributeProfile;
					use EcommerceStandardsDocuments\ESDRecordAttribute;
					use EcommerceStandardsDocuments\ESDRecordAttributeValue;
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
					
					//import organisation attribute data if the API session was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create attribute profile records
						$attributeProfileRecords = array();
						
						//create first attribute profile record
						$attributeProfileRecord = new ESDRecordAttributeProfile();
						$attributeProfileRecord->keyAttributeProfileID = "PAP002";
						$attributeProfileRecord->name = "Clothing Styling";
						$attributeProfileRecord->description = "View the styling details of clothes";
						$attributeProfileRecord->attributes = array();
						array_push($attributeProfileRecords, $attributeProfileRecord);
						
						//add attribute record to the 1st attribute profile
						$attributeRecord = new ESDRecordAttribute();
						$attributeRecord->keyAttributeID = "PAP002-1";
						$attributeRecord->name = "Colour";
						$attributeRecord->dataType = ESDRecordAttribute::DATA_TYPE_STRING;
						array_push($attributeProfileRecord->attributes, $attributeRecord);
						
						//add 2nd attribute record to the 1st attribute profile
						$attributeRecord = new ESDRecordAttribute();
						$attributeRecord->keyAttributeID = "PAP002-2";
						$attributeRecord->name = "Size";
						$attributeRecord->dataType = ESDRecordAttribute::DATA_TYPE_NUMBER;
						array_push($attributeProfileRecord->attributes, $attributeRecord);
						
						//add 3rd attribute record to the 1st attribute profile
						$attributeRecord = new ESDRecordAttribute();
						$attributeRecord->keyAttributeID = "PAP002-3";
						$attributeRecord->name = "Texture";
						$attributeRecord->dataType = ESDRecordAttribute::DATA_TYPE_STRING;
						array_push($attributeProfileRecord->attributes, $attributeRecord);
						
						//create 2nd attribute profile record
						$attributeProfileRecord = new ESDRecordAttributeProfile();
						$attributeProfileRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeProfileRecord->name = "Make/Model Vehicle Details";
						$attributeProfileRecord->description = "Details about the characteristics of automotive vehicles";
						$attributeProfileRecord->attributes = array();
						array_push($attributeProfileRecords, $attributeProfileRecord);
						
						//add 1st attribute record to the 2nd attribute profile
						$attributeRecord = new ESDRecordAttribute();
						$attributeRecord->keyAttributeID = "MMCAR-TYPE";
						$attributeRecord->name = "Vehicle Type";
						$attributeRecord->dataType = ESDRecordAttribute::DATA_TYPE_STRING;
						array_push($attributeProfileRecord->attributes, $attributeRecord);
						
						//add 2nd attribute record to the 2nd attribute profile
						$attributeRecord = new ESDRecordAttribute();
						$attributeRecord->keyAttributeID = "MMCAR-ENGINE-CYLINDERS";
						$attributeRecord->name = "Number of Cyclinders";
						$attributeRecord->dataType = ESDRecordAttribute::DATA_TYPE_NUMBER;
						array_push($attributeProfileRecord->attributes, $attributeRecord);
						
						//add 3rd attribute record to the 2nd attribute profile
						$attributeRecord = new ESDRecordAttribute();
						$attributeRecord->keyAttributeID = "MMCAR-FUEL-TANK-LITRES";
						$attributeRecord->name = "Fuel Tank Size (Litres)";
						$attributeRecord->dataType = ESDRecordAttribute::DATA_TYPE_NUMBER;
						array_push($attributeProfileRecord->attributes, $attributeRecord);
						
						//add 4th attribute record to the 2nd attribute profile
						$attributeRecord = new ESDRecordAttribute();
						$attributeRecord->keyAttributeID = "MMCAR-WHEELSIZE-RADIUS-INCH";
						$attributeRecord->name = "Wheel Size (Inches)";
						$attributeRecord->dataType = ESDRecordAttribute::DATA_TYPE_NUMBER;
						array_push($attributeProfileRecord->attributes, $attributeRecord);
						
						//add 5th attribute record to the 2nd attribute profile
						$attributeRecord = new ESDRecordAttribute();
						$attributeRecord->keyAttributeID = "MMCAR-WHEELSIZE-TREAD";
						$attributeRecord->name = "Tyre Tread";
						$attributeRecord->dataType = ESDRecordAttribute::DATA_TYPE_STRING;
						array_push($attributeProfileRecord->attributes, $attributeRecord);
						
						//create product attribute values array
						$productAttributeValueRecords = array();
						
						//set attribute values for products, for product PROD-001 set the clothing attributes colour=red, size=8 and 10, texture = soft
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyProductID = "PROD-001";
						$attributeValueRecord->keyAttributeProfileID = "PAP002";
						$attributeValueRecord->keyAttributeID = "PAP002-1";
						$attributeValueRecord->stringValue = "Red";
						array_push($productAttributeValueRecords, $attributeValueRecord);
						
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyProductID = "PROD-001";
						$attributeValueRecord->keyAttributeProfileID = "PAP002";
						$attributeValueRecord->keyAttributeID = "PAP002-2";
						$attributeValueRecord->numberValue = 8;
						array_push($productAttributeValueRecords, $attributeValueRecord);
						
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyProductID = "PROD-001";
						$attributeValueRecord->keyAttributeProfileID = "PAP002";
						$attributeValueRecord->keyAttributeID = "PAP002-2";
						$attributeValueRecord->numberValue = 10;
						array_push($productAttributeValueRecords, $attributeValueRecord);
						
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyProductID = "PROD-001";
						$attributeValueRecord->keyAttributeProfileID = "PAP002";
						$attributeValueRecord->keyAttributeID = "PAP002-3";
						$attributeValueRecord->stringValue = "soft";
						array_push($productAttributeValueRecords, $attributeValueRecord);
						
						//after 60 seconds give up on waiting for a response from the API when importing the organisation data
						$timeoutMilliseconds = 60000;
						
						//add a dataFields attribute that contains a comma delimited list of attribute record fields that the API is allowed to insert or update in the platform
						$configs = array();
						$configs['dataFields'] = 'keyProductID,keyAttributeProfileID,keyAttributeID,stringValue,numberValue';
						
						//create attribute Ecommerce Standards document and add attribute profile and product attribute value records to the document
						$attributeESD = new ESDocumentAttribute(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $attributeProfileRecords, $productAttributeValueRecords, $configs);
						
						//output the JSON serialised ESD document that will be sent in the API server request
						echo '<h3>Request Data Serialised:<h3><textarea style="width: 90%; margin: 0 auto;" rows="4">'.htmlentities(json_encode($attributeESD)).'</textarea><br/>';

						//send the attribute document to the API to be imported against the organisation logged into the API
						$endpointResponseESD = APIv1EndpointOrgImportESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgImportESDocument::IMPORT_TYPE_ID_ATTRIBUTE, $attributeESD);
						
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