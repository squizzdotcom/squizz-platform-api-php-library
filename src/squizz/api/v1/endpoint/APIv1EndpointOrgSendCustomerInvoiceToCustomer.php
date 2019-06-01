<?php
	/**
	* Copyright (C) 2019 Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	namespace squizz\api\v1\endpoint;
	require_once __DIR__ . '/../../../../../3rd-party/jsonmapper/JsonMapper.php';
	
	use squizz\api\v1\APIv1Constants;
	use squizz\api\v1\APIv1HTTPRequest;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\endpoint\APIv1EndpointResponseESD;
	use EcommerceStandardsDocuments\ESDocument;
	use EcommerceStandardsDocuments\ESDocumentConstants;
	use EcommerceStandardsDocuments\ESDocumentSupplierInvoice;
	use EcommerceStandardsDocuments\ESDocumentCustomerInvoice;
	use \JsonMapper;
	use \JsonMapper\Exception;
	
	/**
	* Class handles calling the SQUIZZ.com API endpoint to send one more of an organisation's customer invoices to the platform, where they are then converted into supplier invoices and sent to a customer organisation for importing and processing.
	* This endpoint allows the invoicing of goods and services supplied by a supplying organisation logged into the API session. to be sent their chosen customer organisation
	*/
	class APIv1EndpointOrgSendCustomerInvoiceToCustomer
	{
		/**
		* Calls the platform's API endpoint and pushes up and import organisation data in a Ecommerce Standards Document of a specified type
		* @param apiOrgSession existing organisation API session
		* @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
		* @param customerOrgID unique ID of the customer organisation in the SQUIZZ.com platform
		* @param supplierAccountCode code of the customer organisation's supplier account. Supplier account only needs to be set if the customer organisation has assigned multiple accounts to the supplier organisation logged into the API session (supplier org)
		* @param esDocumentCustomerInvoice Customer Invoice Ecommerce Standards Document that contains one or more customer invoice records
		* @return response from calling the API endpoint containing a Ecommerce Standards Document enclosed within it
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $customerOrgID, $supplierAccountCode, $esDocumentCustomerInvoice)
		{
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponseESD();
			
			try{
				//set request parameters
				$endpointParams = "customer_org_id=". urlencode(utf8_encode($customerOrgID)) . "&supplier_account_code=".urlencode(utf8_encode($supplierAccountCode));
				
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					
					//deserialize array into supplier invoice ESD
					$jsonMapper = new JsonMapper();
					$jsonMapper->bEnforceMapType = false;
					$esDocumentSupplierInvoice = $jsonMapper->map($jsonArray, new ESDocumentSupplierInvoice());
					
					//add ESDocument to endpoint response and return the response
					$endpointResponse->esDocument = $esDocumentSupplierInvoice;
					return $endpointResponse;
				};
				
				//make a HTTP request to the platform's API endpoint to send the ESD containing the customer invoices
				$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_POST, APIv1Constants::API_ORG_ENDPOINT_SEND_CUSTOMER_INVOICE_TO_CUSTOMER . APIv1Constants::API_PATH_SLASH . $apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, "", $esDocumentCustomerInvoice, $endpointTimeoutMilliseconds, $apiOrgSession->getLangBundle(), $endpointJSONReader, $endpointResponse);
				
				//check that the data was successfully pushed up
				if(strcasecmp($endpointResponse->result, APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS) != 0)
				{
					//check if the session still exists
					if(strcasecmp($endpointResponse->result, APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_SESSION_INVALID) != 0){
						//mark that the session has expired
						$apiOrgSession->markSessionExpired();
					}
				}
			}
			catch(Exception $ex)
			{
				$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
				$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
				$endpointResponse->result_message = $apiOrgSession->getLangBundle()->getString($endpointResponse->result_code) . "\n" . $ex.getMessage();
			}
			
			return $endpointResponse;
		}
		
		/**
		* gets a list of invoice indexes that contain invoice lines that could not be mapped to a customer organisation's products
		* @param esDocument Ecommerce standards document containing configuration that specifies unmapped invoice lines
		* @return array containing pairs. Each pair has the index of the invoice, and the index of the invoice line that could not be mapped
		*/
		public static function getUnmappedInvoiceLines($esDocument)
		{
			$upmappedInvoiceLines = array();

			//check that the ecommerce standards document's configs contains a key specifying the unmapped invoice lines
			
			if(array_key_exists(APIv1EndpointResponseESD::ESD_CONFIG_INVOICES_WITH_UNMAPPED_LINES, $esDocument->configs))
			{
				//get comma separated list of invoice record indicies and line indicies that indicate the unmapped invoice lines
				$unmappedInvoiceLineCSV = $esDocument->configs[APIv1EndpointResponseESD::ESD_CONFIG_INVOICES_WITH_UNMAPPED_LINES];

				//get the index of the invoice record and line that contained the unmapped product
				if(strlen(trim($unmappedInvoiceLineCSV)) > 0)
				{
					$unmappedInvoiceLineIndices = explode(',', trim($unmappedInvoiceLineCSV));

					//iterate through each invoice-line index
					foreach($unmappedInvoiceLineIndices as $unmappedInvoiceLineIndex){
						//get invoice index and line index
						$invoiceLineIndex = explode(':',$unmappedInvoiceLineIndex);
						if(count($invoiceLineIndex) == 2){
						
							try{
								$invoiceIndex = $invoiceLineIndex[0];
								$lineIndex = (int)$invoiceLineIndex[1];
								$upmappedInvoiceLines[$invoiceIndex] = $lineIndex;
							}catch(Exception $ex){
							}
						}
					}
				}
			}

			return $upmappedInvoiceLines;
		}
	}
?>