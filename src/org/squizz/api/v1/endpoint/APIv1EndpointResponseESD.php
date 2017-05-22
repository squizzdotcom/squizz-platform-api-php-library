<?php
	/**
	* Copyright (C) 2017 Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	namespace org\squizz\api\v1\endpoint;
	use org\esd\EcommerceStandardsDocuments\ESDocument;
	use org\squizz\api\v1\endpoint\APIv1EndpointResponse;

	/**
	* Represents a response returned from the platform's API containing a Ecommerce Standards Document in its body
	*/
	class APIv1EndpointResponseESD
	{
		const ESD_CONFIG_ORDERS_WITH_UNMAPPED_LINES = "orders_with_unmapped_lines";
		const ESD_CONFIG_ORDERS_WITH_UNPRICED_LINES = "orders_with_unpriced_lines";
		
		//set default values for the response
		public $result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
		public $result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
		public $result_message = "";
		public $api_version = "1.0.0.0";
		public $session_id = "";
		public $session_valid = "";
		public $esDocument = null;
		
		/**
		* deserializes an array and gets the values from a JSON object and sets them into the properties of the value
		* @param encodedDataArray	gets an array from 
		*/
		public function jsonDeserialize($encodedDataArray)
		{
			if(array_key_exists("result", $encodedDataArray)){
				$this->result = $encodedDataArray["result"];
			}
			
			if(array_key_exists("result_code", $encodedDataArray)){
				$this->result_code = $encodedDataArray["result_code"];
			}
			
			if(array_key_exists("result_message", $encodedDataArray)){
				$this->result_message = $encodedDataArray["result_message"];
			}
			
			if(array_key_exists("api_version", $encodedDataArray)){
				$this->api_version = $encodedDataArray["api_version"];
			}
			
			if(array_key_exists("session_id", $encodedDataArray)){
				$this->session_id = $encodedDataArray["session_id"];
			}
			
			if(array_key_exists("session_valid", $encodedDataArray)){
				$this->session_valid = $encodedDataArray["session_valid"];
			}
		}
	}
?>