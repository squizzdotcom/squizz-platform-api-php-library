<!DOCTYPE html>
<html>
	<head>
		<style>
			td, th{
				text-align: left;
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
			<h1>Retrieve Supplier Organisation Customer Account Record API Example</h1>
			<p>Tests making a request to the SQUIZZ.com API to create a session for an organisation, then retrieves the details and lines of a single record (invoice, back order, sales order, credit, payment) from a connected organisation's customer account in the platform</p>
			<div style="max-width: 607px; background-color: #2b2b2b; color: #cacaca; text-align: center; margin: auto; padding-top: 15px;">
				<?php
					/**
					* Copyright (C) 2018 Squizz PTY LTD
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
					use squizz\api\v1\endpoint\APIv1EndpointOrgRetrieveCustomerAccountRecord;
					use squizz\api\v1\APIv1OrgSession;
					use squizz\api\v1\APIv1Constants;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryBackOrder;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryBackOrderLine;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryCredit;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryCreditLine;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryInvoice;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryInvoiceLine;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryOrderSale;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryOrderSaleLine;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryPayment;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryPaymentLine;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryQuote;
					use EcommerceStandardsDocuments\ESDRecordCustomerAccountEnquiryQuoteLine;
					use EcommerceStandardsDocuments\ESDocumentConstants;
					use EcommerceStandardsDocuments\ESDocumentCustomerAccountEnquiry;
					
					//obtain or load in an organisation's API credentials, in this example from command line arguments
					
					$orgID = $_GET["orgID"];
					$orgAPIKey = $_GET["orgAPIKey"];
					$orgAPIPass = $_GET["orgAPIPass"];
					$supplierOrgID = $_GET["supplierOrgID"];
					$sessionTimeoutMilliseconds = 60000;
					$customerAccountCode = $_GET["customerAccountCode"];
					$keyRecordID = $_GET["keyRecordID"];
					$recordType = $_GET["recordType"];
					
					switch($recordType)
					{
						case ESDocumentConstants::RECORD_TYPE_ORDER_SALE:
							$recordType = ESDocumentConstants::RECORD_TYPE_ORDER_SALE;
							break;
						case ESDocumentConstants::RECORD_TYPE_BACKORDER:
							$recordType = ESDocumentConstants::RECORD_TYPE_BACKORDER;
							break;
						case ESDocumentConstants::RECORD_TYPE_CREDIT:
							$recordType = ESDocumentConstants::RECORD_TYPE_CREDIT;
							break;
						case ESDocumentConstants::RECORD_TYPE_PAYMENT:
							$recordType = ESDocumentConstants::RECORD_TYPE_PAYMENT;
							break;
						default:
							$recordType = ESDocumentConstants::RECORD_TYPE_INVOICE;
					}
					
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
					
					//search for account records if the API was successfully created
					if($apiOrgSession->sessionExists())
					{
						//after 30 seconds give up on waiting for a response from the API
						$timeoutMilliseconds = 30000;
						
						//call the platform's API to search for customer account records from the supplier organiation's connected business system
						$endpointResponseESD = APIv1EndpointOrgRetrieveCustomerAccountRecord::call($apiOrgSession, $timeoutMilliseconds, $recordType, $supplierOrgID, $customerAccountCode, $keyRecordID);
						
						$esDocument = $endpointResponseESD->esDocument;
			
						//check that the data successfully imported
						if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
							
							$result = "SUCCESS";
							$resultMessage = "Account record data successfully obtained from the platform.<br/>Records returned:".$esDocument->totalDataRecords;
							
							//output records based on the record type
							switch($recordType){
								case ESDocumentConstants::RECORD_TYPE_INVOICE:
									$resultMessage = 
										"SUCCESS - account invoice record data successfully obtained from the platform<br/>".
										"Invoice Records Returned: ".$esDocument->totalDataRecords;

									//check that invoice records have been placed into the standards document
									if ($esDocument->invoiceRecords != null)
									{	
										//display the details of the record stored within the standards document
										foreach ($esDocument->invoiceRecords as $invoiceRecord)
										{
											//output details of the invoice record
											$resultMessage = $resultMessage."<br/>Invoice Details:".
												'<table style="width: 100%;">'.
												"<tr>".
													"<th>Invoice Field</th>".
													"<th>Value</th>".
												"</tr>".
												"<tr><td>Key Invoice ID:</td><td>" . $invoiceRecord->keyInvoiceID."</td></tr>".
												"<tr><td>Invoice ID:</td><td>" . $invoiceRecord->invoiceID."</td></tr>".
												"<tr><td>Invoice Number:</td><td>" . $invoiceRecord->invoiceNumber."</td></tr>".
												"<tr><td>Invoice Date:</td><td>" . date("d/m/Y", ($invoiceRecord->invoiceDate/1000))."</td></tr>".
												"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $invoiceRecord->totalIncTax)." ".$invoiceRecord->currencyCode."</td></tr>".
												"<tr><td>Total Paid:</td><td>" . money_format('%.2n', $invoiceRecord->totalPaid)." ".$invoiceRecord->currencyCode."</td></tr>".
												"<tr><td>Total Owed:</td><td>" . money_format('%.2n', $invoiceRecord->balance)." ".$invoiceRecord->currencyCode."</td></tr>".
												"<tr><td>Description:</td><td>" . $invoiceRecord->description."</td></tr>".
												"<tr><td>Comment:</td><td>" . $invoiceRecord->comment."</td></tr>".
												"<tr><td>Reference Number:</td><td>" . $invoiceRecord->referenceNumber."</td></tr>".
												"<tr><td>Reference Type:</td><td>" . $invoiceRecord->referenceType."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Delivery Address: </td></tr>".
												"<tr><td>Organisation Name:</td><td>" . $invoiceRecord->deliveryOrgName."</td></tr>".
												"<tr><td>Contact:</td><td>" . $invoiceRecord->deliveryContact."</td></tr>".
												"<tr><td>Address 1:</td><td>" . $invoiceRecord->deliveryAddress1."</td></tr>".
												"<tr><td>Address 2:</td><td>" . $invoiceRecord->deliveryAddress2."</td></tr>".
												"<tr><td>Address 3:</td><td>" . $invoiceRecord->deliveryAddress3."</td></tr>".
												"<tr><td>State/Province/Region:</td><td>" . $invoiceRecord->deliveryStateProvince."</td></tr>".
												"<tr><td>Country:</td><td>" . $invoiceRecord->deliveryCountry."</td></tr>".
												"<tr><td>Postcode/Zipcode:</td><td>" . $invoiceRecord->deliveryPostcode."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Billing Address:</td></tr>".
												"<tr><td>Organisation Name:</td><td>" . $invoiceRecord->billingOrgName."</td></tr>".
												"<tr><td>Contact:</td><td>" . $invoiceRecord->billingContact."</td></tr>".
												"<tr><td>Address 1:</td><td>" . $invoiceRecord->billingAddress1."</td></tr>".
												"<tr><td>Address 2:</td><td>" . $invoiceRecord->billingAddress2."</td></tr>".
												"<tr><td>Address 3:</td><td>" . $invoiceRecord->billingAddress3."</td></tr>".
												"<tr><td>State/Province/Region:</td><td>" . $invoiceRecord->billingStateProvince."</td></tr>".
												"<tr><td>Country:</td><td>" . $invoiceRecord->billingCountry."</td></tr>".
												"<tr><td>Postcode/Zipcode:</td><td>" . $invoiceRecord->billingPostcode."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Freight Details: </td></tr>".
												"<tr><td>Consignment Code:</td><td>" . $invoiceRecord->freightCarrierConsignCode."</td></tr>".
												"<tr><td>Tracking Code:</td><td>" . $invoiceRecord->freightCarrierTrackingCode."</td></tr>".
												"<tr><td>Carrier Name:</td><td>" . $invoiceRecord->freightCarrierName."</td></tr>";

											//output the details of each line
											if ($invoiceRecord->lines != null)
											{
												$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
												$lineNumber=0;
												foreach ($invoiceRecord->lines as $invoiceLineRecord)
												{
													$lineNumber++;
													$resultMessage = $resultMessage.
														"<tr><td colspan=\"2\"><hr/></td></tr>".
														"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

													if ($invoiceLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
													{
														$resultMessage = $resultMessage.
															"<tr><td>Line Type::</td><td>ITEM".
															"<tr><td>Line Item ID::</td><td>" . $invoiceLineRecord->lineItemID."</td></tr>".
															"<tr><td>Line Item Code::</td><td>" . $invoiceLineRecord->lineItemCode."</td></tr>".
															"<tr><td>Description::</td><td>" . $invoiceLineRecord->description."</td></tr>".
															"<tr><td>Quantity Ordered::</td><td>" . $invoiceLineRecord->quantityOrdered . " " . $invoiceLineRecord->unit."</td></tr>".
															"<tr><td>Quantity Delivered::</td><td>" . $invoiceLineRecord->quantityDelivered . " " . $invoiceLineRecord->unit."</td></tr>".
															"<tr><td>Quantity Back Ordered:</td><td>" . $invoiceLineRecord->quantityBackordered . " " . $invoiceLineRecord->unit."</td></tr>".
															"<tr><td>Unit Price (Ex Tax):</td><td>". money_format('%.2n', $invoiceLineRecord->priceExTax)." ".$invoiceRecord->currencyCode ."</td></tr>".
															"<tr><td>Total Price (Ex Tax):</td><td>" . money_format('%.2n', $invoiceLineRecord->totalPriceExTax)." ".$invoiceRecord->currencyCode."</td></tr>".
															"<tr><td>Total Tax:</td><td>" . money_format('%.2n', $invoiceLineRecord->totalPriceTax) . " Inclusive of " . $invoiceLineRecord->taxCode . " " . $invoiceLineRecord->taxCodeRatePercent . "%"."</td></tr>".
															"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $invoiceLineRecord->totalPriceIncTax)." ".$invoiceRecord->currencyCode."</td></tr>";
													}
													else if ($invoiceLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
													{
														$resultMessage = 
															$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
															"<tr><td>Description:</td><td>".$invoiceLineRecord.description."</td></tr>";
													}
												}
											}
											
											$resultMessage = $resultMessage.'<table style="width: 100%;">';
											break;
										}
									}
									break;
 								case ESDocumentConstants::RECORD_TYPE_ORDER_SALE:
									$resultMessage = 
										"SUCCESS - account sales order record data successfully obtained from the platform<br/>".
										"Sales Order Records Returned: ".$esDocument->totalDataRecords;

									//check that sales order records have been placed into the standards document
									if ($esDocument->orderSaleRecords != null)
									{	
										//display the details of the record stored within the standards document
										foreach ($esDocument->orderSaleRecords as $orderSaleRecord)
										{
											//output details of the sales order record
											$resultMessage = $resultMessage."<br/>Sales Order Details:".
												'<table style="width: 100%;">'.
												"<tr>".
													"<th>Sales Order Field</th>".
													"<th>Value</th>".
												"</tr>".
												"<tr><td>Key Order Sale ID:</td><td>" . $orderSaleRecord->keyOrderSaleID."</td></tr>".
												"<tr><td>Order ID:</td><td>" . $orderSaleRecord->orderID."</td></tr>".
												"<tr><td>Order Number:</td><td>" . $orderSaleRecord->orderNumber."</td></tr>".
												"<tr><td>Order Date:</td><td>" . date("d/m/Y", ($orderSaleRecord->orderDate/1000))."</td></tr>".
												"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $orderSaleRecord->totalIncTax)." ".$orderSaleRecord->currencyCode."</td></tr>".
												"<tr><td>Total Paid:</td><td>" . money_format('%.2n', $orderSaleRecord->totalPaid)." ".$orderSaleRecord->currencyCode."</td></tr>".
												"<tr><td>Total Owed:</td><td>" . money_format('%.2n', $orderSaleRecord->balance)." ".$orderSaleRecord->currencyCode."</td></tr>".
												"<tr><td>Description:</td><td>" . $orderSaleRecord->description."</td></tr>".
												"<tr><td>Comment:</td><td>" . $orderSaleRecord->comment."</td></tr>".
												"<tr><td>Reference Number:</td><td>" . $orderSaleRecord->referenceNumber."</td></tr>".
												"<tr><td>Reference Type:</td><td>" . $orderSaleRecord->referenceType."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Delivery Address: </td></tr>".
												"<tr><td>Organisation Name:</td><td>" . $orderSaleRecord->deliveryOrgName."</td></tr>".
												"<tr><td>Contact:</td><td>" . $orderSaleRecord->deliveryContact."</td></tr>".
												"<tr><td>Address 1:</td><td>" . $orderSaleRecord->deliveryAddress1."</td></tr>".
												"<tr><td>Address 2:</td><td>" . $orderSaleRecord->deliveryAddress2."</td></tr>".
												"<tr><td>Address 3:</td><td>" . $orderSaleRecord->deliveryAddress3."</td></tr>".
												"<tr><td>State/Province/Region:</td><td>" . $orderSaleRecord->deliveryStateProvince."</td></tr>".
												"<tr><td>Country:</td><td>" . $orderSaleRecord->deliveryCountry."</td></tr>".
												"<tr><td>Postcode/Zipcode:</td><td>" . $orderSaleRecord->deliveryPostcode."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Billing Address:</td></tr>".
												"<tr><td>Organisation Name:</td><td>" . $orderSaleRecord->billingOrgName."</td></tr>".
												"<tr><td>Contact:</td><td>" . $orderSaleRecord->billingContact."</td></tr>".
												"<tr><td>Address 1:</td><td>" . $orderSaleRecord->billingAddress1."</td></tr>".
												"<tr><td>Address 2:</td><td>" . $orderSaleRecord->billingAddress2."</td></tr>".
												"<tr><td>Address 3:</td><td>" . $orderSaleRecord->billingAddress3."</td></tr>".
												"<tr><td>State/Province/Region:</td><td>" . $orderSaleRecord->billingStateProvince."</td></tr>".
												"<tr><td>Country:</td><td>" . $orderSaleRecord->billingCountry."</td></tr>".
												"<tr><td>Postcode/Zipcode:</td><td>" . $orderSaleRecord->billingPostcode."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Freight Details: </td></tr>".
												"<tr><td>Consignment Code:</td><td>" . $orderSaleRecord->freightCarrierConsignCode."</td></tr>".
												"<tr><td>Tracking Code:</td><td>" . $orderSaleRecord->freightCarrierTrackingCode."</td></tr>".
												"<tr><td>Carrier Name:</td><td>" . $orderSaleRecord->freightCarrierName."</td></tr>";

											//output the details of each line
											if ($orderSaleRecord->lines != null)
											{
												$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
												$lineNumber=0;
												foreach ($orderSaleRecord->lines as $orderSaleLineRecord)
												{
													$lineNumber++;
													$resultMessage = $resultMessage.
														"<tr><td colspan=\"2\"><hr/></td></tr>".
														"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

													if ($orderSaleLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
													{
														$resultMessage = $resultMessage.
															"<tr><td>Line Type::</td><td>ITEM".
															"<tr><td>Line Item ID::</td><td>" . $orderSaleLineRecord->lineItemID."</td></tr>".
															"<tr><td>Line Item Code::</td><td>" . $orderSaleLineRecord->lineItemCode."</td></tr>".
															"<tr><td>Description::</td><td>" . $orderSaleLineRecord->description."</td></tr>".
															"<tr><td>Quantity Ordered::</td><td>" . $orderSaleLineRecord->quantityOrdered . " " . $orderSaleLineRecord->unit."</td></tr>".
															"<tr><td>Quantity Delivered::</td><td>" . $orderSaleLineRecord->quantityDelivered . " " . $orderSaleLineRecord->unit."</td></tr>".
															"<tr><td>Quantity Back Ordered:</td><td>" . $orderSaleLineRecord->quantityBackordered . " " . $orderSaleLineRecord->unit."</td></tr>".
															"<tr><td>Unit Price (Ex Tax):</td><td>". money_format('%.2n', $orderSaleLineRecord->priceExTax)." ".$orderSaleRecord->currencyCode ."</td></tr>".
															"<tr><td>Total Price (Ex Tax):</td><td>" . money_format('%.2n', $orderSaleLineRecord->totalPriceExTax)." ".$orderSaleRecord->currencyCode."</td></tr>".
															"<tr><td>Total Tax:</td><td>" . money_format('%.2n', $orderSaleLineRecord->totalPriceTax) . " Inclusive of " . $orderSaleLineRecord->taxCode . " " . $orderSaleLineRecord->taxCodeRatePercent . "%"."</td></tr>".
															"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $orderSaleLineRecord->totalPriceIncTax)." ".$orderSaleRecord->currencyCode."</td></tr>";
													}
													else if ($orderSaleLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
													{
														$resultMessage = 
															$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
															"<tr><td>Description:</td><td>".$orderSaleLineRecord.description."</td></tr>";
													}
												}
											}
											
											$resultMessage = $resultMessage.'<table style="width: 100%;">';
											break;
										}
									}
									break;
								case ESDocumentConstants::RECORD_TYPE_BACKORDER:
									$resultMessage = 
										"SUCCESS - account back order record data successfully obtained from the platform<br/>".
										"Sales Order Records Returned: ".$esDocument->totalDataRecords;

									//check that back order records have been placed into the standards document
									if ($esDocument->backOrderRecords != null)
									{	
										//display the details of the record stored within the standards document
										foreach ($esDocument->backOrderRecords as $backOrderRecord)
										{
// 											//output details of the back order record
											$resultMessage = $resultMessage."<br/>Back Order Details:".
												'<table style="width: 100%;">'.
												"<tr>".
													"<th>Back Order Field</th>".
													"<th>Value</th>".
												"</tr>".
												"<tr><td>Key Back Order ID:</td><td>" . $backOrderRecord->keyBackOrderID."</td></tr>".
												"<tr><td>Back Order ID:</td><td>" . $backOrderRecord->backOrderID."</td></tr>".
												"<tr><td>Back Order Number:</td><td>" . $backOrderRecord->backOrderNumber."</td></tr>".
												"<tr><td>Back Order Date:</td><td>" . date("d/m/Y", ($backOrderRecord->backOrderDate/1000))."</td></tr>".
												"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $backOrderRecord->totalIncTax)." ".$backOrderRecord->currencyCode."</td></tr>".
												"<tr><td>Total Paid:</td><td>" . money_format('%.2n', $backOrderRecord->totalPaid)." ".$backOrderRecord->currencyCode."</td></tr>".
												"<tr><td>Total Owed:</td><td>" . money_format('%.2n', $backOrderRecord->balance)." ".$backOrderRecord->currencyCode."</td></tr>".
												"<tr><td>Description:</td><td>" . $backOrderRecord->description."</td></tr>".
												"<tr><td>Comment:</td><td>" . $backOrderRecord->comment."</td></tr>".
												"<tr><td>Reference Number:</td><td>" . $backOrderRecord->referenceNumber."</td></tr>".
												"<tr><td>Reference Type:</td><td>" . $backOrderRecord->referenceType."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Delivery Address: </td></tr>".
												"<tr><td>Organisation Name:</td><td>" . $backOrderRecord->deliveryOrgName."</td></tr>".
												"<tr><td>Contact:</td><td>" . $backOrderRecord->deliveryContact."</td></tr>".
												"<tr><td>Address 1:</td><td>" . $backOrderRecord->deliveryAddress1."</td></tr>".
												"<tr><td>Address 2:</td><td>" . $backOrderRecord->deliveryAddress2."</td></tr>".
												"<tr><td>Address 3:</td><td>" . $backOrderRecord->deliveryAddress3."</td></tr>".
												"<tr><td>State/Province/Region:</td><td>" . $backOrderRecord->deliveryStateProvince."</td></tr>".
												"<tr><td>Country:</td><td>" . $backOrderRecord->deliveryCountry."</td></tr>".
												"<tr><td>Postcode/Zipcode:</td><td>" . $backOrderRecord->deliveryPostcode."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Billing Address:</td></tr>".
												"<tr><td>Organisation Name:</td><td>" . $backOrderRecord->billingOrgName."</td></tr>".
												"<tr><td>Contact:</td><td>" . $backOrderRecord->billingContact."</td></tr>".
												"<tr><td>Address 1:</td><td>" . $backOrderRecord->billingAddress1."</td></tr>".
												"<tr><td>Address 2:</td><td>" . $backOrderRecord->billingAddress2."</td></tr>".
												"<tr><td>Address 3:</td><td>" . $backOrderRecord->billingAddress3."</td></tr>".
												"<tr><td>State/Province/Region:</td><td>" . $backOrderRecord->billingStateProvince."</td></tr>".
												"<tr><td>Country:</td><td>" . $backOrderRecord->billingCountry."</td></tr>".
												"<tr><td>Postcode/Zipcode:</td><td>" . $backOrderRecord->billingPostcode."</td></tr>";

											//output the details of each line
											if ($backOrderRecord->lines != null)
											{
												$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
												$lineNumber=0;
												foreach ($backOrderRecord->lines as $backOrderLineRecord)
												{
													$lineNumber++;
													$resultMessage = $resultMessage.
														"<tr><td colspan=\"2\"><hr/></td></tr>".
														"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

													if ($backOrderLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
													{
														$resultMessage = $resultMessage.
															"<tr><td>Line Type::</td><td>ITEM".
															"<tr><td>Line Item ID::</td><td>" . $backOrderLineRecord->lineItemID."</td></tr>".
															"<tr><td>Line Item Code::</td><td>" . $backOrderLineRecord->lineItemCode."</td></tr>".
															"<tr><td>Description::</td><td>" . $backOrderLineRecord->description."</td></tr>".
															"<tr><td>Quantity Ordered::</td><td>" . $backOrderLineRecord->quantityOrdered . " " . $backOrderLineRecord->unit."</td></tr>".
															"<tr><td>Quantity Delivered::</td><td>" . $backOrderLineRecord->quantityDelivered . " " . $backOrderLineRecord->unit."</td></tr>".
															"<tr><td>Quantity Back Ordered:</td><td>" . $backOrderLineRecord->quantityBackordered . " " . $backOrderLineRecord->unit."</td></tr>".
															"<tr><td>Unit Price (Ex Tax):</td><td>". money_format('%.2n', $backOrderLineRecord->priceExTax)." ".$backOrderRecord->currencyCode ."</td></tr>".
															"<tr><td>Total Price (Ex Tax):</td><td>" . money_format('%.2n', $backOrderLineRecord->totalPriceExTax)." ".$backOrderRecord->currencyCode."</td></tr>".
															"<tr><td>Total Tax:</td><td>" . money_format('%.2n', $backOrderLineRecord->totalPriceTax) . " Inclusive of " . $backOrderLineRecord->taxCode . " " . $backOrderLineRecord->taxCodeRatePercent . "%"."</td></tr>".
															"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $backOrderLineRecord->totalPriceIncTax)." ".$backOrderRecord->currencyCode."</td></tr>";
													}
													else if ($backOrderLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
													{
														$resultMessage = 
															$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
															"<tr><td>Description:</td><td>".$backOrderLineRecord.description."</td></tr>";
													}
												}
											}
											
											$resultMessage = $resultMessage.'<table style="width: 100%;">';
											break;
										}
									}
									break;
								case ESDocumentConstants::RECORD_TYPE_CREDIT:
									$resultMessage = 
										"SUCCESS - account credit record data successfully obtained from the platform<br/>".
										"Credit Records Returned: ".$esDocument->totalDataRecords;

									//check that credit records have been placed into the standards document
									if ($esDocument->creditRecords != null)
									{	
										//display the details of the record stored within the standards document
										foreach ($esDocument->creditRecords as $creditRecord)
										{
// 											//output details of the credit record
											$resultMessage = $resultMessage."<br/>Credit Details:".
												'<table style="width: 100%;">'.
												"<tr>".
													"<th>Credit Field</th>".
													"<th>Value</th>".
												"</tr>".
												"<tr><td>Key Credit ID:</td><td>" . $creditRecord->keyCreditID."</td></tr>".
												"<tr><td>Credit ID:</td><td>" . $creditRecord->creditID."</td></tr>".
												"<tr><td>Credit Number:</td><td>" . $creditRecord->creditNumber."</td></tr>".
												"<tr><td>Credit Date:</td><td>" . date("d/m/Y", ($creditRecord->creditDate/1000))."</td></tr>".
												"<tr><td>Amount Credited:</td><td>" . money_format('%.2n', $creditRecord->appliedAmount)." ".$creditRecord->currencyCode."</td></tr>".
												"<tr><td>Description:</td><td>" . $creditRecord->description."</td></tr>".
												"<tr><td>Comment:</td><td>" . $creditRecord->comment."</td></tr>".
												"<tr><td>Reference Number:</td><td>" . $creditRecord->referenceNumber."</td></tr>".
												"<tr><td>Reference Type:</td><td>" . $creditRecord->referenceType."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Delivery Address: </td></tr>".
												"<tr><td>Organisation Name:</td><td>" . $creditRecord->deliveryOrgName."</td></tr>".
												"<tr><td>Contact:</td><td>" . $creditRecord->deliveryContact."</td></tr>".
												"<tr><td>Address 1:</td><td>" . $creditRecord->deliveryAddress1."</td></tr>".
												"<tr><td>Address 2:</td><td>" . $creditRecord->deliveryAddress2."</td></tr>".
												"<tr><td>Address 3:</td><td>" . $creditRecord->deliveryAddress3."</td></tr>".
												"<tr><td>State/Province/Region:</td><td>" . $creditRecord->deliveryStateProvince."</td></tr>".
												"<tr><td>Country:</td><td>" . $creditRecord->deliveryCountry."</td></tr>".
												"<tr><td>Postcode/Zipcode:</td><td>" . $creditRecord->deliveryPostcode."</td></tr>".
												"<tr><td></td></tr>".
												"<tr><td>Billing Address:</td></tr>".
												"<tr><td>Organisation Name:</td><td>" . $creditRecord->billingOrgName."</td></tr>".
												"<tr><td>Contact:</td><td>" . $creditRecord->billingContact."</td></tr>".
												"<tr><td>Address 1:</td><td>" . $creditRecord->billingAddress1."</td></tr>".
												"<tr><td>Address 2:</td><td>" . $creditRecord->billingAddress2."</td></tr>".
												"<tr><td>Address 3:</td><td>" . $creditRecord->billingAddress3."</td></tr>".
												"<tr><td>State/Province/Region:</td><td>" . $creditRecord->billingStateProvince."</td></tr>".
												"<tr><td>Country:</td><td>" . $creditRecord->billingCountry."</td></tr>".
												"<tr><td>Postcode/Zipcode:</td><td>" . $creditRecord->billingPostcode."</td></tr>";

											//output the details of each line
											if ($creditRecord->lines != null)
											{
												$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
												$lineNumber=0;
												foreach ($creditRecord->lines as $creditLineRecord)
												{
													$lineNumber++;
													$resultMessage = $resultMessage.
														"<tr><td colspan=\"2\"><hr/></td></tr>".
														"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

													if ($creditLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
													{
														$resultMessage = $resultMessage.
															"<tr><td>Line Type:</td><td>ITEM".
															"<tr><td>Line Item ID:</td><td>" . $creditLineRecord->lineItemID."</td></tr>".
															"<tr><td>Line Item Code:</td><td>" . $creditLineRecord->lineItemCode."</td></tr>".
															"<tr><td>Description:</td><td>" . $creditLineRecord->description."</td></tr>".
															"<tr><td>Reference Number:</td><td>" . $creditLineRecord->referenceNumber."</td></tr>".
															"<tr><td>Reference Type:</td><td>" . $creditLineRecord->referenceType ."</td></tr>".
															"<tr><td>Reference Key ID:</td><td>" . $creditLineRecord->referenceKeyID ."</td></tr>".
															"<tr><td>Total Price (Inc Tax):</td><td>" . money_format('%.2n', $creditLineRecord->totalPriceIncTax)." ".$creditLineRecord->currencyCode."</td></tr>";
													}
													else if ($creditLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
													{
														$resultMessage = 
															$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
															"<tr><td>Description:</td><td>".$creditLineRecord.description."</td></tr>";
													}
												}
											}
											
											$resultMessage = $resultMessage.'<table style="width: 100%;">';
											break;
										}
									}
									break;
								case ESDocumentConstants::RECORD_TYPE_PAYMENT:
									$resultMessage = 
										"SUCCESS - account payment record data successfully obtained from the platform<br/>".
										"Payment Records Returned: ".$esDocument->totalDataRecords;

									//check that payment records have been placed into the standards document
									if ($esDocument->paymentRecords != null)
									{	
										//display the details of the record stored within the standards document
										foreach ($esDocument->paymentRecords as $paymentRecord)
										{
// 											//output details of the payment record
											$resultMessage = $resultMessage."<br/>Payment Details:".
												'<table style="width: 100%;">'.
												"<tr>".
													"<th>Payment Field</th>".
													"<th>Value</th>".
												"</tr>".
												"<tr><td>Key Payment ID:</td><td>" . $paymentRecord->keyPaymentID."</td></tr>".
												"<tr><td>Payment ID:</td><td>" . $paymentRecord->paymentID."</td></tr>".
												"<tr><td>Payment Number:</td><td>" . $paymentRecord->paymentNumber."</td></tr>".
												"<tr><td>Payment Date:</td><td>" . date("d/m/Y", ($paymentRecord->paymentDate/1000))."</td></tr>".
												"<tr><td>Total Amount:</td><td>" . money_format('%.2n', $paymentRecord->totalAmount)." ".$paymentRecord->currencyCode."</td></tr>".
												"<tr><td>Description:</td><td>" . $paymentRecord->description."</td></tr>".
												"<tr><td>Comment:</td><td>" . $paymentRecord->comment."</td></tr>".
												"<tr><td>Reference Number:</td><td>" . $paymentRecord->referenceNumber."</td></tr>".
												"<tr><td>Reference Type:</td><td>" . $paymentRecord->referenceType."</td></tr>".
												"<tr><td>Reference Key ID:</td><td>" . $paymentRecord->referenceKeyID."</td></tr>";

											//output the details of each line
											if ($paymentRecord->lines != null)
											{
												$resultMessage = $resultMessage."<tr><td>Lines:</td></tr>";
												$lineNumber=0;
												foreach ($paymentRecord->lines as $paymentOrderLineRecord)
												{
													$lineNumber++;
													$resultMessage = $resultMessage.
														"<tr><td colspan=\"2\"><hr/></td></tr>".
														"<tr><td>Line Number:</td><td>".$lineNumber."</td></tr>";

													if ($paymentOrderLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_ITEM)
													{
														$resultMessage = $resultMessage.
															"<tr><td>Line Type:</td><td>ITEM".
															"<tr><td>Line Item ID:</td><td>" . $paymentOrderLineRecord->lineItemID."</td></tr>".
															"<tr><td>Line Item Code:</td><td>" . $paymentOrderLineRecord->lineItemCode."</td></tr>".
															"<tr><td>Description:</td><td>" . $paymentOrderLineRecord->description."</td></tr>".
															"<tr><td>Reference Number:</td><td>" . $paymentOrderLineRecord->referenceNumber."</td></tr>".
															"<tr><td>Reference Type:</td><td>" . $paymentOrderLineRecord->referenceType ."</td></tr>".
															"<tr><td>Reference Key ID:</td><td>" . $paymentOrderLineRecord->referenceKeyID ."</td></tr>".
															"<tr><td>Payment Amount:</td><td>" . money_format('%.2n', $paymentOrderLineRecord->amount)." ".$paymentRecord->currencyCode."</td></tr>";
													}
													else if ($paymentOrderLineRecord->lineType == ESDocumentConstants::RECORD_LINE_TYPE_TEXT)
													{
														$resultMessage = 
															$resultMessage."<tr><td>Line Type:</td><td>TEXT</td></tr>".
															"<tr><td>Description:</td><td>".$paymentOrderLineRecord.description."</td></tr>";
													}
												}
											}
											
											$resultMessage = $resultMessage.'<table style="width: 100%;">';
											break;
										}
									}
									break;
							}
						}else{
							$result = "FAIL";
							$resultMessage = "Organisation data failed to be obtained from the platform. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
						}
					}
					
					//next steps
					//call other API endpoints...
					//destroy API session when done...
					$apiOrgSession->destroyOrgSession();
					
					echo "<div>Result:<div>";
					echo "<div><b>$result</b><div><br/>";
					echo "<div>Message:<div>";
					echo "<div>$resultMessage<div><br/>";
				?>
			</div>
		</div>
	</body>
</html>
