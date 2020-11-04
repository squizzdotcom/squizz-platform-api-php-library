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
	use EcommerceStandardsDocuments\ESDocumentOrderSale;
	use \JsonMapper;

	/**
	* Class handles calling the SQUIZZ.com API endpoint to send one more of an organisation's sales orders into the platform, where they are then validated, optionally re-priced and raised against the organisation for processing and dispatch.
	* This endpoint allows an organisation to import its own sales orders from its own selling system(s) into SQUIZZ.com, via an API session that the organisation has logged into
	*/
	class APIv1EndpointOrgImportSalesOrder
	{
		/**
		* Calls the platform's API endpoint and pushes up and import organisation sales order record in a Ecommerce Standards Document of a specified type
		* @param apiOrgSession existing organisation API session
		* @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
		* @param esDocumentOrderSale Sales Order Ecommerce Standards Document that contains one or more sales order records
		* @param repriceOrder if true then allow the order lines and surcharges to be repriced on import		
		* @return response from calling the API endpoint containing a Ecommerce Standards Document enclosed within it
		*/
		public static function call($apiOrgSession, $endpointTimeoutMilliseconds, $esDocumentOrderSale, $repriceOrder)
		{
			$requestHeaders = array();
			$endpointResponse = new APIv1EndpointResponseESD();
			
			try{
				//set notification parameters
				$endpointParams = "reprice_order=".($repriceOrder? ESDocumentConstants::ESD_VALUE_YES: ESDocumentConstants::ESD_VALUE_NO);
				
				//set function used to read the response from the endpoint
				$endpointJSONReader = function($jsonArray, $endpointResponse){
					$endpointResponse->jsonDeserialize($jsonArray);
					
					//deserialize array into sales order ESD
					$jsonMapper = new JsonMapper();
					$jsonMapper->bEnforceMapType = false;
					$esDocumentOrderSaleResponse = $jsonMapper->map($jsonArray, new ESDocumentOrderSale());
					
					//add ESDocument to endpoint response and return the response
					$endpointResponse->esDocument = $esDocumentOrderSaleResponse;
					return $endpointResponse;
				};
				
				//make a HTTP request to the platform's API endpoint to import the ESD containing the sales order(s)
				$endpointResponse = APIv1HTTPRequest::sendESDocumentHTTPRequest(APIv1Constants::HTTP_REQUEST_METHOD_POST, APIv1Constants::API_ORG_ENDPOINT_IMPORT_SALES_ORDER_ESD . APIv1Constants::API_PATH_SLASH . $apiOrgSession->getSessionID(), $endpointParams, $requestHeaders, "", $esDocumentOrderSale, $endpointTimeoutMilliseconds, $apiOrgSession->getLangBundle(), $endpointJSONReader, $endpointResponse);
				
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
		
		/**
		* gets a list of order indexes that contain order lines that could not be matched up to the organisation's own products
		* @param esDocument Ecommerce standards document containing configuration that specifies unmatched order lines
		* @return an array containing pairs. Each pair has the index of the order, and the index of the order line that could not be mapped
		*/
		public static function getUnmatchedOrderLines($esDocument)
		{
			$upmappedOrderLines = array();
			
			//check that the ecommerce standards document's configs contains a key specifying the unmatced order lines
			if(array_key_exists(APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNMATCHED_LINES, $esDocument->configs))
			{
				//get comma separated list of order record indicies and line indicies that indicate the unmatched order lines
				$unmappedOrderLineCSV = $esDocument->configs[APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNMATCHED_LINES];

				//get the index of the order record and line that contained the unmatched product
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
		
		/**
		* gets a list of order indexes that contain order surcharges that could not be matched to the organisation's own surcharges
		* @param esDocument Ecommerce standards document containing configuration that specifies unmatched order surcharges
		* @return an array containing pairs. Each pair has the index of the order, and the index of the order surcharge that could not be matched
		*/
		public static function getUnmatchedOrderSurcharges($esDocument)
		{
			$unmatchedOrderSurcharges = array();
			
			//check that the ecommerce standards document's configs contains a key specifying the unmapped order surcharges
			if(array_key_exists(APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNMATCHED_SURCHARGES, $esDocument->configs))
			{
				//get comma separated list of order record indicies and surcharge record indicies that indicate the unmatched order surcharge
				$unmappedOrderSurchargeCSV = $esDocument->configs[APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNMATCHED_SURCHARGES];

				//get the index of the order record and surcharge that contained the unmatched surcharge
				if(strlen(trim($unmappedOrderSurchargeCSV)) > 0){
					$unmappedOrderSurchargeIndices = explode(',', trim($unmappedOrderSurchargeCSV));

					//iterate through each order-surcharge index
					foreach($unmappedOrderSurchargeIndices as $unmatchedOrderSurchargeIndex){
						//get order index and surcharge index
						$orderSurchargeIndex = explode(':',$unmatchedOrderSurchargeIndex);
						if(count($orderSurchargeIndex) == 2){
						
							try{
								$orderIndex = $orderSurchargeIndex[0];
								$surchargeIndex = (int)$orderSurchargeIndex[1];
								$unmatchedOrderSurcharges[$orderIndex] = $surchargeIndex;
							}catch(Exception $ex){
							}
						}
					}
				}
			}
			
			return $unmatchedOrderSurcharges;
		}
		
		/**
		* gets a list of order indexes that contain order surcharges that could not be priced for the organisation's own surcharges
		* @param esDocument Ecommerce standards document containing configuration that specifies unpriced order surcharges
		* @return an array containing pairs. Each pair has the index of the order, and the index of the order surcharge that could not be priced
		*/
		public static function getUnpricedOrderSurcharges($esDocument)
		{
			$unpricedOrderSurcharges = array();
			
			//check that the ecommerce standards document's configs contains a key specifying the unpriced order surcharges
			if(array_key_exists(APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNPRICED_SURCHARGES, $esDocument->configs))
			{
				//get comma separated list of order record indicies and surcharge indicies that indicate the unpriced order surcharges
				$unpricedOrderSurchargeCSV = $esDocument->configs[APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNPRICED_SURCHARGES];

				//get the index of the order record and surcharge that contained the unpriced product
				if(strlen(trim($unpricedOrderSurchargeCSV)) > 0){
					$unmappedOrderSurchargeIndices = explode(',', trim($unpricedOrderSurchargeCSV));

					//iterate through each order-surcharge index
 					foreach($unmappedOrderSurchargeIndices as $unpricedOrderSurchargeIndex){
						//get order index and surcharge index
						$orderSurchargeIndex = explode(':',$unpricedOrderSurchargeIndex);
						if(count($orderSurchargeIndex) == 2){
							try{
								$orderIndex = (int)$orderSurchargeIndex[0];
								$surchargeIndex = (int)$orderSurchargeIndex[1];
								$unpricedOrderSurcharges[$orderIndex] = $surchargeIndex;
							}catch(Exception $ex){
							}
						}
					}
				}
			}
			
			return $unpricedOrderSurcharges;
		}
		
		/**
		* gets a list of order indexes that contain order payments that could not be matched to the organisation's own payment types
		* @param esDocument Ecommerce standards document containing configuration that specifies unmatched order payments
		* @return an array containing pairs. Each pair has the index of the order, and the index of the order payment that could not be matched
		*/
		public static function getUnmatchedOrderPayments($esDocument)
		{
			$unmatchedOrderPayments = array();
			
			//check that the ecommerce standards document's configs contains a key specifying the unmapped order payments
			if(array_key_exists(APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNMATCHED_PAYMENTS, $esDocument->configs))
			{
				//get comma separated list of order record indicies and payment record indicies that indicate the unmatched order payment
				$unmappedOrderPaymentCSV = $esDocument->configs[APIv1EndpointResponseESD::ESD_CONFIG_ORDERS_WITH_UNMATCHED_PAYMENTS];

				//get the index of the order record and payment that contained the unmatched payment
				if(strlen(trim($unmappedOrderPaymentCSV)) > 0){
					$unmappedOrderPaymentIndices = explode(',', trim($unmappedOrderPaymentCSV));

					//iterate through each order-payment index
					foreach($unmappedOrderPaymentIndices as $unmatchedOrderPaymentIndex){
						//get order index and payment index
						$orderPaymentIndex = explode(':',$unmatchedOrderPaymentIndex);
						if(count($orderPaymentIndex) == 2){
						
							try{
								$orderIndex = $orderPaymentIndex[0];
								$paymentIndex = (int)$orderPaymentIndex[1];
								$unmatchedOrderPayments[$orderIndex] = $paymentIndex;
							}catch(Exception $ex){
							}
						}
					}
				}
			}
			
			return $unmatchedOrderPayments;
		}
	}
?>