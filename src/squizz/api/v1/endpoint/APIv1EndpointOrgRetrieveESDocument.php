<?php
	/**
	* Copyright (C) Squizz PTY LTD
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
	use EcommerceStandardsDocuments\ESDocumentCategory;
	use EcommerceStandardsDocuments\ESDocumentProductCombination;
	use EcommerceStandardsDocuments\ESDocumentAttribute;
	use EcommerceStandardsDocuments\ESDocumentMaker;
	use EcommerceStandardsDocuments\ESDocumentMakerModel;
	use EcommerceStandardsDocuments\ESDocumentMakerModelMapping;
	use EcommerceStandardsDocuments\ESDocumentImage;
	use \JsonMapper;

	/**
	* Class handles calling the SQUIZZ.com API endpoint to get different kinds of organisational data from a connected organisation in the platform such as products, stock quantities, and other data types. See the full list at https://www.squizz.com/docs/squizz/Platform-API.html#section843
	* The data being retrieved is wrapped up in a Ecommerce Standards Document (ESD) that contains records storing data of a particular type
	*/
	class APIv1EndpointOrgRetrieveESDocument
	{
		const RETRIEVE_TYPE_ID_PRODUCTS = 3;
		const RETRIEVE_TYPE_ID_CATEGORIES = 8;
		const RETRIEVE_TYPE_ID_PRODUCT_COMBINATIONS = 15;
		const RETRIEVE_TYPE_ID_PRICING = 37;
		const RETRIEVE_TYPE_ID_PRODUCT_STOCK = 10;
		const RETRIEVE_TYPE_ID_PRODUCT_IMAGE = 12;
		const RETRIEVE_TYPE_ID_ATTRIBUTES = 11;
		const RETRIEVE_TYPE_ID_MAKERS = 44;
		const RETRIEVE_TYPE_ID_MAKER_MODELS = 45;
		const RETRIEVE_TYPE_ID_MAKER_MODEL_MAPPINGS = 46;

		/**
		* @var int date time in milliseconds that indicates to retrieve all records and not filter records
		*/
		const RETRIEVE_ALL_RECORDS_DATE_TIME_MILLISECONDS = 0;
		
		/**
		* Calls the platform's API endpoint and gets organisation data in a Ecommerce Standards Document of a specified type
		* @param apiOrgSession existing organisation API session
		* @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
		* @param retrieveTypeID ID of the type of data to retrieve
		* @param supplierOrgID unique ID of the supplier organisation in the SQUIZZ.com platform to obtain data from
		* @param customerAccountCode code of the supplier organisation's customer account. Customer account only needs to be set if the supplier organisation has assigned multiple accounts to the organisation logged into the API session (customer org) and account specific data is being obtained
		* @param recordsMaxAmount maximum number of records to obtain from the platform
		* @param recordsStartIndex index containing the position of records to start obtaining from the server
		* @param recordsUpdatedAfterDateTimeMilliseconds optionally limit to only retrieving records that were updated after the given date time. Provide date time in milliseconds since the 01-01-1970 12am UTC epoch, else set to 0 to obtain all records
		* @return APIv1EndpointResponseESD response from calling the API endpoint
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $retrieveTypeID, $supplierOrgID, $customerAccountCode, $recordsMaxAmount, $recordsStartIndex, $recordsUpdatedAfterDateTimeMilliseconds = self::RETRIEVE_ALL_RECORDS_DATE_TIME_MILLISECONDS)
		{
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponseESD();
			$deserializeESDDocument = null;
			$callEndpoint = true;
			
			try{
				//set endpoint parameters
				$endpointParams = "data_type_id=".$retrieveTypeID."&supplier_org_id=".urlencode(utf8_encode($supplierOrgID))."&customer_account_code=".urlencode(utf8_encode($customerAccountCode))."&records_max_amount=".$recordsMaxAmount."&records_start_index=".$recordsStartIndex."&records_updated_after_date_time=".$recordsUpdatedAfterDateTimeMilliseconds;
				
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
					case self::RETRIEVE_TYPE_ID_PRODUCT_IMAGE:
						$deserializeESDDocument = new ESDocumentImage();
						break;
					case self::RETRIEVE_TYPE_ID_CATEGORIES:
						$deserializeESDDocument = new ESDocumentCategory();
						break;
					case self::RETRIEVE_TYPE_ID_PRODUCT_COMBINATIONS:
						$deserializeESDDocument = new ESDocumentProductCombination();
						break;
					case self::RETRIEVE_TYPE_ID_ATTRIBUTES:
						$deserializeESDDocument = new ESDocumentAttribute();
						break;
					case self::RETRIEVE_TYPE_ID_MAKERS:
						$deserializeESDDocument = new ESDocumentMaker();
						break;
					case self::RETRIEVE_TYPE_ID_MAKER_MODELS:
						$deserializeESDDocument = new ESDocumentMakerModel();
						break;
					case self::RETRIEVE_TYPE_ID_MAKER_MODEL_MAPPINGS:
						$deserializeESDDocument = new ESDocumentMakerModelMapping();
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
				$endpointResponse->result_message = $apiOrgSession->getLangBundle()->getString($endpointResponse->result_code) . "\n" . $ex.getMessage();
			}
			
			return $endpointResponse;
		}
	}
?>