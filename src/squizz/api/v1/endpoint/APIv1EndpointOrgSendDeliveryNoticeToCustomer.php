<?php
	/**
	* Copyright (C) Squizz PTY LTD
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
	use EcommerceStandardsDocuments\ESDocumentDeliveryNotice;
	use \JsonMapper;
	use \JsonMapper\Exception;
	
	/**
	* Class handles calling the SQUIZZ.com API endpoint to send one more of an organisation's delivery notices to the platform, where they are then sent to a customer person, or organisation (optionally for importing and processing)
	* This endpoint allows the notifications of ordered goods from a supplying organisation logged into the API session, to be sent their chosen customer on squizz. These notices can advise how the goods are tracking through dispatch and delivery processes.
	*/
	class APIv1EndpointOrgSendDeliveryNoticeToCustomer
	{
		/**
		* Calls the platform's API endpoint to push up a delivery notice and have it be sent to a connected customer organisation or person
		* @param apiOrgSession existing organisation API session
		* @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
		* @param customerOrgID unique ID of the customer organisation in the SQUIZZ.com platform
		* @param supplierAccountCode code of the customer organisation's supplier account. Supplier account only needs to be set if the customer organisation has assigned multiple accounts to the supplier organisation logged into the API session (supplier org)
		* @param useDeliveryNoticeExport if true then after the delivery notice is imported into Squizz it will be exported across to another system, using Customer Delivery Notice data export configured with the default data adaptor
		* @param esDocumentDeliveryNotice Delivery Notice Ecommerce Standards Document that contains one or more delivery notice records
		* @return response response from calling the API endpoint containing a Ecommerce Standards Document enclosed within it
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $customerOrgID, $supplierAccountCode, $useDeliveryNoticeExport, $esDocumentDeliveryNotice)
		{
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponseESD();
			
			try{
				//set request parameters
				$endpointParams = "customer_org_id=". urlencode(utf8_encode($customerOrgID)) . "&supplier_account_code=".urlencode(utf8_encode($supplierAccountCode)) . "&use_delivery_notice_export=" . ($useDeliveryNoticeExport? ESDocumentConstants::ESD_VALUE_YES: ESDocumentConstants::ESD_VALUE_NO);
				
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					
					//deserialize array into delivery notice ESD
					$jsonMapper = new JsonMapper();
					$jsonMapper->bEnforceMapType = false;
					$esDocumentDeliveryNotice = $jsonMapper->map($jsonArray, new ESDocumentDeliveryNotice());
					
					//add ESDocument to endpoint response and return the response
					$endpointResponse->esDocument = $esDocumentDeliveryNotice;
					return $endpointResponse;
				};
				
				//make a HTTP request to the platform's API endpoint to send the ESD containing the delivery notices
				$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_POST, APIv1Constants::API_ORG_ENDPOINT_SEND_DELIVERY_NOTICE_TO_CUSTOMER . APIv1Constants::API_PATH_SLASH . $apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, "", $esDocumentDeliveryNotice, $endpointTimeoutMilliseconds, $apiOrgSession->getLangBundle(), $endpointJSONReader, $endpointResponse);
				
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
	}
?>