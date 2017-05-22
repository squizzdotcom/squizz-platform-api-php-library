<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Procure Purchase Order From Supplier API Example</h1>
			<p>Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to push a purchase order to a supplier for processing and procurement</p>
			<div style="max-width: 607px; background-color: #2b2b2b; color: #cacaca; text-align: center; margin: auto; padding-top: 15px;">
				<?php
					/**
					* Copyright (C) 2017 Squizz PTY LTD
					* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
					* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
					* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
					*/
					
					//set automatic loader of the library's classes
					require_once __DIR__ . '/../../../../../../3rd-party/jsonmapper/JsonMapper.php';
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
						
						$apiNamespace = "org\\squizz\\api\\v1";
						$esdNamespace = "org\\esd\\EcommerceStandardsDocuments";
						
						//set absolute path to API php class files
						if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
							$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
						}
						//set absolute path to ESD library files
						else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
							$fileName = '/opt/squizz/esd-php-library/src/' . $fileName;
						}
						
						require $fileName;
					});
					
					use org\squizz\api\v1\endpoint\APIv1EndpointResponse;
					use org\squizz\api\v1\endpoint\APIv1EndpointResponseESD;
					use org\squizz\api\v1\endpoint\APIv1EndpointOrgProcurePurchaseOrderFromSupplier;
					use org\squizz\api\v1\APIv1OrgSession;
					use org\squizz\api\v1\APIv1Constants;
					use org\esd\EcommerceStandardsDocuments\ESDRecordOrderPurchase;
					use org\esd\EcommerceStandardsDocuments\ESDRecordOrderPurchaseLine;
					use org\esd\EcommerceStandardsDocuments\ESDocumentConstants;
					use org\esd\EcommerceStandardsDocuments\ESDocumentOrderSale;
					use org\esd\EcommerceStandardsDocuments\ESDocumentOrderPurchase;
					
					//obtain or load in an organisation's API credentials, in this example from command line arguments
					$orgID = $_GET["orgID"];
					$orgAPIKey = $_GET["orgAPIKey"];
					$orgAPIPass = $_GET["orgAPIPass"];
					$supplierOrgID = $_GET["supplierOrgID"];
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
					
					//sand and procure purchsae order if the API was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create purchase order record to import
						$purchaseOrderRecord = new ESDRecordOrderPurchase();
						
						//set data within the purchase order
						$purchaseOrderRecord->keyPurchaseOrderID = "111";
						$purchaseOrderRecord->purchaseOrderCode = "POEXAMPLE-345";
						$purchaseOrderRecord->purchaseOrderNumber = "345";
						$purchaseOrderRecord->purchaseOrderNumber = "345";
						$purchaseOrderRecord->instructions = "Leave goods at the back entrance";
						$purchaseOrderRecord->keySupplierAccountID = "2";
						$purchaseOrderRecord->supplierAccountCode = "ACM-002";
						
						//set delivery address that ordered goods will be delivered to
						$purchaseOrderRecord->deliveryAddress1 = "32";
						$purchaseOrderRecord->deliveryAddress2 = "Main Street";
						$purchaseOrderRecord->deliveryAddress3 = "Melbourne";
						$purchaseOrderRecord->deliveryRegionName = "Victoria";
						$purchaseOrderRecord->deliveryCountryName = "Australia";
						$purchaseOrderRecord->deliveryPostcode = "3000";
						$purchaseOrderRecord->deliveryOrgName = "Acme Industries";
						$purchaseOrderRecord->deliveryContact = "Jane Doe";
						
						//set billing address that the order will be billed to for payment
						$purchaseOrderRecord->billingAddress1 = "43";
						$purchaseOrderRecord->billingAddress2 = " High Street";
						$purchaseOrderRecord->billingAddress3 = "Melbourne";
						$purchaseOrderRecord->billingRegionName = "Victoria";
						$purchaseOrderRecord->billingCountryName = "Australia";
						$purchaseOrderRecord->billingPostcode = "3000";
						$purchaseOrderRecord->billingOrgName = "Acme Industries International";
						$purchaseOrderRecord->billingContact = "John Citizen";
						
						//create an array of purchase order lines
						$orderLines = array();
						
						//create purchase order line record
						$orderProduct = new ESDRecordOrderPurchaseLine();
						$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_PRODUCT;
						$orderProduct->productCode = "TEA-TOWEL-GREEN";
						$orderProduct->productName = "Green tea towel - 30 x 6 centimetres";
						$orderProduct->keySellUnitID = "2";
						$orderProduct->unitName = "EACH";
						$orderProduct->quantity = 4;
						$orderProduct->sellUnitBaseQuantity = 4;
						$orderProduct->salesOrderLineCode = "ACME-TTGREEN"; 
						$orderProduct->priceExTax = 5.00;
						$orderProduct->priceIncTax = 5.50;
						$orderProduct->priceTax = 0.50;
						$orderProduct->priceTotalIncTax = 22.00;
						$orderProduct->priceTotalExTax = 20.00;
						$orderProduct->priceTotalTax = 2.00;
						
						//add order line to lines list
						array_push($orderLines, $orderProduct);
						
						//add order lines to the order
						$purchaseOrderRecord->lines = $orderLines;
					
						//create purchase order records list and add purchase order to it
						$purchaseOrderRecords = array();
						array_push($purchaseOrderRecords, $purchaseOrderRecord);
						
						//after 60 seconds give up on waiting for a response from the API when creating the notification
						$timeoutMilliseconds = 60000;
						
						//create purchase order Ecommerce Standards document and add purchse order records to the document
						$orderPurchaseESD = new ESDocumentOrderPurchase(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $purchaseOrderRecords, array());

						//send purchase order document to the API for procurement by the supplier organisation
						$endpointResponseESD = APIv1EndpointOrgProcurePurchaseOrderFromSupplier::call($apiOrgSession, $timeoutMilliseconds, $supplierOrgID, "", $orderPurchaseESD);
						//$esDocumentOrderSale = (ESDocumentOrderSale) $endpointResponseESD->esDocument;
						$esDocumentOrderSale = $endpointResponseESD->esDocument;
						
						//check the result of procuring the purchase orders
						if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
							$result = "SUCCESS";
							$resultMessage = "Organisation purchase orders have successfully been sent to supplier organisation.";
							
							//iterate through each of the returned sales orders and output the details of the sales orders
							if($esDocumentOrderSale->dataRecords != null){
								foreach($esDocumentOrderSale->dataRecords as &$salesOrderRecord)
								{								
									$resultMessage = $resultMessage . "<br/><br/>Sales Order Returned, Order Details: <br/>";
									$resultMessage = $resultMessage . "Sales Order Code: " . $salesOrderRecord->salesOrderCode . "<br/>";
									$resultMessage = $resultMessage . "Sales Order Total Cost: " . $salesOrderRecord->totalPriceIncTax . " (" . $salesOrderRecord->currencyISOCode . ")" . "<br/>";
									$resultMessage = $resultMessage . "Sales Order Total Taxes: " . $salesOrderRecord->totalTax . " (" . $salesOrderRecord->currencyISOCode . ")" . "<br/>";
									$resultMessage = $resultMessage . "Sales Order Customer Account: " . $salesOrderRecord->customerAccountCode . "<br/>";
									$resultMessage = $resultMessage . "Sales Order Total Lines: " . $salesOrderRecord->totalLines;
								}
							}
						}else{
							$result = "FAIL";
							$resultMessage = "Organisation purchase orders failed to be processed. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							
							//if one or more products in the purchase order could not match a product for the supplier organisation then find out the order lines caused the problem
							if($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_PRODUCT_NOT_MAPPED && $esDocumentOrderSale != null)
							{
								//get a list of order lines that could not be mapped
								$unmappedLines = APIv1EndpointOrgProcurePurchaseOrderFromSupplier::getUnmappedOrderLines($esDocumentOrderSale);
								
								//iterate through each unmapped order line
								foreach($unmappedLines as $orderIndex => $lineIndex)
								{								
									//check that the order can be found that contains the problematic line
									if($orderIndex < count($orderPurchaseESD->dataRecords) && $lineIndex < count($orderPurchaseESD->dataRecords[$orderIndex]->lines)){
										$resultMessage = $resultMessage . "<br/>For purchase order: " . $orderPurchaseESD->dataRecords[$orderIndex]->purchaseOrderCode . " a matching supplier product for line number: " . ($lineIndex+1) . " could not be found.";
									}
								}
							}
							//if one or more products in the purchase order could not be priced by the supplier organisation then find the order line that caused the problem
							elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_MAPPED_PRODUCT_PRICE_NOT_FOUND && $esDocumentOrderSale != null)
							{
								if(array_key_exists(APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNPRICED_LINES, $esDocumentOrderSale->configs))
								{
									//get a list of order lines that could not be priced
									$unpricedLines = APIv1EndpointOrgProcurePurchaseOrderFromSupplier::getUnpricedOrderLines($esDocumentOrderSale);

									//iterate through each unpriced order line
									foreach($unpricedLines as $orderIndex => $lineIndex)
									{
										//check that the order can be found that contains the problematic line
										if($orderIndex < count($orderPurchaseESD->dataRecords) && $lineIndex < count($orderPurchaseESD->dataRecords[$orderIndex]->lines)){
											$resultMessage = $resultMessage . "For purchase order: " . $orderPurchaseESD->dataRecords[$orderIndex]->purchaseOrderCode . " the supplier has not set pricing for line number: " . ($lineIndex+1);
										}
									}
								}
							}
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