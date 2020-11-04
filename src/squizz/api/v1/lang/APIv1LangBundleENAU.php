<?php
	/**
	* Copyright (C) Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	namespace squizz\api\v1\lang;
	use squizz\api\v1\endpoint\APIv1EndpointResponse;
	use squizz\api\v1\lang\IAPIv1LangBundle;

	/**
	* Defines the language used for the platform's API, by default in English Australian
	*/
	class APIv1LangBundleENAU implements IAPIv1LangBundle
	{
		/**
		* defines key value pairs of language used for the API
		*/
		public $lang = array(
			APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS => "SQUIZZ.com API was successfully called.",
			APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE => "An error occurred when SQUIZZ.com's API was called.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_UNKNOWN => "An unknown or non-specified error occurred when calling SQUIZZ.com's API.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_MALFORMED_URL => "An error occurred when calling SQUIZZ.com's API due to URL being not correctly set.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_RESPONSE => "An error occurred when calling SQUIZZ.com's API due to the server returning a bad response. The platform's API may be unavailable, under heavy load, or a network connection error could be occurring.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_REQUEST_PROTOCOL => "An error occurred when calling SQUIZZ.com's API due to an issue with the protocol used to call the endpoint.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_CONNECTION => "An error occurred when calling SQUIZZ.com's API due to an issue with connecting to the platform's servers. Check that your internet connection is available and no other networking issues are occurring.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_IO => "An error occurred when calling SQUIZZ.com's API due to an IO (Reading or Writing) error.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORG_NOT_FOUND => "An error occurred when calling SQUIZZ.com's API since no organisation could be found matching the ID given.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INCORRECT_API_CREDENTIALS => "An error occurred when calling SQUIZZ.com's API due to incorrect API credentials being given for the organisation.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORG_INACTIVE => "An error occurred when calling SQUIZZ.com's API due to the organisation being inactive or deleted from the platform.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_SESSION_INVALID => "An error occurred when calling SQUIZZ.com's API due to the API session not existing or previously destroyed.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INVALID_NOTIFICATION_CATEGORY => "An error occurred when calling SQUIZZ.com's API due to an incorrect notification category been given to it.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_NO_ORG_PEOPLE_TO_NOTIFY => "An error occurred when calling SQUIZZ.com's API due to no people being configured in the organisation to receive the notification.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INSUFFICIENT_CREDIT => "An error occurred when calling SQUIZZ.com's API due to the organisation having insufficient trading tokens in the platform to process the request.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_SECURITY_CERTIFICATE_NOT_FOUND => "An error occurred when calling SQUIZZ.com's API due to the organisation's security certificate not able to found or does not exist.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_SENDER_DOES_NOT_MATCH_CERTIFICATE_COMMON_NAME => "An error occurred when calling SQUIZZ.com's API, due to the common name set in the organisation's security certificate not matching the IP address of the internet connection used to call the endpoint",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INVALID_API_ACTION => "An error occurred when calling SQUIZZ.com's API, due to the endpoint being called not being found or no longer existing.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_PERMISSION_DENIED => "An error occurred when calling SQUIZZ.com's API due to permission being denied to access data, or save data, or access the endpoint.",
				
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_DATA_IMPORT_MISSING_IMPORT_TYPE => "An error occurred when calling SQUIZZ.com's API due to no data type set indicating the kind of data being imported.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_DATA_IMPORT_MAX_IMPORTS_RUNNING => "An error occurred when calling SQUIZZ.com's API due to the maximum number of data imports being run over a short period of time. Wait a while before calling the endpoint again and consider calling the endpoint less often.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_DATA_IMPORT_BUSY => "An error occurred when calling SQUIZZ.com's API due to another data import already running or the API is busy processing other requests.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_DATA_IMPORT_NOT_FOUND => "An error occurred when calling SQUIZZ.com's API due to an incorrect or unsupported data type being set.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_DATA_JSON_WRONG_CONTENT_TYPE => "An error occurred when calling SQUIZZ.com's API due to the content type in the request headers not being set to application/json, specifying that data imported is in the JSON data format.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_DATA_JSON_MALFORMED => "An error occurred when calling SQUIZZ.com's API due to the data being imported in the JSON data format not able to be processed, because it has not been correctly formed. Check for syntax errors with the JSON data.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ESD_DOCUMENT_HEADER_MALFORMED => "An error occurred when calling SQUIZZ.com's API due to the Ecommerce Standards Document uploaded missing an opening bracket in its JSON data.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ESD_DOCUMENT_HEADER_MISSING_ATTRIBUTES => "An error occurred when calling SQUIZZ.com's API due to the Ecommerce Standards Document being uploaded missing the dataRecords attribute that should contain the record data to import.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_DATA_IMPORT_ABORTED => "An error occurred when calling SQUIZZ.com's API due to the data import being aborted. It may have been aborted by a person or by the platform.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ESD_DOCUMENT_UNSUCCESSFUL => "An error occurred when calling SQUIZZ.com's API due to Ecommerce Standards Document failing to import. Check that the document was correctly formed.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ESD_DOCUMENT_NO_RECORD => "An error occurred when calling SQUIZZ.com's API due to Ecommerce Standards Document not containing any records to import. Look to add one or more records to the document.",
			
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORG_DOES_NOT_EXIST => "An error occurred when calling SQUIZZ.com's API due to the organisation given not able to be found in the platform, or it is not active.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORG_NOT_SELLING => "An error occurred when calling SQUIZZ.com's API due to the organisation's Trading Status does not allow it to sell on the platform.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORG_NOT_ENOUGH_CREDITS => "An error occurred when calling SQUIZZ.com's API due to the organisation not having enough trading tokens.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_NO_ORG_CUSTOMER_ACCOUNT_SET => "An error occurred when calling SQUIZZ.com's API due to the supplier organisation having no active customer accounts assigned to the customer organistion.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_NO_ORG_CUSTOMER_ACCOUNT_ASSIGNED => "An error occurred when calling SQUIZZ.com's API due to no customer account in the supplier's organisation able to be found with the given account code.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_CUSTOMER_ACCOUNT_NO_ACCOUNT_PAYMENT_TYPE => "An error occurred when calling SQUIZZ.com's API due to supplier organisation's customer account not having any On Account payment method set.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_PRODUCT_NOT_MAPPED => "An error occurred when calling SQUIZZ.com's API due to not being able to find a matching product in the supplier's organisation.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_MAPPED_PRODUCT_PRICE_NOT_FOUND => "An error occurred when calling SQUIZZ.com's API due to not being able to price a product in the supplier's organisation.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_CUSTOMER_ACCOUNT_ON_HOLD => "An error occurred when calling SQUIZZ.com's API due to the customer account in the supplier's organisation being on hold and not allowing for further trading activity.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_CUSTOMER_ACCOUNT_OUTSIDE_BALANCE_LIMIT => "An error occurred when calling SQUIZZ.com's API due to the customer account's balance in the supplier's organisation being over the allowed limit. Not further trading can occur with the account until payments have been made against the account's balance.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INCORRECT_DATA_TYPE => "An error occurred when calling SQUIZZ.com's API due to the data type specified not being supported or of the correct type.",
            APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_MAPPED_PRODUCT_STOCK_NOT_AVAILABLE => "An error occurred when calling SQUIZZ.com's API due to not being able to find available stock for one or more products in the supplier's organisation.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_MAX_SEARCHES_REACHED => "An error occurred when calling SQUIZZ.com's API due to the maximum number search requested being made. Look to wait a few minutes before trying to searh again.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INCORRECT_RECORD_TYPE => "An error occurred when calling SQUIZZ.com's API due to the wrong or unsupported record type being given.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_TEXT_LINES_ONLY_NOT_ALLOWED => "An error occurred when calling SQUIZZ.com's API due to the supplier organisation not allowing orders that contain only text lines.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_SERVER_ERROR_ORDER_TEXT_LINES_ONLY_NOT_ALLOWED => "An error occurred when calling SQUIZZ.com's API due to the supplier organisation not allowing orders that contain only text lines.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORG_CANNOT_BE_FOUND => "An error occurred when call SQUIZZ.com's API due to no organisation that can be found with the organisation ID provided.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INVOICE_LINE_NOT_MAPPED => "An error occurred when calling SQUIZZ.com's API due to a line within the invoice not able to be matched up to one of the customer's products, labour, or downloads.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INVOICE_SURCHARGE_NOT_MAPPED => "An error occurred when calling SQUIZZ.com's API due to a surcharge within the invoice not able to the matched up to one of the customer's surcharges.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INVOICE_LINE_TAXCODE_NOT_MAPPED => "An error occurred when calling SQUIZZ.com's API due to a line's taxcode within the invoice not able to be matched up to one of the customer's taxcodes.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_INVOICE_SURCHARGE_TAXCODE_NOT_MAPPED => "An error occurred when calling SQUIZZ.com's API due to a surcharge's taxcode within the invoice not able to be matched up to one of the customer's taxcodes.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_NO_ORG_SUPPLIER_ACCOUNT_ASSIGNED => "An error occurred when calling SQUIZZ.com's API due to no supplier account in the customer's organisation able to be found with the given account code.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_NO_ORG_SUPPLIER_ACCOUNT_SET => "An error occurred when calling SQUIZZ.com's API due to the customer organisation having no active supplier accounts assigned to the supplier organistion.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_PRODUCT_NOT_MATCHED => "An error occurred when calling SQUIZZ.com's API due to not being able to find a matching organisation's product for the order.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_LINE_PRICING_MISSING => "An error occurred when calling SQUIZZ.com's API due to not being able to price one or more products in the order.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_SURCHARGE_NOT_FOUND => "An error occurred when calling SQUIZZ.com's API due to not being able to find a matching organisation's surcharge for the order.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_SURCHARGE_PRICING_MISSING => "An error occurred when calling SQUIZZ.com's API due to not being able to price one or more surcharges in the order.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_ORDER_PAYMENT_NOT_MATCHED => "An error occurred when calling SQUIZZ.com's API due to not being able to find a matching organisation's payment for the order.",
			APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_PAYMENT_STATUS_NOT_SUPPORTED => "An error occurred when calling SQUIZZ.com's API due to the payment status set for the order not being supported."
		);
		
		public function getString($languageCode)
		{
			return $this->lang[$languageCode];
		}
	}
?>