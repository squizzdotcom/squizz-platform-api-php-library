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
	* Class handles calling the SQUIZZ.com API endpoint to send one more of an organisation's purchase orders into the platform, where they are then converted into sales orders and sent to a supplier organisation for processing and dispatch.
	* This endpoint allows goods and services to be purchased by the "customer" organisation logged into the API session from their chosen supplier organisation
	*/
	class APIv1EndpointOrgProcurePurchaseOrderFromSupplier 
	{
		/**
		* Calls the platform's API endpoint and pushes up and import organisation data in a Ecommerce Standards Document of a specified type
		* @param apiOrgSession existing organisation API session
		* @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
		* @param supplierOrgID unique ID of the supplier organisation in the SQUIZZ.com platform
		* @param customerAccountCode code of the supplier organisation's customer account. Customer account only needs to be set if the supplier organisation has assigned multiple accounts to the organisation logged into the API session (customer org)
		* @param esDocumentOrderPurchase Purchase Order Ecommerce Standards Document that contains one or more purchase order records
		* @return response from calling the API endpoint containing a Ecommerce Standards Document enclosed within it
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $supplierOrgID, $customerAccountCode, $esDocumentOrderPurchase)
		{
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponseESD();
			
			try{
				//set notification parameters
				$endpointParams = "supplier_org_id=". urlencode(utf8_encode($supplierOrgID)) . "&customer_account_code=".urlencode(utf8_encode($customerAccountCode));
				
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					
					//deserialize array into sales order ESD
					$jsonMapper = new JsonMapper();
					$jsonMapper->bEnforceMapType = false;
					$esDocumentOrderSale = $jsonMapper->map($jsonArray, new ESDocumentOrderSale());
					
					//add ESDocument to endpoint response and return the response
					$endpointResponse->esDocument = $esDocumentOrderSale;
					return $endpointResponse;
				};
				
				//make a HTTP request to the platform's API endpoint to send the ESD containing the purchase orders
				$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_POST, APIv1Constants::API_ORG_ENDPOINT_PROCURE_PURCHASE_ORDER_FROM_SUPPLIER . APIv1Constants::API_PATH_SLASH . $apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, "", $esDocumentOrderPurchase, $endpointTimeoutMilliseconds, $apiOrgSession->getLangBundle(), $endpointJSONReader, $endpointResponse);
				
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
		
		/**
		* gets a list of order indexes that contain order lines that could not be mapped to a supplier organisation's products
		* @param esDocument Ecommerce standards document containing configuration that specifies unmapped order lines
		* @return an array containing pairs. Each pair has the index of the order, and the index of the order line that could not be mapped
		*/
		public static function getUnmappedOrderLines($esDocument)
		{
			$upmappedOrderLines = array();
			
			//check that the ecommerce standards document's configs contains a key specifying the unmapped order lines
			if(array_key_exists(APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNMAPPED_LINES, $esDocument->configs))
			{
				//get comma separated list of order record indicies and line indicies that indicate the unmapped order lines
				$unmappedOrderLineCSV = $esDocument->configs[APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNMAPPED_LINES];

				//get the index of the order record and line that contained the unmapped product
				if(strlen(trim($unmappedOrderLineCSV)) > 0){
					$unmappedOrderLineIndices = explode(',', trim($unmappedOrderLineCSV));

					//iterate through each order-line index
					foreach($unmappedOrderLineIndices as $unmappedOrderLineIndex){
						//get order index and line index
						$orderLineIndex = explode(':',$unmappedOrderLineIndex);
						if(count($orderLineIndex) == 2){
						
							try{
								$orderIndex = $orderLineIndex[0];
								$lineIndex = (int)$orderLineIndex[1];
								$upmappedOrderLines[$orderIndex] = $lineIndex;
							}catch(Exception $ex){
							}
						}
					}
				}
			}
			
			return $upmappedOrderLines;
		}
		
		/**
		* gets a list of order indexes that contain order lines that could not be priced for a supplier organisation's products
		* @param esDocument Ecommerce standards document containing configuration that specifies unpriced order lines
		* @return an array containing pairs. Each pair has the index of the order, and the index of the order line that could not be priced
		*/
		public static function getUnpricedOrderLines($esDocument)
		{
			$unpricedOrderLines = array();
			
			//check that the ecommerce standards document's configs contains a key specifying the unpriced order lines
			if(array_key_exists(APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNPRICED_LINES, $esDocument->configs))
			{
				//get comma separated list of order record indicies and line indicies that indicate the unpriced order lines
				$unpricedOrderLineCSV = $esDocument->configs[APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNPRICED_LINES];

				//get the index of the order record and line that contained the unpriced product
				if(strlen(trim($unpricedOrderLineCSV)) > 0){
					$unpricedOrderLineIndices = explode(',', trim($unpricedOrderLineCSV));

					//iterate through each order-line index
 					foreach($unpricedOrderLineIndices as $unpricedOrderLineIndex){
						//get order index and line index
						$orderLineIndex = explode(':',$unpricedOrderLineIndex);
						if(count($orderLineIndex) == 2){
							try{
								$orderIndex = (int)$orderLineIndex[0];
								$lineIndex = (int)$orderLineIndex[1];
								$unpricedOrderLines[$orderIndex] = $lineIndex;
							}catch(Exception $ex){
							}
						}
					}
				}
			}
			
			return $unpricedOrderLines;
		}
	}
?>