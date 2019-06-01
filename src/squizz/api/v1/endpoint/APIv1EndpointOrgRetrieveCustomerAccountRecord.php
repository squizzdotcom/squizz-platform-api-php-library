<?php
	/**
	* Copyright (C) 2019 Squizz PTY LTD
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
	use EcommerceStandardsDocuments\ESDocumentCustomerAccountEnquiry;
	use \JsonMapper;

	/**
	* Class handles calling the SQUIZZ.com API endpoint to retrieve details of a single record (such as invoice, sales order, back order, payment, credit, transaction) associated to a supplier organisation's customer account. See the full list at https://www.squizz.com/docs/squizz/Platform-API.html#section1036
	* The data being retrieved is wrapped up in a Ecommerce Standards Document(ESD) that contains records storing data of a particular type
	*/
	class APIv1EndpointOrgRetrieveCustomerAccountRecord
	{
		/**
		* Calls the platform's API endpoint and retrieves for a connected organisation a customer account record retrieved live from organisation's connected business system</summary>
        * @param apiOrgSession existing organisation API session
        * @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
        * @param recordType type of record data to retrieve
        * @param supplierOrgID unique ID of the organisation in the SQUIZZ.com platform that has supplies the customer account
        * @param customerAccountCode code of the account organisation's customer account. Customer account only needs to be set if the supplier organisation has assigned multiple accounts to the organisation logged into the API session (customer org) and account specific data is being obtained
        * @param keyRecordID comma delimited list of records unique key record ID to match on. Each Key Record ID value needs to be URI encoded
        * @return APIv1EndpointResponseESD response from calling the API endpoint
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $recordType, $supplierOrgID, $customerAccountCode,$keyRecordID)
		{
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponseESD();
			$deserializeESDDocument = new ESDocumentCustomerAccountEnquiry();
			$callEndpoint = true;
			
			try{
				//set endpoint parameters
                $endpointParams = "record_type=".$recordType."&supplier_org_id=".urlencode(utf8_encode($supplierOrgID))."&customer_account_code=".urlencode(utf8_encode($customerAccountCode))."&key_record_id=".urlencode(utf8_encode($keyRecordID));
				
				//make a HTTP request to the platform's API endpoint to retrieve the customer account enquiry record details
				if($callEndpoint == true)
				{
					//set function used to read the response from the endpoint
					$endpointJSONReader = function($jsonArray, $endpointResponse) use($deserializeESDDocument){
						$endpointResponse->jsonDeserialize($jsonArray);
						
						//deserialize array into Ecommerce Standards Document based on the given data type
						$jsonMapper = new JsonMapper();
						$jsonMapper->bEnforceMapType = false;
						$jsonMapper->bStrictNullTypes = false;
						$esDocument = $jsonMapper->map($jsonArray, $deserializeESDDocument);
						
						//add ESDocument to endpoint response and return the response
						$endpointResponse->esDocument = $esDocument;
						return $endpointResponse;
					};
				
					$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_GET, APIv1Constants::API_ORG_ENDPOINT_RETRIEVE_CUSTOMER_ACCOUNT_RECORD_ESD . APIv1Constants::API_PATH_SLASH . $apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, "", null, $endpointTimeoutMilliseconds, $apiOrgSession->getLangBundle(), $endpointJSONReader, $endpointResponse);
					
					//check that the data was successfully retrieved
					if(strcasecmp($endpointResponse->result, APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS) != 0)
					{
						//check if the session still exists
						if(strcasecmp($endpointResponse->result, APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_SESSION_INVALID) != 0){
							//mark that the session has expired
							$apiOrgSession->markSessionExpired();
						}
					}
				}
			}
			catch(Exception $ex)
			{
				$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
				$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
				$endpointResponse->result_message = $apiOrgSession->getLangBundle()->getString($endpointResponse->result_code)."\n".$ex.getMessage();
			}
			
			return $endpointResponse;
		}
	}
?>
