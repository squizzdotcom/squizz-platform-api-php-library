<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Import Organisation Sales Order API Example</h1>
			<p>Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to import the organisation's own sales order into the SQUIZZ.com against the organsation</p>
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
					use squizz\api\v1\endpoint\APIv1EndpointOrgImportSalesOrder;
					use squizz\api\v1\APIv1OrgSession;
					use squizz\api\v1\APIv1Constants;
					use EcommerceStandardsDocuments\ESDRecordOrderSale;
					use EcommerceStandardsDocuments\ESDRecordOrderSaleLine;
					use EcommerceStandardsDocuments\ESDRecordOrderLineTax;
					use EcommerceStandardsDocuments\ESDRecordOrderSurcharge;
					use EcommerceStandardsDocuments\ESDRecordOrderPayment;
					use EcommerceStandardsDocuments\ESDocumentConstants;
					use EcommerceStandardsDocuments\ESDocumentOrderSale;
					
					//obtain or load in an organisation's API credentials, in this example from command line arguments
					$orgID = $_GET["orgID"];
					$orgAPIKey = $_GET["orgAPIKey"];
					$orgAPIPass = $_GET["orgAPIPass"];
					$rePriceOrder = $_GET["rePriceOrder"];
					$supplierOrgID = $_GET["supplierOrgID"];
					$customerAccountCode = $_GET["customerAccountCode"];

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
					
					//create and send sales order if the API was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create sales order record to import
						$salesOrderRecord = new ESDRecordOrderSale();
						
						//set data within the sales order
						$salesOrderRecord->keySalesOrderID = "111";
						$salesOrderRecord->salesOrderCode = "SOEXAMPLE-678";
						$salesOrderRecord->salesOrderNumber = "678";
						$salesOrderRecord->instructions = "Leave goods at the back entrance";
						//$salesOrderRecord->keyCustomerAccountID = "3";
						$salesOrderRecord->keyCustomerAccountID = "1";
						$salesOrderRecord->customerAccountCode = "CUS-003";
						$salesOrderRecord->customerAccountName = "Acme Industries";
						$salesOrderRecord->customerEntity = ESDocumentConstants::ENTITY_TYPE_ORG;
						$salesOrderRecord->customerOrgName = "Acme Industries Pty Ltd";

						//set delivery address that ordered goods will be delivered to
						$salesOrderRecord->deliveryAddress1 = "32";
						$salesOrderRecord->deliveryAddress2 = "Main Street";
						$salesOrderRecord->deliveryAddress3 = "Melbourne";
						$salesOrderRecord->deliveryRegionName = "Victoria";
						$salesOrderRecord->deliveryCountryName = "Australia";
						$salesOrderRecord->deliveryPostcode = "3000";
						$salesOrderRecord->deliveryOrgName = "Acme Industries";
						$salesOrderRecord->deliveryContact = "Jane Doe";

						//set billing address that the order will be billed to for payment
						$salesOrderRecord->billingAddress1 = "43";
						$salesOrderRecord->billingAddress2 = " High Street";
						$salesOrderRecord->billingAddress3 = "Melbourne";
						$salesOrderRecord->billingRegionName = "Victoria";
						$salesOrderRecord->billingCountryName = "Australia";
						$salesOrderRecord->billingPostcode = "3000";
						$salesOrderRecord->billingOrgName = "Acme Industries International";
						$salesOrderRecord->billingContact = "John Citizen";
						
						//create an array of sales order lines
						$orderLines = array();
						
						//create sales order line record
						$orderProduct = new ESDRecordOrderSaleLine();
						$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_PRODUCT;
						//set mandatory data in line fields
						$orderProduct->productCode = "TEA-TOWEL-GREEN";
						$orderProduct->productName = "Green tea towel - 30 x 6 centimetres";
						$orderProduct->quantity = 4;
						$orderProduct->keySellUnitID = "EA";
						$orderProduct->unitName = "EACH";
						$orderProduct->sellUnitBaseQuantity = 4;
						//pricing data only needs to be set if the order isn't being repriced
						$orderProduct->priceExTax = 5.00;
						$orderProduct->priceIncTax = 5.50;
						$orderProduct->priceTax = 0.50;
						$orderProduct->priceTotalIncTax = 22.00;
						$orderProduct->priceTotalExTax = 20.00;
						$orderProduct->priceTotalTax = 2.00;
						
						//add order line to lines list
						array_push($orderLines, $orderProduct);
						
						//add taxes to the line
						$orderLineTaxes = array();
						$orderProduct->taxes = $orderLineTaxes;
						$orderProductTax = new ESDRecordOrderLineTax();
						$orderProductTax->keyTaxcodeID = "TAXCODE-1";
						$orderProductTax->taxcode = "GST";
						$orderProductTax->taxcodeLabel = "Goods And Services Tax";
						//pricing data only needs to be set if the order isn't being repriced
						$orderProductTax->priceTax = 0.50;
						$orderProductTax->taxRate = 10;
						$orderProductTax->quantity = 4;
						$orderProductTax->priceTotalTax = 2.00;
						array_push($orderLineTaxes, $orderProductTax);
						
						//add a 2nd sales order line record that is a text line
						$orderProduct = new ESDRecordOrderSaleLine();
						$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_TEXT;
						$orderProduct->textDescription = "Please bundle tea towels into a box";
						array_push($orderLines, $orderProduct);
						
						//add a 3rd sales order line record
						$orderProduct = new ESDRecordOrderSaleLine();
						$orderProduct->lineType = ESDocumentConstants::ORDER_LINE_TYPE_PRODUCT;
						$orderProduct->productCode = "TEA-TOWEL-BLUE";
						$orderProduct->productName = "Blue tea towel - 30 x 6 centimetres";
						$orderProduct->unitName = "BOX";
						$orderProduct->keySellUnitID = "BOX-OF-10";
						$orderProduct->quantity = 2;
						$orderProduct->sellUnitBaseQuantity = 20;
						//pricing data only needs to be set if the order isn't being repriced
						$orderProduct->priceExTax = 10.00;
						$orderProduct->priceIncTax = 11.00;
						$orderProduct->priceTax = 1.00;
						$orderProduct->priceTotalIncTax = 22.00;
						$orderProduct->priceTotalExTax = 20.00;
						$orderProduct->priceTotalTax = 2.00;
						array_push($orderLines, $orderProduct);
						
						//add taxes to the line
						$orderLineTaxes = array();
						$orderProduct->taxes = $orderLineTaxes;
						$orderProductTax = new ESDRecordOrderLineTax();
						$orderProductTax->keyTaxcodeID = "TAXCODE-1";
						$orderProductTax->taxcode = "GST";
						$orderProductTax->taxcodeLabel = "Goods And Services Tax";
						//pricing data only needs to be set if the order isn't being repriced
						$orderProductTax->priceTax = 1.00;
						$orderProductTax->taxRate = 10;
						$orderProductTax->quantity = 2;
						$orderProductTax->priceTotalTax = 2.00;
						array_push($orderLineTaxes, $orderProductTax);
						
						//add order lines to the order
						$salesOrderRecord->lines = $orderLines;
						
						//create an array of sales order surcharges
						$orderSurcharges = array();
						
						//create sales order surcharge record
						$orderSurcharge = new ESDRecordOrderSurcharge();
						$orderSurcharge->surchargeCode = "FREIGHT-FEE";
						$orderSurcharge->surchargeLabel = "Freight Surcharge";
						$orderSurcharge->surchargeDescription = "Cost of freight delivery";
						$orderSurcharge->keySurchargeID = "SURCHARGE-1";
						//pricing data only needs to be set if the order isn't being repriced
						$orderSurcharge->priceExTax = 3.00;
						$orderSurcharge->priceIncTax = 3.30;
						$orderSurcharge->priceTax = 0.30;
						array_push($orderSurcharges, $orderSurcharge);
						
						//add taxes to the surcharge
						$orderSurchargeTaxes = array();
						$orderSurcharge->taxes = $orderSurchargeTaxes;
						$orderSurchargeTax = new ESDRecordOrderLineTax();
						$orderSurchargeTax->keyTaxcodeID = "TAXCODE-1";
						$orderSurchargeTax->taxcode = "GST";
						$orderSurchargeTax->taxcodeLabel = "Goods And Services Tax";
						$orderSurchargeTax->quantity = 1;
						//pricing data only needs to be set if the order isn't being repriced
						$orderSurchargeTax->priceTax = 0.30;
						$orderSurchargeTax->taxRate = 10;
						$orderSurchargeTax->priceTotalTax = 0.30;
						array_push($orderSurchargeTaxes, $orderSurchargeTax);
						
						//create 2nd sales order surcharge record
						$orderSurcharge = new ESDRecordOrderSurcharge();
						$orderSurcharge->surchargeCode = "PAYMENT-FEE";
						$orderSurcharge->surchargeLabel = "Credit Card Surcharge";
						$orderSurcharge->surchargeDescription = "Cost of Credit Card Payment";
						$orderSurcharge->keySurchargeID = "SURCHARGE-2";
						//pricing data only needs to be set if the order isn't being repriced
						$orderSurcharge->priceExTax = 5.00;
						$orderSurcharge->priceIncTax = 5.50;
						$orderSurcharge->priceTax = 0.50;
						array_push($orderSurcharges, $orderSurcharge);
						
						//add taxes to the 2nd surcharge
						$orderSurchargeTaxes = array();
						$orderSurcharge->taxes = $orderSurchargeTaxes;
						$orderSurchargeTax = new ESDRecordOrderLineTax();
						$orderSurchargeTax->keyTaxcodeID = "TAXCODE-1";
						$orderSurchargeTax->taxcode = "GST";
						$orderSurchargeTax->taxcodeLabel = "Goods And Services Tax";
						//pricing data only needs to be set if the order isn't being repriced
						$orderSurchargeTax->priceTax = 0.50;
						$orderSurchargeTax->taxRate = 10;
						$orderSurchargeTax->quantity = 1;
						$orderSurchargeTax->priceTotalTax = 5.00;
						array_push($orderSurchargeTaxes, $orderSurchargeTax);
						
						//add surcharges to the order
						$salesOrderRecord->surcharges = $orderSurcharges;
						
						//create an array of sales order payments
						$orderPayments = array();
						
						//create sales order payment record
						$orderPayment = new ESDRecordOrderPayment();
						$orderPayment->paymentMethod = ESDocumentConstants::PAYMENT_METHOD_CREDIT;
						$orderPayment->paymentProprietaryCode = "Freight Surcharge";
						$orderPayment->paymentReceipt = "3422ads2342233";
						$orderPayment->keyPaymentTypeID = "VISA-CREDIT-CARD";
						$orderPayment->paymentAmount = 22.80;
						array_push($orderPayments, $orderPayment);
						
						//create 2nd sales order payment record
						$orderPayment = new ESDRecordOrderPayment();
						$orderPayment->paymentMethod = ESDocumentConstants::PAYMENT_METHOD_PROPRIETARY;
						$orderPayment->paymentProprietaryCode = "PAYPAL";
						$orderPayment->paymentReceipt = "2323432341231";
						$orderPayment->keyPaymentTypeID = "PP";
						$orderPayment->paymentAmount = 30.00;
						array_push($orderPayments, $orderPayment);
						
						//add payments to the order and set overall payment details
						$salesOrderRecord->payments = $orderPayments;
						$salesOrderRecord->paymentAmount = 41.00;
						$salesOrderRecord->paymentStatus = ESDocumentConstants::PAYMENT_STATUS_PAID;
						
						//set order totals, pricing data only needs to be set if the order isn't being repriced
						$salesOrderRecord->totalPriceIncTax = 52.80;
						$salesOrderRecord->totalPriceExTax = 48.00;
						$salesOrderRecord->totalTax = 4.80;
						$salesOrderRecord->totalSurchargeExTax = 8.00;
						$salesOrderRecord->totalSurchargeIncTax = 8.80;
						$salesOrderRecord->totalSurchargeTax = 8.00;
					
						//create sales order records list and add sales order to it
						$salesOrderRecords = array();
						array_push($salesOrderRecords, $salesOrderRecord);
						
						//after 60 seconds give up on waiting for a response from the API when importing the order
						$timeoutMilliseconds = 60000;
						
						//create sales order Ecommerce Standards document and add sales order records to the document
						$orderSaleESD = new ESDocumentOrderSale(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $salesOrderRecords, array());

						//send sales order document to the API for importing agqainst the organisation
						$endpointResponseESD = APIv1EndpointOrgImportSalesOrder::call($apiOrgSession, $timeoutMilliseconds, $orderSaleESD, ($rePriceOrder == ESDocumentConstants::ESD_VALUE_YES), $supplierOrgID, $customerAccountCode);
						$esDocumentOrderSale = $endpointResponseESD->esDocument;
						
						//check the result of importing the sales order
						if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
							$result = "SUCCESS";
							$resultMessage = "Organisation sales order(s) have successfully been imported against the organisation.";
							
							//iterate through each of the returned sales orders and output the details of the sales order(s)
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
							$resultMessage = "Organisation sales order(s) failed to be processed. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							
							//if one or more products in the sales order could not match a product for the organisation then find out the order lines that caused the problem
							if($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_PRODUCT_NOT_MATCHED && $esDocumentOrderSale != null)
							{
								//get a list of order lines that could not be mapped
								$unmatchedLines = APIv1EndpointOrgImportSalesOrder::getUnmatchedOrderLines($esDocumentOrderSale);
								
								//iterate through each unmatched order line
								foreach($unmatchedLines as $orderIndex => $lineIndex)
								{								
									//check that the order can be found that contains the problematic line
									if($orderIndex < count($orderSaleESD->dataRecords) && $lineIndex < count($orderSaleESD->dataRecords[$orderIndex]->lines)){
										$resultMessage = $resultMessage . "<br/>For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " a matching product for line number: " . ($lineIndex+1) . " could not be found.";
									}
								}
							}
							//if one or more products in the sales order could not be priced for the organisation then find the order line that caused the problem
							elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_LINE_PRICING_MISSING && $esDocumentOrderSale != null)
							{
								//get a list of order lines that could not be priced
								$unpricedLines = APIv1EndpointOrgImportSalesOrder::getUnpricedOrderLines($esDocumentOrderSale);

								//iterate through each unpriced order line
								foreach($unpricedLines as $orderIndex => $lineIndex)
								{
									//check that the order can be found that contains the problematic line
									if($orderIndex < count($orderSaleESD->dataRecords) && $lineIndex < count($orderSaleESD->dataRecords[$orderIndex]->lines)){
										$resultMessage = $resultMessage . "For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " no pricing found for line number: " . ($lineIndex+1);
									}
								}
							}
							//if one or more surcharges in the sales order could not match a surcharge for the organisation then find out the order surcharge that caused the problem
							elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_SURCHARGE_NOT_FOUND && $esDocumentOrderSale != null)
							{
								//get a list of order surcharges that could not be matched
								$unmatchedSurcharges = APIv1EndpointOrgImportSalesOrder::getUnmatchedOrderSurcharges($esDocumentOrderSale);
								
								//iterate through each unmatched order surcharge
								foreach($unmatchedSurcharges as $orderIndex => $surchargeIndex)
								{								
									//check that the order can be found that contains the problematic surcharge
									if($orderIndex < count($orderSaleESD->dataRecords) && $surchargeIndex < count($orderSaleESD->dataRecords[$orderIndex]->surcharges)){
										$resultMessage = $resultMessage . "<br/>For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " a matching surcharge for surcharge number: " . ($surchargeIndex+1) . " could not be found.";
									}
								}
							}
							//if one or more surcharges in the sales order could not be priced then find the order surcharge that caused the problem
							elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_SURCHARGE_PRICING_MISSING && $esDocumentOrderSale != null)
							{
								//get a list of order lines that could not be priced
								$unpricedSurcharges = APIv1EndpointOrgImportSalesOrder::getUnpricedOrderSurcharges($esDocumentOrderSale);

								//iterate through each unpriced order surcharge
								foreach($unpricedSurcharges as $orderIndex => $surchargeIndex)
								{
									//check that the order can be found that contains the problematic surcharge
									if($orderIndex < count($orderSaleESD->dataRecords) && $surchargeIndex < count($orderSaleESD->dataRecords[$orderIndex]->surcharges)){
										$resultMessage = $resultMessage . "For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " no pricing found for surcharge number: " . ($surchargeIndex+1);
									}
								}
							}
							//if one or more surcharges in the sales order could not match a surcharge for the organisation then find out the order surcharge that caused the problem
							elseif($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_PAYMENT_NOT_MATCHED && $esDocumentOrderSale != null)
							{
								//get a list of order payments that could not be matched
								$unmatchedPayments = APIv1EndpointOrgImportSalesOrder::getUnmatchedOrderPayments($esDocumentOrderSale);
								
								//iterate through each unmatched order payment
								foreach($unmatchedPayments as $orderIndex => $paymentIndex)
								{								
									//check that the order can be found that contains the problematic payment
									if($orderIndex < count($orderSaleESD->dataRecords) && $paymentIndex < count($orderSaleESD->dataRecords[$orderIndex]->payments)){
										$resultMessage = $resultMessage . "<br/>For sales order: " . $orderSaleESD->dataRecords[$orderIndex]->salesOrderCode . " a matching payment type for payment number: " . ($paymentIndex+1) . " could not be found.";
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