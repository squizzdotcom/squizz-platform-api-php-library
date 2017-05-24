<?php

	/**
	* Copyright (C) 2017 Squizz PTY LTD
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
	use \JsonMapper;

	/**
	* Class handles calling the SQUIZZ.com API endpoint for raising organisation notifications within the platform. 
	* Organisation notifications are sent to selected people assigned to the organisation's notification categories
	*/
	class APIv1EndpointOrgCreateNotification
	{
		const NOTIFY_CATEGORY_ORG = "org";
		const NOTIFY_CATEGORY_ACCOUNT = "account";
		const NOTIFY_CATEGORY_ORDER_SALE = "order_sale";
		const NOTIFY_CATEGORY_ORDER_PURCHASE = "order_purchase";
		const NOTIFY_CATEGORY_FEED = "feed";
		const MAX_MESSAGE_PLACEHOLDERS = 5;
	
		/**
		* Calls the platform's API endpoint to create an organisation notification and notify selected people assigned to an organisation's notification category
		* To allow notifications to be sent to the platform the organisation must have sufficient trading tokens
		* @param apiOrgSession APIv1OrgSession existing organisation API session
		* @param endpointTimeoutMilliseconds int amount of milliseconds to wait after calling the the API before giving up, set a positive number
		* @param notifyCategory string string notification category that the notification appears within for the organisation's people. Set to one of the NOTIFY_CATEGORY_ constants
		* @param message string message to display in the notification. Put placeholders in message {1}, {2}, {3}, {4}, {5} to replace with links or labels
		* @param linkURLs string[] ordered array of URLs to replace in each of the place holders of the message. Set empty strings to ignore placing values into place holders
		* @param linkLabels string[] ordered array of labels to replace in each of the place holders of the message. Set empty strings to ignore placing values into place holders
		* @return APIv1EndpointResponseESD response from calling the API endpoint
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $notifyCategory, $message, $linkURLs, $linkLabels)
		{
			$endpointParams = "";
			$requestHeaders = array(APIv1HTTPRequest::HTTP_HEADER_CONTENT_TYPE . ": " . APIv1HTTPRequest::HTTP_HEADER_CONTENT_TYPE_FORM_URL_ENCODED);
			$endpointResponse = new APIv1EndpointResponseESD();
			
			try{
				$linkURLParams = "";
				$linkLabelParams = "";
				
				//generate parameters for link URLs to be placed in the message
				for($i=0; $i < count($linkURLs) && $i < self::MAX_MESSAGE_PLACEHOLDERS; $i++){
					if(strlen(trim($linkURLs[$i])) > 0){
						$linkURLParams = $linkURLParams."&link".($i+1)."_url=".urlencode(utf8_encode($linkURLs[$i]));
					}
				}
				
				//generate parameters for link labels to be placed in the message
				for($i=0; $i < count($linkLabels) && $i < self::MAX_MESSAGE_PLACEHOLDERS; $i++){
					if(strlen(trim($linkLabels[$i])) > 0){
						$linkLabelParams = $linkLabelParams."&link".($i+1)."_label=".urlencode(utf8_encode($linkLabels[$i]));
					}
				}
				
				//set notification parameters
				$requestPostBody = "notify_category=" . urlencode(utf8_encode($notifyCategory))."&message=" . urlencode(utf8_encode($message)) . $linkURLParams . $linkLabelParams;
				
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					
					//deserialize array into Ecommerce standards documents
					$jsonMapper = new JsonMapper();
					$jsonMapper->bEnforceMapType = false;
					$esDocument = $jsonMapper->map($jsonArray, new ESDocument());
					
					//add ESDocument to endpoint response and return the response
					$endpointResponse->esDocument = $esDocument;
					return $endpointResponse;
				};
				
				//make a HTTP request to the platform's API endpoint to create the organisation notifications
				$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_POST, APIv1Constants::API_ORG_ENDPOINT_CREATE_NOTIFCATION.APIv1Constants::API_PATH_SLASH.$apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, $requestPostBody, null, $endpointTimeoutMilliseconds, $apiOrgSession->getLangBundle(), $endpointJSONReader, $endpointResponse);
				
				//check that the notification were successfully sent
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
				$endpointResponse->result_message = $apiOrgSession->getLangBundle()->getString($endpointResponse->result_code) . "\n" . $ex->getMessage();
			}
			
			return $endpointResponse;
		}
	}
?>