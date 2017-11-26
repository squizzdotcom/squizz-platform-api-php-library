<?php
	/**
	* Copyright (C) 2018 Squizz PTY LTD
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
	* Class handles calling the SQUIZZ.com API endpoint to search for and retrieve records (such as invoices, sales orders, back orders, payments, credits, transactions) associated to a supplier organisation's customer account. See the full list at https://www.squizz.com/docs/squizz/Platform-API.html#section1035
	* The data being retrieved is wrapped up in a Ecommerce Standards Document (ESD) that contains records storing data of a particular type
	*/
	class APIv1EndpointOrgSearchCustomerAccountRecords
	{
		/**
		* Calls the platform's API endpoint and searches for a connected organisation's customer account records retrieved live from their connected business system
		* @param apiOrgSession organisation API session
        * @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
        * @param recordType type of record data to search for.
        * @param supplierOrgID unique ID of the organisation in the SQUIZZ.com platform that has supplies the customer account
        * @param customerAccountCode code of the account organisation's customer account. Customer account only needs to be set if the supplier organisation has assigned multiple accounts to the organisation logged into the API session (customer org) and account specific data is being obtained
        * @param beginDateTime earliest date time to search for records for. Date time set as milliseconds since 1/1/1970 12am UTC epoch
        * @param endDateTime latest date time to search for records up to.Date time set as milliseconds since 1/1/1970 12am UTC epoch
        * @param pageNumber page number to obtain records from
        * @param recordsMaxAmount maximum number of records to return
        * @param outstandingRecords if true then only search for records that are marked as outstanding (such as unpaid invoices)
        * @param searchString search text to match records on
        * @param keyRecordIDs comma delimited list of records unique key record ID to match on.Each Key Record ID value needs to be URI encoded
        * @param searchType specifies the field to search for records on, matching the record's field with the search string given
        * @return APIv1EndpointResponseESD response from calling the API endpoint with the obtained Ecommerce Standards Document containing customer account enquiry records
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $recordType, $supplierOrgID, $customerAccountCode, $beginDateTime, $endDateTime, $pageNumber, $recordsMaxAmount, $outstandingRecords, $searchString, $keyRecordIDs, $searchType)
		{
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponseESD();
			$deserializeESDDocument = new ESDocumentCustomerAccountEnquiry();
			$callEndpoint = true;
			
			try{
				//set endpoint parameters
                $endpointParams ="record_type=".$recordType."&supplier_org_id=".urlencode(utf8_encode($supplierOrgID))."&customer_account_code=".urlencode(utf8_encode($customerAccountCode))."&begin_date_time=".$beginDateTime."&end_date_time=".$endDateTime."&page_number=".$pageNumber."&records_max_amount=".$recordsMaxAmount."&outstanding_records=".($outstandingRecords ? "Y" : "N")."&search_string=".urlencode(utf8_encode($searchString))."&key_record_ids=".urlencode(utf8_encode($keyRecordIDs))."&search_type=".urlencode(utf8_encode($searchType));
				
				//make a HTTP request to the platform's API endpoint to search for the customer account enquiry records
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
				
					$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_GET, APIv1Constants::API_ORG_ENDPOINT_SEARCH_CUSTOMER_ACCOUNT_RECORDS_ESD . APIv1Constants::API_PATH_SLASH . $apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, "", null, $endpointTimeoutMilliseconds, $apiOrgSession->getLangBundle(), $endpointJSONReader, $endpointResponse);
					
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
