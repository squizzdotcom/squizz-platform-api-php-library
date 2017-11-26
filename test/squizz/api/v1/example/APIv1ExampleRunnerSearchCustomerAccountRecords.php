<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Search Supplier Organisation Customer Account Records API Example</h1>
			<p>Tests making a request to the SQUIZZ.com API to create a session for an organisation, then searches for records (invoices, back orders, sales orders, credits, payments, transactions) from a connected organisation's customer account in the platform</p>
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
					use squizz\api\v1\endpoint\APIv1EndpointOrgSearchCustomerAccountRecords;
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
					$recordType = $_GET["recordType"];
					
					switch($recordType)
					{
						case ESDocumentConstants::RECORD_TYPE_ORDER_SALE:
							$recordType = ESDocumentConstants::RECORD_TYPE_ORDER_SALE;
							break;
						case ESDocumentConstants::RECORD_TYPE_BACKORDER:
							$recordType = ESDocumentConstants::RECORD_TYPE_BACKORDER;
							break;
						case ESDocumentConstants::RECORD_TYPE_TRANSACTION:
							$recordType = ESDocumentConstants::RECORD_TYPE_TRANSACTION;
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
					
					$searchBeginMonths = $_GET["searchBeginMonths"];
					$searchString = $_GET["searchString"];
					$searchType = $_GET["searchType"];
					$keyRecordIDs = $_GET["keyRecordIDs"];
					$beginDateTime = strtotime(date('Y-m-d', strtotime("-".$searchBeginMonths." months", strtotime(date("Y-m-d")))))* 1000;
					$endDateTime = round(microtime(true) * 1000);
					$pageNumber = 1;
					$recordsMaxAmount = 100;
					$outstandingRecordsOnly = false;
					
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
						$endpointResponseESD = APIv1EndpointOrgSearchCustomerAccountRecords::call(
							$apiOrgSession,
							$timeoutMilliseconds,
							$recordType,
							$supplierOrgID,
							$customerAccountCode,
							$beginDateTime,
							$endDateTime,
							$pageNumber,
							$recordsMaxAmount,
							$outstandingRecordsOnly,
							$searchString,
							$keyRecordIDs,
							$searchType
						);
						
						$esDocument = $endpointResponseESD->esDocument;
			
						//check that the data successfully imported
						if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
							
							$result = "SUCCESS";
							$resultMessage = "Account record data successfully obtained from the platform.<br/>Price records returned:".$esDocument->totalDataRecords;
							
							//output records based on the record type
							switch($recordType){
								case ESDocumentConstants::RECORD_TYPE_INVOICE:
									$resultMessage = 
										"SUCCESS - account invoice record data successfully obtained from the platform<br/>".
										"Invoice Records Returned: ".$esDocument->totalDataRecords;

									//check that invoice records have been placed into the standards document
									if ($esDocument->invoiceRecords != null)
									{	
										$resultMessage = $resultMessage."<br/>Invoice Records:".
											'<table style="width: 100%;">'.
												"<tr>".
													"<th>#</th>".
													"<th>Key Invoice ID</th>".
													"<th>Invoice ID</th>".
													"<th>Invoice Number</th>".
													"<th>Invoice Date</th>".
													"<th>Total Price (Inc Tax)</th>".
													"<th>Total Paid</th>".
													"<th>Total Owed</th>".
												"</tr>";

										//iterate through each invoice record stored within the standards document
										$recordNumber=1;
										foreach ($esDocument->invoiceRecords as $invoiceRecord)
										{
											//output details of the invoice record
											$resultMessage = $resultMessage."<tr>".
												"<td>".$recordNumber."</td>".
												"<td>".$invoiceRecord->keyInvoiceID."</td>".
												"<td>".$invoiceRecord->invoiceID."</td>".
												"<td>".$invoiceRecord->invoiceNumber."</td>".
												"<td>".date("d/m/Y", ($invoiceRecord->invoiceDate/1000))."</td>".
												"<td>".money_format('%.2n', $invoiceRecord->totalIncTax)." ".$invoiceRecord->currencyCode."</td>".
												"<td>".money_format('%.2n', $invoiceRecord->totalPaid)." ".$invoiceRecord->currencyCode."</td>".
												"<td>".money_format('%.2n', $invoiceRecord->balance)." ".$invoiceRecord->currencyCode."</td>".
											"</tr>";
											
											$recordNumber++;
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
										$resultMessage = $resultMessage."<br/>Sales Order Records:".
										'<table style="width: 100%;">'.
											"<tr>".
												"<th>#</th>".
												"<th>Key Order Sale ID</th>".
												"<th>Order ID</th>".
												"<th>Order Number</th>".
												"<th>Order Date</th>".
												"<th>Total Price (Inc Tax)</th>".
												"<th>Total Paid</th>".
												"<th>Total Owed</th>".
											"</tr>";

										//iterate through each sales order record stored within the standards document
										$recordNumber=1;
										foreach ($esDocument->orderSaleRecords as $orderSaleRecord)
										{
											//output details of the sales order record
											$resultMessage = $resultMessage."<tr>".
												"<td>".$recordNumber."</td>".
												"<td>".$orderSaleRecord->keyOrderSaleID."</td>".
												"<td>".$orderSaleRecord->orderID."</td>".
												"<td>".$orderSaleRecord->orderNumber."</td>".
												"<td>".date("d/m/Y", ($orderSaleRecord->orderDate/1000))."</td>".
												"<td>".money_format('%.2n', $orderSaleRecord->totalIncTax)." ".$orderSaleRecord->currencyCode."</td>".
												"<td>".money_format('%.2n', $orderSaleRecord->totalPaid)." ".$orderSaleRecord->currencyCode."</td>".
												"<td>".money_format('%.2n', $orderSaleRecord->balance)." ".$orderSaleRecord->currencyCode."</td>".
											"</tr>";
											
											$recordNumber++;
										}
									}
									break;
								case ESDocumentConstants::RECORD_TYPE_BACKORDER:
									
									$resultMessage = 
										"SUCCESS - account back order record data successfully obtained from the platform<br/>".
										"Back Order Records Returned: ".$esDocument->totalDataRecords;

									//check that back order records have been placed into the standards document
									if ($esDocument->backOrderRecords != null)
									{
										$resultMessage = $resultMessage."<br/>Back Order Records:".
										'<table style="width: 100%;">'.
											"<tr>".
												"<th>#</th>".
												"<th>Key Back Order ID</th>".
												"<th>Order ID</th>".
												"<th>Back Order Number</th>".
												"<th>Order Date</th>".
												"<th>Total Price (Inc Tax)</th>".
											"</tr>";

										//iterate through each back order record stored within the standards document
										$recordNumber=1;
										foreach ($esDocument->backOrderRecords as $backOrderRecord)
										{
											//output details of the back order record
											$resultMessage = $resultMessage."<tr>".
												"<td>".$recordNumber."</td>".
												"<td>".$backOrderRecord->keyBackOrderID."</td>".
												"<td>".$backOrderRecord->backOrderID."</td>".
												"<td>".$backOrderRecord->backOrderNumber."</td>".
												"<td>".date("d/m/Y", ($backOrderRecord->backOrderDate/1000))."</td>".
												"<td>".money_format('%.2n', $backOrderRecord->totalIncTax)." ".$backOrderRecord->currencyCode."</td>".
											"</tr>";
											
											$recordNumber++;
										}
									}
									break;
								case ESDocumentConstants::RECORD_TYPE_TRANSACTION:
									
									$resultMessage = 
										"SUCCESS - account transaction record data successfully obtained from the platform<br/>".
										"Transaction Returned: ".$esDocument->totalDataRecords;

									//check that transaction records have been placed into the standards document
									if ($esDocument->transactionRecords != null)
									{
										$resultMessage = $resultMessage."<br/>Transaction Records:".
										'<table style="width: 100%;">'.
											"<tr>".
												"<th>#</th>".
												"<th>Key Transaction ID</th>".
												"<th>Transaction ID</th>".
												"<th>Transaction Number</th>".
												"<th>Transaction Date</th>".
												"<th>Amount Debited</th>".
												"<th>Amount Credited</th>".
												"<th>Balance</th>".
											"</tr>";

										//iterate through each transaction record stored within the standards document
										$recordNumber=1;
										foreach ($esDocument->backOrderRecords as $transactionRecord)
										{
											//output details of the transaction record
											$resultMessage = $resultMessage."<tr>".
												"<td>".$recordNumber."</td>".
												"<td>".$transactionRecord->keyTransactionID."</td>".
												"<td>".$transactionRecord->transactionID."</td>".
												"<td>".$transactionRecord->transactionNumber."</td>".
												"<td>".date("d/m/Y", ($transactionRecord->transactionDate/1000))."</td>".
												"<td>".money_format('%.2n', $transactionRecord->debitAmount)." ".$transactionRecord->currencyCode."</td>".
												"<td>".money_format('%.2n', $transactionRecord->creditAmount)." ".$transactionRecord->currencyCode."</td>".
												"<td>".money_format('%.2n', $transactionRecord->balance)." ".$transactionRecord->currencyCode."</td>".
											"</tr>";
											
											$recordNumber++;
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
										$resultMessage = $resultMessage."<br/>Transaction Records:".
										'<table style="width: 100%;">'.
											"<tr>".
												"<th>#</th>".
												"<th>Key Credit ID</th>".
												"<th>Credit ID</th>".
												"<th>Credit Number</th>".
												"<th>Credit Date</th>".
												"<th>Amount Credited</th>".
											"</tr>";

										//iterate through each credit record stored within the standards document
										$recordNumber=1;
										foreach ($esDocument->creditRecords as $creditRecord)
										{
											//output details of the credit record
											$resultMessage = $resultMessage."<tr>".
												"<td>".$recordNumber."</td>".
												"<td>".$creditRecord->keyCreditID."</td>".
												"<td>".$creditRecord->creditID."</td>".
												"<td>".$creditRecord->creditNumber."</td>".
												"<td>".date("d/m/Y", ($creditRecord->creditDate/1000))."</td>".
												"<td>".money_format('%.2n', $creditRecord->appliedAmount)." ".$creditRecord->currencyCode."</td>".
											"</tr>";
											
											$recordNumber++;
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
										$resultMessage = $resultMessage."<br/>Transaction Records:".
										'<table style="width: 100%;">'.
											"<tr>".
												"<th>#</th>".
												"<th>Key Payment ID</th>".
												"<th>Payment ID</th>".
												"<th>Payment Number</th>".
												"<th>Payment Date</th>".
												"<th>Total Amount Paid</th>".
											"</tr>";

										//iterate through each payment record stored within the standards document
										$recordNumber=1;
										foreach ($esDocument->paymentRecords as $paymentRecord)
										{
											//output details of the payment record
											$resultMessage = $resultMessage."<tr>".
												"<td>".$recordNumber."</td>".
												"<td>".$paymentRecord->keyPaymentID."</td>".
												"<td>".$paymentRecord->paymentID."</td>".
												"<td>".$paymentRecord->paymentNumber."</td>".
												"<td>".date("d/m/Y", ($paymentRecord->paymentDate/1000))."</td>".
												"<td>".money_format('%.2n', $paymentRecord->totalAmount)." ".$paymentRecord->currencyCode."</td>".
											"</tr>";
											
											$recordNumber++;
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
