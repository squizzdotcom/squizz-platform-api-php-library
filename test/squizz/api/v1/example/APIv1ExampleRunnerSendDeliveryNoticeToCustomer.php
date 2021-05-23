<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Send Delivery Notice To Customer API Example</h1>
			<p>Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to push a delivery notice to a customer organisation or person, optionally importing into the customer's system or exporting to another system</p>
			<div style="max-width: 607px; background-color: #2b2b2b; color: #cacaca; text-align: center; margin: auto; padding-top: 15px;">
				<?php
					/**
					* Copyright (C) Squizz PTY LTD
					* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
					* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
					* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
					*/
					
					require_once __DIR__ . '/../../../../../3rd-party/jsonmapper/JsonMapper/Exception.php';
					
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
					use squizz\api\v1\endpoint\APIv1EndpointOrgSendDeliveryNoticeToCustomer;
					use squizz\api\v1\APIv1OrgSession;
					use squizz\api\v1\APIv1Constants;
					use EcommerceStandardsDocuments\ESDRecordDeliveryNotice;
					use EcommerceStandardsDocuments\ESDocumentConstants;
					use EcommerceStandardsDocuments\ESDocumentDeliveryNotice;
					
					//obtain or load in an organisation's API credentials, in this example from command line arguments
					$orgID = $_GET["orgID"];
					$orgAPIKey = $_GET["orgAPIKey"];
					$orgAPIPass = $_GET["orgAPIPass"];
					$customerOrgID = $_GET["customerOrgID"];
					$supplierAccountCode = $_GET["supplierAccountCode"];
					$useDeliveryNoticeExport = ($_GET["useDeliveryNoticeExport"] == ESDocumentConstants::ESD_VALUE_YES? true: false);
					
					$sessionTimeoutMilliseconds = 60000;
					
					echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
					
					//create an API session instance
					$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
					
					//call the platform's API to request that a session is created
					$endpointResponse = $apiOrgSession->createOrgSession();
					
					//check if the organisation's credentials were correct and that a session was created in the platform's API
					$result = "FAIL";
					$resultMessage = "";
					if($endpointResponse->result != APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
					{
						//session failed to be created
						$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
					}
					
					//send the delivery notice if the API was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create customer invoice record to import
						$deliveryNoticeRecord = new ESDRecordDeliveryNotice();

						//set data within the delivery notice
						$deliveryNoticeRecord->keyDeliveryNoticeID = "DN123";
						$deliveryNoticeRecord->deliveryNoticeCode = "CUSDELNUM-123-A";
						$deliveryNoticeRecord->deliveryStatus = ESDocumentConstants::DELIVERY_STATUS_IN_TRANSIT;
						$deliveryNoticeRecord->deliveryStatusMessage = "Currently en-route to receiver.";
		
						//set information about the freight carrier currently performing the delivery
						$deliveryNoticeRecord->freightCarrierName = "ACME Freight Logistics Inc.";
						$deliveryNoticeRecord->freightCarrierCode = "ACFLI";
						$deliveryNoticeRecord->freightCarrierTrackingCode = "34320-ACFLI-34324-234";
						$deliveryNoticeRecord->freightCarrierAccountCode = "VIP00012";
						$deliveryNoticeRecord->freightCarrierConsignCode = "42343-242344";
						$deliveryNoticeRecord->freightCarrierServiceCode = "SUPER-SMART-FREIGHT-FACILITATOR";
						$deliveryNoticeRecord->freightSystemRefCode = "SSFF-3421";
		
						// add references to other records (sales order, customer invoice, purchase order, customer account) that this delivery is associated to
						$deliveryNoticeRecord->keyCustomerInvoiceID = "4";
						$deliveryNoticeRecord->customerInvoiceCode = "CINV-22";
						$deliveryNoticeRecord->customerInvoiceNumber = "22";
						$deliveryNoticeRecord->keySalesOrderID = "121-332";
						$deliveryNoticeRecord->salesOrderCode = "SSO-332-ABC";
						$deliveryNoticeRecord->salesOrderNumber = "332";
						$deliveryNoticeRecord->purchaseOrderCode = "PO-345";
						$deliveryNoticeRecord->purchaseOrderNumber = "345";
						$deliveryNoticeRecord->instructions = "Please leave goods via the back driveway";
						$deliveryNoticeRecord->keyCustomerAccountID = "1";
		
						// set where the delivery is currently located geographically
						$deliveryNoticeRecord->atGeographicLocation = ESDocumentConstants::ESD_VALUE_YES;
						$deliveryNoticeRecord->locationLatitude = -37.8277324706811;
						$deliveryNoticeRecord->locationLongitude = 144.92382897158126;

						//create delivery notice records list and add delivery notice to it
						$deliveryNoticeRecords = array();
						array_push($deliveryNoticeRecords, $deliveryNoticeRecord);
						
						//after 60 seconds give up on waiting for a response from the API when sending the delivery notice
						$timeoutMilliseconds = 60000;
						
						//create delivery notice Ecommerce Standards document and add delivery notice records to the document
						$deliveryNoticeESD = new ESDocumentDeliveryNotice(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $deliveryNoticeRecords, array());

						//send delivery notice document to the API for sending onto the customer organisation
						$endpointResponseESD = APIv1EndpointOrgSendDeliveryNoticeToCustomer::call($apiOrgSession, $timeoutMilliseconds, $customerOrgID, $supplierAccountCode, $useDeliveryNoticeExport, $deliveryNoticeESD);
						
						//check the result of sending the delivery notice
						if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
							$result = "SUCCESS";
							$resultMessage = "Organisation delivery notices have successfully been sent to customer.";
							
						}else{
							$result = "FAIL";
							$resultMessage = "Organisation delivery notice failed to be processed. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
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
