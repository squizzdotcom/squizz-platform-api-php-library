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
	use EcommerceStandardsDocuments\ESDocumentProduct;
	use EcommerceStandardsDocuments\ESDocumentPrice;
	use EcommerceStandardsDocuments\ESDocumentStockQuantity;
	use \JsonMapper;

	/**
	* Class handles calling the SQUIZZ.com API endpoint to get different kinds of organisational data from a connected organisation in the platform such as products, stock quantities, and other data types. See the full list at https://www.squizz.com/docs/squizz/Platform-API.html#section843
	* The data being retrieved is wrapped up in a Ecommerce Standards Document (ESD) that contains records storing data of a particular type
	*/
	class APIv1EndpointOrgRetrieveESDocument
	{
		const RETRIEVE_TYPE_ID_PRODUCTS = 3;
		const RETRIEVE_TYPE_ID_PRICING = 37;
		const RETRIEVE_TYPE_ID_PRODUCT_STOCK = 10;
		
		/**
		* Calls the platform's API endpoint and gets organisation data in a Ecommerce Standards Document of a specified type
		* @param apiOrgSession existing organisation API session
		* @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
		* @param retrieveTypeID ID of the type of data to retrieve
		* @param supplierOrgID unique ID of the supplier organisation in the SQUIZZ.com platform to obtain data from
		* @param customerAccountCode code of the supplier organisation's customer account. Customer account only needs to be set if the supplier organisation has assigned multiple accounts to the organisation logged into the API session (customer org) and account specific data is being obtained
		* @return APIv1EndpointResponseESD response from calling the API endpoint
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $retrieveTypeID, $supplierOrgID, $customerAccountCode)
		{
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponseESD();
			$deserializeESDDocument = null;
			$callEndpoint = true;
			
			try{
				//set endpoint parameters
				$endpointParams = "data_type_id=".$retrieveTypeID."&supplier_org_id=".urlencode(utf8_encode($supplierOrgID))."&customer_account_code=".urlencode(utf8_encode($customerAccountCode));
				
				
				//set the class to use to deserialise the ecommerce standards documents that has been returned from the platform's API
				switch($retrieveTypeID){
					case self::RETRIEVE_TYPE_ID_PRODUCTS:
						$deserializeESDDocument = new ESDocumentProduct();
						break;
					case self::RETRIEVE_TYPE_ID_PRICING:
						$deserializeESDDocument = new ESDocumentPrice();
						break;
					case self::RETRIEVE_TYPE_ID_PRODUCT_STOCK:
						$deserializeESDDocument = new ESDocumentStockQuantity();
						break;
					default:
						$callEndpoint = false;
						$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
						$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INCORRECT_DATA_TYPE;
						break;
				}
				
				//make a HTTP request to the platform's API endpoint to retrieve the specified organisation data contained in the Ecommerce Standards Document
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
				
					$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_GET, APIv1Constants::API_ORG_ENDPOINT_RETRIEVE_ESD . APIv1Constants::API_PATH_SLASH . $apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, "", null, $endpointTimeoutMilliseconds, $apiOrgSession->getLangBundle(), $endpointJSONReader, $endpointResponse);
					
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
			}
			catch(Exception $ex)
			{
				$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
				$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
				$endpointResponse->result_message = $apiOrgSession->getLangBundle()->getString($endpointResponse->result_code) . "\n" . ex.getMessage();
			}
			
			return $endpointResponse;
		}
	}
?>