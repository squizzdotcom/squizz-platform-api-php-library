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
	use EcommerceStandardsDocuments\ESDocumentOrderPurchase;
	use EcommerceStandardsDocuments\ESDocumentOrderSale;
	use \JsonMapper;

	/**
	* Class handles calling the SQUIZZ.com API endpoint to push and import different kinds of organisational data into the platform such as products, customer accounts, and many other data types. See the full list at https://www.squizz.com/docs/squizz/Platform-API.html#section843
	* The data being pushed must be wrapped up in a Ecommerce Standards Document (ESD) that contains records storing data of a particular type
	*/
	class APIv1EndpointOrgImportESDocument
	{
		const IMPORT_TYPE_ID_TAXCODES = 1;
		const IMPORT_TYPE_ID_PRICE_LEVELS = 2;
		const IMPORT_TYPE_ID_PRODUCTS = 3;
		const IMPORT_TYPE_ID_PRODUCT_PRICE_LEVEL_UNIT_PRICING = 4;
		const IMPORT_TYPE_ID_PRODUCT_PRICE_LEVEL_QUANTITY_PRICING = 6;
		const IMPORT_TYPE_ID_PRODUCT_CUSTOMER_ACCOUNT_PRICING = 7;
		const IMPORT_TYPE_ID_CATEGORY = 8;
		const IMPORT_TYPE_ID_ALTERNATE_CODES = 9;
		const IMPORT_TYPE_ID_PRODUCT_STOCK_QUANTITIES = 10;
		const IMPORT_TYPE_ID_ATTRIBUTE = 11;
		const IMPORT_TYPE_ID_SALES_REPRESENTATIVES = 16;
		const IMPORT_TYPE_ID_CUSTOMER_ACCOUNTS = 17;
		const IMPORT_TYPE_ID_SUPPLIER_ACCOUNTS = 18;
		const IMPORT_TYPE_ID_CUSTOMER_ACCOUNT_CONTRACTS = 19;
		const IMPORT_TYPE_ID_CUSTOMER_ACCOUNT_ADDRESSES = 20;
		const IMPORT_TYPE_ID_LOCATIONS = 23;
		const IMPORT_TYPE_ID_PURCHASERS = 25;
		const IMPORT_TYPE_ID_SURCHARGES = 26;
		const IMPORT_TYPE_ID_PAYMENT_TYPES = 27;
		const IMPORT_TYPE_ID_SELL_UNITS = 28;
		const IMPORT_TYPE_ID_MAKER = 44;
		const IMPORT_TYPE_ID_MAKER_MODEL = 45;
		const IMPORT_TYPE_ID_MAKER_MODEL_MAPPING = 46;
		
		/**
		* Calls the platform's API endpoint and pushes up and import organisation data in a Ecommerce Standards Document of a specified type
		* @param apiOrgSession					APIv1OrgSession		existing organisation API session
		* @param endpointTimeoutMilliseconds 	int					amount of milliseconds to wait after calling the the API before giving up, set a positive number
		* @param importTypeID					int					ID of the of the type of data to import
		* @param esDocument						ESDocument			Ecommerce Standards Document that contains records and data to to upload. Ensure the document matches the import type given
		* @return APIv1EndpointResponseESD response from calling the API endpoint
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $importTypeID, $esDocument)
		{
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponseESD();
			
			try{
				//set endpoint parameters
				$endpointParams = "import_type_id=".$importTypeID;
				
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					
					//deserialize json data into an ESDocument
					$jsonMapper = new JsonMapper();
					$jsonMapper->bEnforceMapType = false;
					$esDocument = $jsonMapper->map($jsonArray, new ESDocument());
					
					//add ESDocument to endpoint response and return the response
					$endpointResponse->esDocument = $esDocument;
					return $endpointResponse;
				};
				
				//make a HTTP request to the platform's API endpoint to push the ESDocument data up
				$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_POST, APIv1Constants::API_ORG_ENDPOINT_IMPORT_ESD . APIv1Constants::API_PATH_SLASH . $apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, "", $esDocument, $endpointTimeoutMilliseconds, $apiOrgSession->getLangBundle(), $endpointJSONReader, $endpointResponse);
				
				//check that the data was successfully pushed up
				if(strcasecmp($endpointResponse->result, APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS) != 0)
				{
					//check if the session still exists
					if(strcasecmp($endpointResponse->result_code, APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_SESSION_INVALID) == 0){
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