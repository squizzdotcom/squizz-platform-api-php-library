<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Import Maker Model Organisation Data API Example</h1>
			
			<p style="text-align: left; max-width: 607px; margin: 0 auto;">
				Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to import maker model data of an organisation into the SQUIZZ.com platform. Each model record imported represents a collection of products or materials to make up a complete model. Models are created by makers/manufacturers.
			</p>
			<br/>
			<ul style="text-align: left; max-width: 607px; margin: 0 auto;">
				<li>Ensure maker data has been imported against an organisation first to allow models to be imported against previously imported makers, otherwise model record will be ignored from being imported against any makers that don't exist.</li>
				<li>Ensure attribute data has been imported against an organisation first to allow maker model attribute data to import, otherwise attribute data will be ignored from being imported and assigned against models where an attribute does not exist already.</li>
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
					use EcommerceStandardsDocuments\ESDocumentMakerModel;
					use EcommerceStandardsDocuments\ESDRecordMakerModel;
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
					
					//import organisation maker model data if the API session was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create maker model records
						$makerModelRecords = array();
						$makerModelRecord = new ESDRecordMakerModel();
						$makerModelRecord->keyMakerModelID = "2";
						$makerModelRecord->keyMakerID = "2";
						$makerModelRecord->modelCode = "SEDAN1";
						$makerModelRecord->modelSubCode = "1ABC";
						$makerModelRecord->name = "Sahara Luxury Sedan 2016";
						$makerModelRecord->modelSearchCode = "Car-Manufacturer-A-Saraha-Luxury-Sedan-2016";
						$makerModelRecord->groupClass = "SEDAN";
						$makerModelRecord->releasedDate = 1456750800000;
						$makerModelRecord->createdDate = 1430748000000;
						$makerModelRecord->attributes = array();
						
						//add attribute value records against the model record
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-TYPE";
						$attributeValueRecord->stringValue = "Sedan";
						array_push($makerModelRecord->attributes, $attributeValueRecord);
						
						//add 2nd attribute value to the model
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-ENGINE-CYLINDERS";
						$attributeValueRecord->numberValue = 4;
						array_push($makerModelRecord->attributes, $attributeValueRecord);
						
						//add 3rd attribute value to the model
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-FUEL-TANK-LITRES";
						$attributeValueRecord->numberValue = 80.5;
						array_push($makerModelRecord->attributes, $attributeValueRecord);
						
						//add model record to the list of models
						array_push($makerModelRecords, $makerModelRecord);
						
						//create 2nd maker model record
						$makerModelRecord = new ESDRecordMakerModel();
						$makerModelRecord->keyMakerModelID = "3";
						$makerModelRecord->keyMakerID = "2";
						$makerModelRecord->modelCode = "TRUCK22";
						$makerModelRecord->modelSubCode = "EX";
						$makerModelRecord->name = "City Truck 2016";
						$makerModelRecord->modelSearchCode = "Car-Manufacturer-A-City-Truck-2016";
						$makerModelRecord->groupClass = "TRUCK";
						$makerModelRecord->releasedDate = 1456750800000;
						$makerModelRecord->createdDate = 1430748000000;
						$makerModelRecord->attributes = array();
						
						//add attribute value records against the model record
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-TYPE";
						$attributeValueRecord->stringValue = "Truck";
						array_push($makerModelRecord->attributes, $attributeValueRecord);
						
						//add 2nd attribute value to the model
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-ENGINE-CYLINDERS";
						$attributeValueRecord->numberValue = 6;
						array_push($makerModelRecord->attributes, $attributeValueRecord);
						
						//add 3rd attribute value to the model
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-FUEL-TANK-LITRES";
						$attributeValueRecord->numberValue = 140;
						array_push($makerModelRecord->attributes, $attributeValueRecord);
						
						//add 2nd model record to the list of models
						array_push($makerModelRecords, $makerModelRecord);
						
						//after 60 seconds give up on waiting for a response from the API when importing the organisation data
						$timeoutMilliseconds = 60000;
						
						//add a dataFields attribute that contains a comma delimited list of maker model record fields that the API is allowed to insert or update in the platform
						$configs = array();
						$configs['dataFields'] = 'keyMakerModelID,keyMakerID,modelCode,modelSubCode,name,modelSearchCode,groupClass,releasedDate,createdDate,attributes';
						
						//create makerModel Ecommerce Standards document and add makerModel records to the document
						$makerModelESD = new ESDocumentMakerModel(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $makerModelRecords, $configs);
						
						//output the JSON serialised ESD document that will be sent in the API server request
						echo '<h3>Request Data Serialised:<h3><textarea style="width: 90%; margin: 0 auto;" rows="4">'.htmlentities(json_encode($makerModelESD)).'</textarea><br/>';

						//send the makerModel document to the API to be imported against the organisation logged into the API
						$endpointResponseESD = APIv1EndpointOrgImportESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgImportESDocument::IMPORT_TYPE_ID_MAKER_MODEL, $makerModelESD);
						
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