<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Import Maker Model Mapping Organisation Data API Example</h1>
			<p style="text-align: left; max-width: 607px; margin: 0 auto;">
				Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to import maker model mapping data of an organisation into the SQUIZZ.com platform. 
				This data allows products to be assigned to different categories of models.<br/><br/>
			</p>
			<ul style="text-align: left; max-width: 607px; margin: 0 auto;">
				<li>Ensure maker model data has been imported against an organisation first to allow maker model mapping data to be imported against previously imported models, otherwise mapping data will be ignored from being imported against a model.</li>
				<li>Ensure attribute data has been imported against an organisation first to allow maker model mapping attribute data to import, otherwise attribute data will be ignored from being imported.</li>
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
					use EcommerceStandardsDocuments\ESDocumentMakerModelMapping;
					use EcommerceStandardsDocuments\ESDRecordMakerModelMapping;
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
					
					//import organisation maker model mapping data if the API session was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create maker model mapping records
						$makerModelMappingRecords = array();
						$makerModelMappingRecord = new ESDRecordMakerModelMapping();
						$makerModelMappingRecord->keyMakerModelID = "2";
						$makerModelMappingRecord->keyCategoryID = "CAR-TYRE";
						$makerModelMappingRecord->keyProductID = "CAR-TYRE-LONG-LASTING";
						$makerModelMappingRecord->quantity = 4;
						$makerModelMappingRecord->attributes = array();
						
						//add attribute value records against the model mapping record
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-WHEELSIZE-RADIUS-INCH";
						$attributeValueRecord->numberValue = 21;
						array_push($makerModelMappingRecord->attributes, $attributeValueRecord);
						
						//add 2nd attribute value to the model mapping
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-WHEELSIZE-TREAD";
						$attributeValueRecord->stringValue = "All Weather";
						array_push($makerModelMappingRecord->attributes, $attributeValueRecord);
						
						//add mapping record to the list of model mappings
						array_push($makerModelMappingRecords, $makerModelMappingRecord);
						
						//create 2nd maker model mapping record
						$makerModelMappingRecord = new ESDRecordMakerModelMapping();
						$makerModelMappingRecord->keyMakerModelID = "2";
						$makerModelMappingRecord->keyCategoryID = "CAR-TYRE";
						$makerModelMappingRecord->keyProductID = "CAR-TYRE-CHEAP";
						$makerModelMappingRecord->quantity = 4;
						$makerModelMappingRecord->attributes = array();
						
						//add attribute value records against the model mapping record
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-WHEELSIZE-RADIUS-INCH";
						$attributeValueRecord->numberValue = 20;
						array_push($makerModelMappingRecord->attributes, $attributeValueRecord);
						
						//add 2nd attribute value to the model mapping
						$attributeValueRecord = new ESDRecordAttributeValue();
						$attributeValueRecord->keyAttributeProfileID = "MAKEMODELCAR";
						$attributeValueRecord->keyAttributeID = "MMCAR-WHEELSIZE-TREAD";
						$attributeValueRecord->stringValue = "BITUMEN";
						array_push($makerModelMappingRecord->attributes, $attributeValueRecord);
						
						//add 2nd mapping record to the list of model mappings
						array_push($makerModelMappingRecords, $makerModelMappingRecord);
						
						//after 60 seconds give up on waiting for a response from the API when importing the organisation data
						$timeoutMilliseconds = 60000;
						
						//add a dataFields attribute that contains a comma delimited list of maker model mapping record fields that the API is allowed to insert or update in the platform
						$configs = array();
						$configs['dataFields'] = 'keyMakerModelID,keyCategoryID,keyProductID,quantity,attributes';
						
						//create makerModelMapping Ecommerce Standards document and add maker model mapping records to the document
						$makerModelMappingESD = new ESDocumentMakerModelMapping(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $makerModelMappingRecords, $configs);
						
						//output the JSON serialised ESD document that will be sent in the API server request
						echo '<h3>Request Data Serialised:<h3><textarea style="width: 90%; margin: 0 auto;" rows="4">'.htmlentities(json_encode($makerModelMappingESD)).'</textarea><br/>';

						//send the makerModelMapping document to the API to be imported against the organisation logged into the API
						$endpointResponseESD = APIv1EndpointOrgImportESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgImportESDocument::IMPORT_TYPE_ID_MAKER_MODEL_MAPPING, $makerModelMappingESD);
						
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