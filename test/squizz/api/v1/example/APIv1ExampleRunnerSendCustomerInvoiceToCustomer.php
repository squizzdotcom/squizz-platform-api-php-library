<!DOCTYPE html>
<html>
	<body style="font-family: sans-serif; background-color: #00a0e3; color: #FFF">
		<div style="text-align: center">
			<img src="http://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png"/>
			<hr style="max-width: 607px"/>
			<div>SQUIZZ Pty Ltd</div>
			<div>Testing SQUIZZ.com API PHP Library: version 1</div>
			<hr style="max-width: 607px"/>
			<h1>Send Customer Invoice To Customer API Example</h1>
			<p>Tests making a request to the SQUIZZ.com API to create a session for an organisation then makes a call to the API to push a customer invoice to a customer organisation to import into the customer's system</p>
			<div style="max-width: 607px; background-color: #2b2b2b; color: #cacaca; text-align: center; margin: auto; padding-top: 15px;">
				<?php
					/**
					* Copyright (C) 2019 Squizz PTY LTD
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
					use squizz\api\v1\endpoint\APIv1EndpointOrgSendCustomerInvoiceToCustomer;
					use squizz\api\v1\APIv1OrgSession;
					use squizz\api\v1\APIv1Constants;
					use EcommerceStandardsDocuments\ESDRecordCustomerInvoice;
					use EcommerceStandardsDocuments\ESDRecordCustomerInvoiceLine;
					use EcommerceStandardsDocuments\ESDocumentConstants;
					use EcommerceStandardsDocuments\ESDocumentCustomerInvoice;
					use EcommerceStandardsDocuments\ESDocumentSupplierInvoice;
					use EcommerceStandardsDocuments\ESDRecordInvoiceLineTax;
					
					//obtain or load in an organisation's API credentials, in this example from command line arguments
					$orgID = $_GET["orgID"];
					$orgAPIKey = $_GET["orgAPIKey"];
					$orgAPIPass = $_GET["orgAPIPass"];
					$customerOrgID = $_GET["customerOrgID"];
					$supplierAccountCode = $_GET["supplierAccountCode"];
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
					
					//send the customer invoice if the API was successfully created
					if($apiOrgSession->sessionExists())
					{
						//create customer invoice record to import
						$customerInvoiceRecord = new ESDRecordCustomerInvoice();

						//set data within the customer invoice
						$customerInvoiceRecord->keyCustomerInvoiceID = "111";
						$customerInvoiceRecord->customerInvoiceCode = "CINV-22";
						$customerInvoiceRecord->customerInvoiceNumber = "22";
						$customerInvoiceRecord->salesOrderCode = "SO-332";
						$customerInvoiceRecord->purchaseOrderNumber = "PO-345";
						$customerInvoiceRecord->instructions = "Please pay within 30 days";
						$customerInvoiceRecord->keyCustomerAccountID = "2";
						$customerInvoiceRecord->customerAccountCode = "ACM-002";

						//set dates within the invoice, having the payment date set 30 days from now, and dispatch date 2 days ago
						$customerInvoiceRecord->paymentDueDate = time() + (30 * 24 * 60 * 60 * 1000);
						$customerInvoiceRecord->createdDate = time();
						$customerInvoiceRecord->dispatchedDate = time() + (-2 * 24 * 60 * 60 * 1000);
						
						//set delivery address that invoice goods were delivered to
						$customerInvoiceRecord->deliveryAddress1 = "32";
						$customerInvoiceRecord->deliveryAddress2 = "Main Street";
						$customerInvoiceRecord->deliveryAddress3 = "Melbourne";
						$customerInvoiceRecord->deliveryRegionName = "Victoria";
						$customerInvoiceRecord->deliveryCountryName = "Australia";
						$customerInvoiceRecord->deliveryPostcode = "3000";
						$customerInvoiceRecord->deliveryOrgName = "Acme Industries";
						$customerInvoiceRecord->deliveryContact = "Jane Doe";

						//set billing address that the invoice is billed to for payment
						$customerInvoiceRecord->billingAddress1 = "43";
						$customerInvoiceRecord->billingAddress2 = " Smith Street";
						$customerInvoiceRecord->billingAddress3 = "Melbourne";
						$customerInvoiceRecord->billingRegionName = "Victoria";
						$customerInvoiceRecord->billingCountryName = "Australia";
						$customerInvoiceRecord->billingPostcode = "3000";
						$customerInvoiceRecord->billingOrgName = "Supplier Industries International";
						$customerInvoiceRecord->billingContact = "Lee Lang";
						
						echo "<div>teste<div>";

						//create an array of customer invoice lines
						$invoiceLines = array();

						//create invoice line record 1
						$invoicedProduct = new ESDRecordCustomerInvoiceLine();
						$invoicedProduct->lineType = ESDocumentConstants::INVOICE_LINE_TYPE_PRODUCT;
						$invoicedProduct->productCode = "ACME-SUPPLIER-TTGREEN";
						$invoicedProduct->productName = "Green tea towel - 30 x 6 centimetres";
						$invoicedProduct->keySellUnitID = "2";
						$invoicedProduct->unitName = "EACH";
						$invoicedProduct->quantityInvoiced = 4;
						$invoicedProduct->sellUnitBaseQuantity = 4;
						$invoicedProduct->priceExTax = 5.00;
						$invoicedProduct->priceIncTax = 5.50;
						$invoicedProduct->priceTax = 0.50;
						$invoicedProduct->priceTotalIncTax = 22.00;
						$invoicedProduct->priceTotalExTax = 20.00;
						$invoicedProduct->priceTotalTax = 2.00;
						//optionally specify customer's product code in purchaseOrderProductCode if it is different to the line's productCode field and the supplier org. knows the customer's codes
						$invoicedProduct->purchaseOrderProductCode = "TEA-TOWEL-GREEN";

						//add tax details to the product invoice line
						$productTax = new ESDRecordInvoiceLineTax();
						$productTax->priceTax = $invoicedProduct->priceTax;
						$productTax->priceTotalTax = $invoicedProduct->priceTotalTax;
						$productTax->quantity = $invoicedProduct->quantityInvoiced;
						$productTax->taxRate = 10.00;
						$productTax->taxcode = "GST";
						$productTax->taxcodeLabel = "Goods And Services Tax";
						$invoicedProduct->taxes = array();
						array_push($invoicedProduct->taxes, $productTax);
						
						//add 1st invoice line to lines list
						array_push($invoiceLines, $invoicedProduct);

						//add a 2nd invoice line record that is a text line
						$invoicedProduct = new ESDRecordCustomerInvoiceLine();
						$invoicedProduct->lineType = ESDocumentConstants::INVOICE_LINE_TYPE_TEXT;
						$invoicedProduct->textDescription = "Please bundle tea towels into a box";
						array_push($invoiceLines, $invoicedProduct);

						//add a 3rd invoice line product record to the invoice
						$invoicedProduct = new ESDRecordCustomerInvoiceLine();
						$invoicedProduct->lineType = ESDocumentConstants::INVOICE_LINE_TYPE_PRODUCT;
						$invoicedProduct->productCode = "ACME-TTBLUE";
						$invoicedProduct->quantityInvoiced = 10;
						$invoicedProduct->priceExTax = 10.00;
						$invoicedProduct->priceIncTax = 1.10;
						$invoicedProduct->priceTax = 1.00;
						$invoicedProduct->priceTotalIncTax = 110.00;
						$invoicedProduct->priceTotalExTax = 100.00;
						$invoicedProduct->priceTotalTax = 10.00;
						array_push($invoiceLines, $invoicedProduct);

						//add lines to the invoice
						$customerInvoiceRecord->lines = $invoiceLines;

						//set invoice totals
						$customerInvoiceRecord->totalPriceIncTax = 132.00;
						$customerInvoiceRecord->totalPriceExTax = 120.00;
						$customerInvoiceRecord->totalTax = 12.00;
						$customerInvoiceRecord->totalLines = count($invoiceLines);
						$customerInvoiceRecord->totalProducts = 2;

						//create customer invoices records list and add customer invoice to it
						$customerInvoiceRecords = array();
						array_push($customerInvoiceRecords, $customerInvoiceRecord);
						
						//after 60 seconds give up on waiting for a response from the API when sending the invoice
						$timeoutMilliseconds = 60000;
						
						//create customer invocie Ecommerce Standards document and add customer invoice records to the document
						$customerInvoiceESD = new ESDocumentCustomerInvoice(ESDocumentConstants::RESULT_SUCCESS, "successfully obtained data", $customerInvoiceRecords, array());

						//send customer invoice document to the API for sending onto the customer organisation
						$endpointResponseESD = APIv1EndpointOrgSendCustomerInvoiceToCustomer::call($apiOrgSession, $timeoutMilliseconds, $customerOrgID, $supplierAccountCode, $customerInvoiceESD);
						
						$esDocumentSupplierInvoice = $endpointResponseESD->esDocument;
						
						//check the result of sending the invoices
						if($endpointResponseESD->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
							$result = "SUCCESS";
							$resultMessage = "Organisation customer invocies have successfully been sent to customer organisation.";
							
							//iterate through each of the returned supplier invoices and output the details of the supplier invoices
							if($esDocumentSupplierInvoice->dataRecords != null){
								foreach($esDocumentSupplierInvoice->dataRecords as &$supplierInvoiceRecord)
								{								
									$resultMessage = $resultMessage . "<br/><br/>Supplier Invoice Returned, Invoice Details: <br/>";
									$resultMessage = $resultMessage . "Supplier Invoice Code: " . $supplierInvoiceRecord->supplierInvoiceCode . "<br/>";
									$resultMessage = $resultMessage . "Supplier Invoice Total Cost: " . $supplierInvoiceRecord->totalPriceIncTax . " (" . $supplierInvoiceRecord->currencyISOCode . ")" . "<br/>";
									$resultMessage = $resultMessage . "Supplier Invoice Total Taxes: " . $supplierInvoiceRecord->totalTax . " (" . $supplierInvoiceRecord->currencyISOCode . ")" . "<br/>";
									$resultMessage = $resultMessage . "Supplier Invoice Supplier Account: " . $supplierInvoiceRecord->supplierAccountCode . "<br/>";
									$resultMessage = $resultMessage . "Supplier Invoice Total Lines: " . $supplierInvoiceRecord->totalLines;
								}
							}
						}else{
							$result = "FAIL";
							$resultMessage = "Organisation customer invoices failed to be processed. Reason: " . $endpointResponseESD->result_message . " Error Code: " . $endpointResponseESD->result_code . "<br/>";
							
							//if one or more lines in the customer invoice could not match a product for the customer organisation then find out the invoice lines caused the problem
							if($endpointResponseESD->result_code == APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INVOICE_LINE_NOT_MAPPED && $esDocumentSupplierInvoice != null)
							{
								//get a list of invoice lines that could not be mapped
								$unmappedLines = APIv1EndpointOrgSendCustomerInvoiceToCustomer::getUnmappedInvoiceLines($esDocumentSupplierInvoice);
								
								//iterate through each unmapped invoice line
								foreach($unmappedLines as $invoiceIndex => $lineIndex)
								{								
									//check that the invoice can be found that contains the problematic line
									if($invoiceIndex < count($customerInvoiceESD->dataRecords) && $lineIndex < count($customerInvoiceESD->dataRecords[$invoiceIndex]->lines)){
										$resultMessage = $resultMessage . "<br/>For customer invoice: " . $customerInvoiceESD->dataRecords[$invoiceIndex]->customerInvoiceCode . " a matching customer product for line number: " . ($lineIndex+1) . " could not be found.";
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
