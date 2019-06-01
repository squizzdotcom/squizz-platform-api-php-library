<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Import Maker Organisation Data API Example</h1>
			<p>Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to import data of an organisation into the SQUIZZ.com platform</p>
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
					use EcommerceStandardsDocuments\ESDocumentMaker;
					use EcommerceStandardsDocuments\ESDRecordMaker;
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
					
					//import organisation maker data if the API session was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create maker records
						$makerRecords = array();
						$makerRecord = new ESDRecordMaker();
						$makerRecord->keyMakerID = "1";
						$makerRecord->makerCode = "CAR1";
						$makerRecord->name = "Car Manufacturer X";
						$makerRecord->makerSearchCode = "Car-Manufacturer-X-Sedans-Wagons-Trucks";
						$makerRecord->groupClass = "POPULAR CARS";
						$makerRecord->ordering = 3;
						$makerRecord->establishedDate = 1449132083087;
						$makerRecord->orgName = "Car Manufacturer X";
						$makerRecord->authorityNumbers = array();
						$makerRecord->authorityNumberLabels = array();
						$makerRecord->authorityNumberTypes = array();
						array_push($makerRecord->authorityNumbers, "988776643221");
						array_push($makerRecord->authorityNumberLabels, "Australian Business Number");
						array_push($makerRecord->authorityNumberTypes, 1);
						
						array_push($makerRecords, $makerRecord);
						
						//add 2nd maker record
						$makerRecord = new ESDRecordMaker();
						$makerRecord->keyMakerID = "2";
						$makerRecord->makerCode = "CAR2";
						$makerRecord->name = "Car Manufacturer A";
						$makerRecord->makerSearchCode = "Car-Manufacturer-A";
						$makerRecord->groupClass = "POPULAR CARS";
						$makerRecord->ordering = 2;
						$makerRecord->establishedDate = 1449132083084;
						$makerRecord->orgName = "Car Manufacturer A";
						$makerRecord->authorityNumbers = array();
						$makerRecord->authorityNumberLabels = array();
						$makerRecord->authorityNumberTypes = array();
						array_push($makerRecord->authorityNumbers, "123456789 1234");
						array_push($makerRecord->authorityNumberLabels, "Australian Business Number");
						array_push($makerRecord->authorityNumberTypes, 1);
						
						array_push($makerRecords, $makerRecord);
						
						//add 3rd maker record
						$makerRecord = new ESDRecordMaker();
						$makerRecord->keyMakerID = "3";
						$makerRecord->makerCode = "CAR3";
						$makerRecord->name = "Car Manufacturer B";
						$makerRecord->makerSearchCode = "Car-Manufacturer-B-Sedans-Wagons";
						$makerRecord->groupClass = "CUSTOM CARS";
						$makerRecord->ordering = 1;
						$makerRecord->establishedDate = 1449132083085;
						$makerRecord->orgName = "Car Manufacturer B";
						$makerRecord->authorityNumbers = array();
						$makerRecord->authorityNumberLabels = array();
						$makerRecord->authorityNumberTypes = array();
						array_push($makerRecord->authorityNumbers, "98877664322");
						array_push($makerRecord->authorityNumberLabels, "Australian Business Number");
						array_push($makerRecord->authorityNumberTypes, 1);
						
						array_push($makerRecords, $makerRecord);
						
						//after 60 seconds give up on waiting for a response from the API when importing the organisation data
						$timeoutMilliseconds = 60000;
						
						//add a dataFields attribute that contains a comma delimited list of maker record fields that the API is allowed to insert or update in the platform
						$configs = array();
						$configs['dataFields'] = 'keyMakerID,makerCode,name,makerSearchCode,groupClass,ordering,establishedDate,orgName,authorityNumbers,authorityNumberLabels,authorityNumberTypes';
						
						//create maker Ecommerce Standards document and add maker records to the document
						$makerESD = new ESDocumentMaker(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $makerRecords, $configs);
						
						//output the JSON serialised ESD document that will be sent in the API server request
						echo '<h3>Request Data Serialised:<h3><textarea style="width: 90%; margin: 0 auto;" rows="4">'.htmlentities(json_encode($makerESD)).'</textarea><br/>';

						//send the maker document to the API to be imported against the organisation logged into the API
						$endpointResponseESD = APIv1EndpointOrgImportESDocument::call($apiOrgSession, $timeoutMilliseconds, APIv1EndpointOrgImportESDocument::IMPORT_TYPE_ID_MAKER, $makerESD);
						
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