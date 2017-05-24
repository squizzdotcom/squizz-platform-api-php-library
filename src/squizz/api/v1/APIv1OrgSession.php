<?php
	/**
	* Copyright (C) 2017 Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	namespace squizz\api\v1;
	use EcommerceStandardsDocuments\ESDocumentConstants;
	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\lang\APIv1LangBundle;
	use squizz\api\v1\APIv1HTTPRequest;
	use squizz\api\v1\APIv1Constants;

	/**
	* Represents a session created within the API of the SQUIZZ.com platform for an organisation
	*/
	class APIv1OrgSession 
	{
		/**
		* ID of the session within the platform's API
		*/
		public $sessionID = "";
		
		/**
		* version of the API that responses are returned from
		*/
		public $apiVersion = "";
		
		/**
		* ID of the organisation logged into the API session
		*/
		public $orgID = "";
		
		/**
		* API key of the organisation logged into the API session
		*/
		public $apiOrgKey = "";
		
		/**
		* API password of the organisation logged into the API session
		*/
		public $apiOrgPassword = "";
		
		/**
		* amount of milliseconds by default to timeout requests to the API if no response is returned
		*/
		public $defaultRequestTimeoutMilliseconds = 10000;
		
		/**
		* if true then a session has been created in the platform's API
		*/
		public $sessionExists = false;
		
		/**
		* resource bundle to control the language that messages in the API are displayed in
		*/
		public $langBundle = null;
		
		/**
		* Makes a HTTP request to the platform's API to create a new session for an organisation
		* @param orgID Unique ID set for the organisation within the platform
		* @param apiOrgKey Key set for the organisation to allow it login in the API
		* @param apiOrgPassword Password set for the organisation to allow it to login to the API
		* @param defaultRequestTimeoutMilliseconds number of milliseconds to wait for a response from calls to the platform's API before giving up
		* @param languageLocale the locale that specifies the language that the API messages are displayed in
		*/
		public function __construct($orgID, $apiOrgKey, $apiOrgPassword, $defaultRequestTimeoutMilliseconds, $languageLocale)
		{
			$this->orgID = $orgID;
			$this->apiOrgKey = $apiOrgKey;
			$this->apiOrgPassword = $apiOrgPassword;
			$this->defaultRequestTimeoutMilliseconds = $defaultRequestTimeoutMilliseconds;
			$this->sessionExists = false;
			
			//set the language used for the session
			$this->langBundle = APIv1LangBundle::getBundle($languageLocale);
		}
		
		/**
		* gets the ID session generated by the platform's API
		* @return API session ID
		*/
		public function getSessionID()
		{
			return $this->sessionID;
		}
		
		/**
		* gets the version number of the API that the session was created with and using
		* @return API version number, returns empty string if no session has been created in the API yet
		*/
		public function getAPIVersion()
		{
			return $this->apiVersion;
		}
		
		/**
		* gets the language bundle that controls the language that messages of the API are displayed in
		* @return Resource bundle
		*/
		public function getLangBundle()
		{
			return $this->langBundle;
		}
		
		/**
		* indicates if the session with the platform's API has been created
		* @return true if the session has been created
		*/
		public function sessionExists()
		{
			return $this->sessionExists;
		}
		
		/**
		* clears the session's ID, marks that the session has expired and no longer exists in the platform's API
		* The method does not destroy the session within the the platform's API, to do so call destroyOrgSession()
		*/
		public function markSessionExpired()
		{
			$this->sessionExists = false;
			$this->sessionID = "";
		}
		
		/**
		* calls the platform's API to create a new organisation session
		* @return response from trying to create the session
		*/
		public function createOrgSession()
		{
			$endpointResponse = new APIv1EndpointResponse();
			$endpointParams = "";
			$requestHeaders = array(APIv1HTTPRequest::HTTP_HEADER_CONTENT_TYPE . ": " . APIv1HTTPRequest::HTTP_HEADER_CONTENT_TYPE_FORM_URL_ENCODED);
			
			try{
				//set endpoint parameters
				$requestPostBody = "org_id=". urlencode(utf8_encode($this->orgID))."&api_org_key=". urlencode(utf8_encode($this->apiOrgKey))."&api_org_pw=". urlencode(utf8_encode($this->apiOrgPassword))."&create_session=" . ESDocumentConstants::ESD_VALUE_YES;
				
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					return $endpointResponse;
				};
				
				//make a HTTP request to the platform's API endpoint to create a new session
				$endpointResponse = APIv1HTTPRequest::sendHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_POST, APIv1Constants::API_ORG_ENDPOINT_CREATE_SESSION, $endpointParams, $requestHeaders, $requestPostBody, $this->defaultRequestTimeoutMilliseconds, $this->langBundle, $endpointJSONReader, $endpointResponse);
				
				//update session credentials if the session was successfully created
				if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
				{
					$this->apiVersion = $endpointResponse->api_version;
					$this->sessionExists = true;
					$this->sessionID = $endpointResponse->session_id;
					$endpointResponse->session_valid = ESDocumentConstants::ESD_VALUE_YES;
				}
			}
			catch(Exception $ex)
			{
				$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
				$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
				$endpointResponse->result_message = $this->langBundle->getString($endpointResponse->result_code) . "\n" . $ex.getMessage();
			}
			
			return $endpointResponse;
		}
		
		/**
		* calls the platform's API to destroy an existing organisation session
		* @return response from trying to destroy the API session
		*/
		public function destroyOrgSession()
		{
			$endpointParams = "";
			$requestPostBody = "";
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponse();
			
			try{
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					return $endpointResponse;
				};
			
				//make a HTTP request to the platform's API endpoint to create a new session
				$endpointResponse = APIv1HTTPRequest::sendHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_GET, APIv1Constants::API_ORG_ENDPOINT_DESTROY_SESSION.APIv1Constants::API_PATH_SLASH . $this->sessionID, $endpointParams, $requestHeaders, $requestPostBody, $this->defaultRequestTimeoutMilliseconds, $this->langBundle, $endpointJSONReader, $endpointResponse);
				
				//update session credentials if the session was successfully destroyed
				if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
				{
					$this->sessionExists = false;
					$this->sessionID = "";
					$endpointResponse->session_valid = ESDocumentConstants::ESD_VALUE_NO;
				}
			}
			catch(Exception $ex)
			{
				$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
				$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
				$endpointResponse->result_message = $this->$langBundle->getString($endpointResponse->result_code) . "\n" . $ex->getMessage();
			}
			
			return $endpointResponse;
		}
		
		/**
		* calls the platform's API to validate an existing organisation session exists and is valid
		* @return response from trying to validate the API session
		*/
		public function validateOrgSession()
		{   
			//call the server to validate that the session still exists
			$endpointParams = "";
			$requestPostBody = "";
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponse();
			
			try{
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					return $endpointResponse;
				};
				
				//make a HTTP request to the platform's API endpoint to create a new session
				$endpointResponse = APIv1HTTPRequest::sendHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_GET, APIv1Constants::API_ORG_ENDPOINT_VALIDATE_SESSION . APIv1Constants::API_PATH_SLASH . $this->sessionID, $endpointParams, $requestHeaders, $requestPostBody, $this->defaultRequestTimeoutMilliseconds, $this->langBundle, $endpointJSONReader, $endpointResponse);
				
				//update session credentials if the session was successfully destroyed
				if(strcasecmp($endpointResponse->result, APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS) == 0)
				{
					$this->apiVersion = $endpointResponse->api_version;
					$this->sessionExists = true;
					$endpointResponse->session_valid = ESDocumentConstants::ESD_VALUE_YES;
				}else{
					//clear the session variables
					if(strcasecmp($endpointResponse->result, APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_SESSION_INVALID) == 0){
						$this->sessionExists = false;
						$this->sessionID = "";
						$endpointResponse->session_valid = ESDocumentConstants::ESD_VALUE_NO;
					}
				}
			}
			catch(Exception $ex)
			{
				$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
				$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
				$endpointResponse->result_message = $langBundle->getString($endpointResponse->result_code) . "\n" . $ex->getMessage();
			}
			
			return $endpointResponse;
		}
		
		/**
		* * calls the platform's API to validate an existing organisation session exists and is valid, if not then attempts to login to the API and create a new session
		* @return response from trying to validate the API session or create the API session
		*/
		public function validateCreateOrgSession()
		{
			$createSession = false;
			$endpointResponse = new APIv1EndpointResponse();
			
			//check if the organisation session is valid
			if($this->sessionID != ""){
				$endpointResponse = $this->validateOrgSession();
				
				//check if the session was validated
				if(strcasecmp($endpointResponse->result, APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_SESSION_INVALID) == 0){
					$createSession = true;
				}
			}else{
				$createSession = true;
			}
			
			//attempt to create a new API organisation session
			if($createSession){
				$endpointResponse = $this->createOrgSession();
			}
			
			return $endpointResponse;
		}
	}
?>