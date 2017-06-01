<?php
	/**
	* Copyright (C) 2017 Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	namespace squizz\api\v1\endpoint;
	require_once __DIR__ . '/../../../../../3rd-party/jsonmapper/JsonMapper.php';
	require_once __DIR__ . '/../../../../../3rd-party/jsonmapper/JsonMapper/Exception.php';
	
	use squizz\api\v1\APIv1Constants;
	use squizz\api\v1\APIv1HTTPRequest;
	use squizz\api\v1\APIv1OrgSession;
	use squizz\api\v1\endpoint\APIv1EndpointResponseESD;
	use EcommerceStandardsDocuments\ESDocument;
	use EcommerceStandardsDocuments\ESDocumentConstants;
	use \JsonMapper;

	/**
	* Class handles calling the SQUIZZ.com API endpoint for verifying a security certificate created for an organisation within the platform. 
	* Security certificates are used to secure organisational data transferred across the Internet and computer networks
	*/
	class APIv1EndpointOrgValidateSecurityCertificate 
	{
		/**
		* Calls the platform's API endpoint to validate the organisation's security certificate
		* The public Internet connection used to call the endpoint will be used to validate against the domain or IP address set for the security certificate
		* @param apiOrgSession APIv1OrgSession existing organisation API session
		* @param endpointTimeoutMilliseconds int amount of milliseconds to wait after calling the the API before giving up, set a positive number
		* @param orgSecurityCertificateID string ID of the orgnisation's security certificate in the platform
		* @return APIv1EndpointResponseESD response from calling the API endpoint
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $orgSecurityCertificateID)
		{
			$endpointParams = "";
			$requestHeaders = array(APIv1HTTPRequest::HTTP_HEADER_CONTENT_TYPE . ": " . APIv1HTTPRequest::HTTP_HEADER_CONTENT_TYPE_FORM_URL_ENCODED);
			$endpointResponse = new APIv1EndpointResponseESD();
			
			try{
				//set notification parameters
				$requestPostBody = "org_security_certificate_id=". urlencode(utf8_encode($orgSecurityCertificateID));
				
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					return $endpointResponse;
				};
				
				//make a HTTP request to the platform's API endpoint to validate the security certificate
				$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_POST, APIv1Constants::API_ORG_ENDPOINT_VALIDATE_CERT.APIv1Constants::API_PATH_SLASH.$apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, $requestPostBody, null, $endpointTimeoutMilliseconds, $apiOrgSession->getSessionID(), $endpointJSONReader, $endpointResponse);
				
				//check that the certificate was successfully validated
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