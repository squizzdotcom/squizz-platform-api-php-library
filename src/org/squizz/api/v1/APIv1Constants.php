<?php
	/**
	* Copyright (C) 2017 Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	namespace org\squizz\api\v1;

	/**
	* Stores constant variables required for accessing the platform's API
	*/
	class APIv1Constants
	{
		/**
		* HTTP protocol to use when calling the platform's API
		*/
		const API_PROTOCOL = "http://";
		
		/**
		* internet domain to use when calling the platform's API
		*/
		const API_DOMAIN = "api.squizz.com";
		
		/**
		* URL path within the platform's API to call organisation endpoints
		*/
		const API_ORG_PATH = "/rest/1/org/";
		
		/**
		* slash character to set within URL path to API endpoint requests
		*/
		const API_PATH_SLASH = "/";
		
		/**
		* name of the platform's API endpoint to call to create a session in the API for an organisation
		*/
		const API_ORG_ENDPOINT_CREATE_SESSION = "create_session";
		
		/**
		* name of the platform's API endpoint to call to destroy a session in the API for an organisation
		*/
		const API_ORG_ENDPOINT_DESTROY_SESSION = "destroy_session";
		
		/**
		* name of the platform's API endpoint to call to validate a session in the API for an organisation
		*/
		const API_ORG_ENDPOINT_VALIDATE_SESSION = "validate_session";
		
		/**
		* name of the platform's API endpoint to call to create an organisation notification
		*/
		const API_ORG_ENDPOINT_CREATE_NOTIFCATION = "create_notification";
		
		/**
		* name of the platform's API endpoint to call to create an organisation notification
		*/
		const API_ORG_ENDPOINT_VALIDATE_CERT = "validate_cert";
		
		/**
		* name of the platform's API endpoint to call to import organisation data stored within an Ecommerce Standards Documents
		*/
		const API_ORG_ENDPOINT_IMPORT_ESD = "import_esd";
		
		/**
		* name of the platform's API endpoint to call to send a purchase order to a supplier organisation for procurement
		*/
		const API_ORG_ENDPOINT_PROCURE_PURCHASE_ORDER_FROM_SUPPLIER = "procure_purchase_order_from_supplier";
		
		/**
		* name of the platform's API endpoint to call to get organisation data returned in an Ecommerce Standards Document from a connected organisation
		*/
		const API_ORG_ENDPOINT_RETRIEVE_ESD = "retrieve_esd";
		
		/**
		* name of the endpoint attribute in the API endpoint response that contains the result code
		*/
		const API_ORG_ENDPOINT_ATTRIBUTE_RESULT_CODE = "result_code";
		
		/**
		* HTTP request method used to post data
		*/
		const HTTP_REQUEST_METHOD_POST = "POST";
		
		/**
		* HTTP request method to get data
		*/
		const HTTP_REQUEST_METHOD_GET = "GET";
		
		/**
		* HTTP request content type header name
		*/
		const HTTP_REQUEST_CONTENT_TYPE = "Content-Type";
		
		/**
		* HTTP request content type header for specifying that the request body consists of JSON data
		*/
		const HTTP_REQUEST_CONTENT_TYPE_JSON = "application/json";
		
		/**
		* English australian locale that the API supports returning messages in
		*/
		const SUPPORTED_LOCALES_EN_AU = "en_AU";
		
		/**
		* Name of the package that contains the language bundles used for storing locale languages
		*/
		const LANG_BUNDLE_NAME = "org.squizz.api.v1.lang.APIv1LangBundle";
	}
?>