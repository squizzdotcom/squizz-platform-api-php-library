<?php
	/**
	* Copyright (C) 2017 Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	namespace org\squizz\api\v1;

 	use org\esd\EcommerceStandardsDocuments\ESDocumentConstants;
	use org\esd\EcommerceStandardsDocuments\ESDocument;
	use org\squizz\api\v1\endpoint\APIv1EndpointResponse;
	use org\squizz\api\v1\APIv1Constants;
	use org\squizz\api\v1\lang\IAPIv1LangBundle;
	//import org.esd.EcommerceStandardsDocuments.*;
	use org\squizz\api\v1\endpoint\APIv1EndpointResponseESD;

	/**
	* A generic class that can be used to send HTTP requests to the platform's API and return HTTP a response as an endpoint object
	*/
	class APIv1HTTPRequest
	{
		const HTTP_HEADER_CONTENT_TYPE = "Content-Type";
		const HTTP_HEADER_CONTENT_TYPE_FORM_URL_ENCODED = "application/x-www-form-urlencoded";
		const HTTP_HEADER_CONTENT_TYPE_JSON = "application/json";
		const HTTP_HEADER_CONTENT_ENCODING = "Content-Encoding";
		const HTTP_HEADER_CONTENT_ENCODING_GZIP = "gzip";
		const HTTP_RESPONSE_CODE_OK = 200;
		
		/**
		* Sends a HTTP request with a specified URL, headers and optionally post data to the SQUIZZ.com platform's API. Parses JSON data returned into a HTTP response
		* @param <T> The endpoint response class used to de-serialize the JSON response. This class should contain the properties that are expected to be returned from the API's endpoint
		* @param requestMethod method to send the HTTP request with. Set to POST to push up data
		* @param endpointName name of the endpoint in the platform's API to send the request to
		* @param endpointParams list of parameters to append to the end of the request's URL
		* @param requestHeaders list of key value pairs to add to the request's headers
		* @param endpointPostData data to place in the body of the HTTP request and post up
		* @param timeoutMilliseconds amount of milliseconds to wait before giving up waiting on receiving a response from the API. For larger amounts of data posted increase the timeout time
		* @param langBundle language bundle to use to return result messages in
		* @param endpointJSONReader the reader used to deserialize the JSON response from the request. ensure that the reader can deserialize the same generic class set when calling the method
		* @param endpointJSONReaderFunction the response reader function reads the response from calling the platform's API and converts the response into a specified endpoint object
		* @return a type of endpoint response based on the type of endpoint being called.
		*/
		public static function sendHTTPRequest($requestMethod, $endpointName, $endpointParams, $requestHeaders, $endpointPostData, $timeoutMilliseconds, $langBundle, $endpointJSONReaderFunction, $endpointResponse)
		{
			//make a request to login to the API with the credentials
			$responseCode = 0;
			
			try 
			{
				//set URL
				$serverAddress = APIv1Constants::API_PROTOCOL . APIv1Constants::API_DOMAIN . APIv1Constants::API_ORG_PATH . $endpointName . "?" . $endpointParams;
			
				// create new request
				$webConnection = curl_init($serverAddress);
				curl_setopt($webConnection, CURLOPT_CUSTOMREQUEST, $requestMethod);
				curl_setopt($webConnection, CURLOPT_HTTPHEADER, $requestHeaders);
				curl_setopt($webConnection, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($webConnection, CURLOPT_CONNECTTIMEOUT ,0); 
				curl_setopt($webConnection, CURLOPT_TIMEOUT, $timeoutMilliseconds/1000);
				
				//set the body of the request if required
				if(strcasecmp($requestMethod, APIv1Constants::HTTP_REQUEST_METHOD_POST) == 0){
					//webConnection.setRequestProperty("Content-Length", String.valueOf(endpointPostData.length()));
					curl_setopt($webConnection, CURLOPT_POSTFIELDS, $endpointPostData);
				}
				
				//call the server to make a HTTP response
				$serverResponseBody = curl_exec($webConnection);
				
				//chech jf a successful response was returned from the serve
				$responseCode = curl_getinfo($webConnection, CURLINFO_HTTP_CODE);
				if ($responseCode == self::HTTP_RESPONSE_CODE_OK) 
				{
					//obtain the encoding returned by the server
					//$encoding = $webConnection->getContentEncoding();
					
					//The Content-Type can be used later to determine the nature of the content regardless of compression
					$contentType = curl_getinfo($webConnection, CURLINFO_CONTENT_TYPE);
					
					//get the body of the response based from the encoding type
					$responseBody = '';
					if (strcasecmp($contentType, self::HTTP_HEADER_CONTENT_ENCODING_GZIP) == 0) {
						$responseBody = gzdecode($serverResponseBody);
					}
					else if (strcasecmp($contentType, "deflate") == 0) {
						//resultingInputStream = new InflaterInputStream((InputStream)webConnection.getContent(), new Inflater(true));
					}
					else {
						//resultingInputStream = (InputStream)webConnection.getContent();
						$responseBody = $serverResponseBody;
					}
					
					//deserialize HTTP response from JSON into the endpoint response object
					$endpointResponse = $endpointJSONReaderFunction(json_decode($responseBody, true), $endpointResponse);
					
					//get the message that corresponds with the result code
					if(array_key_exists($endpointResponse->result_code, $langBundle)){
						$endpointResponse->result_message = $langBundle[$endpointResponse->result_code];
					}
				}else{
					$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
					$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_RESPONSE;
					$endpointResponse->result_message = $langBundle->getString($endpointResponse->result_code) . "\nResponse Code: " . $responseCode;
				
				}
			} catch (Exception $ex) {
				$endpointResponse->result = APIv1EndpointResponse::ENDPOINT_RESULT_FAILURE;
				$endpointResponse->result_code = APIv1EndpointResponse::ENDPOINT_RESULT_CODE_ERROR_CONNECTION;
				$endpointResponse->result_message = $langBundle->getString($endpointResponse->result_code) . "\n" . $ex.getMessage();
			}
			
			return $endpointResponse;
			
		}
		
		/**
		* Sends a HTTP request with a specified URL, headers and data of a Ecommerce Standards Document to the SQUIZZ.com platform's API. 
		* Parses JSON data returned from a HTTP response into an Ecommerce Standards Document of a specified type
		* Note that data uploaded is compressed using GZIP
		* @param requestMethod method to send the HTTP request as, either GET or POST
		* @param endpointName name of the endpoint in the platform's API to send the request to
		* @param endpointParams list of parameters to append to the end of the request's URL
		* @param requestHeaders list of key value pairs to add to the request's headers
		* @param postData content to send in the body of the request, this text is ignored if esDocument is not null
		* @param esDocument Ecommerce Standards Document containing the records and data to push up to the platform's API
		* @param timeoutMilliseconds amount of milliseconds to wait before giving up waiting on receiving a response from the API. For larger amounts of data posted increase the timeout time
		* @param langBundle language bundle to use to return result messages in
		* @param endpointJSONReader the reader used to deserialize the JSON response from the request. ensure that the reader can deserialize the same generic class set when calling the method
		* @param endpointResponse the response object that may be used to report the response from the server
		* @return a type of endpoint response based on the type of endpoint being called, with the response containing the ESDocument
		*/
		/*
		public static function APIv1EndpointResponseESD sendESDocumentHTTPRequest(String requestMethod, String endpointName, String endpointParams, ArrayList<Pair<String, String>> requestHeaders, String postData, ESDocument esDocument, int timeoutMilliseconds, ResourceBundle langBundle, ObjectReader endpointJSONReader, APIv1EndpointResponseESD endpointResponse)
		{
			//make a request to login to the API with the credentials
			HttpURLConnection webConnection = null;
			OutputStreamWriter httpRequestContentWriter = null;
			BufferedReader httpResponseBufferReader = null;
			StringBuilder builder = null;
			int responseCode = 0;
			
			try 
			{
				//create a new HTTP connection
				URL serverAddress = new URL(APIv1Constants.API_PROTOCOL + APIv1Constants.API_DOMAIN + APIv1Constants.API_ORG_PATH + endpointName + "?" + endpointParams);
				webConnection = (HttpURLConnection)serverAddress.openConnection();
				webConnection.setRequestMethod(requestMethod);
				webConnection.setReadTimeout(timeoutMilliseconds);
				
				//add the header properties to the request
				for(int i=0; i < requestHeaders.size(); i++){
					webConnection.setRequestProperty(requestHeaders.get(i).getKey(), requestHeaders.get(i).getValue());
				}
				
				//set the body of the request
				if(requestMethod.equalsIgnoreCase(APIv1Constants.HTTP_REQUEST_METHOD_POST)){
					if(esDocument != null)
					{
						//set the that the data has been compressed within the request
						webConnection.setRequestProperty(HTTP_HEADER_CONTENT_ENCODING, HTTP_HEADER_CONTENT_ENCODING_GZIP);
						webConnection.setRequestProperty(HTTP_HEADER_CONTENT_TYPE, HTTP_HEADER_CONTENT_TYPE_JSON);
						webConnection.setDoOutput(true);
						
						//create JSON mapper for serialisation ofESDocument into JSON and compress with GZIP
						ObjectMapper jsonObjectMapper = new ObjectMapper();

						//dont serialize properties that contain default values
						jsonObjectMapper.setSerializationInclusion(JsonInclude.Include.NON_DEFAULT);
						
						//serialise and compress the ESDocument
						GZIPOutputStream gzipOutputStream = new GZIPOutputStream(webConnection.getOutputStream());
						jsonObjectMapper.writeValue(gzipOutputStream, esDocument);
						gzipOutputStream.flush();
					}else{
						//add post body text to request
						webConnection.setDoOutput(true);
						webConnection.setRequestProperty("Content-Length", String.valueOf(postData.length()));
						httpRequestContentWriter = new OutputStreamWriter(webConnection.getOutputStream());
						httpRequestContentWriter.write(postData);
						httpRequestContentWriter.flush();
					}
				}
				
				//send HTTP request
				webConnection.connect();
				
				//get the output of the HTTP response
				responseCode = webConnection.getResponseCode();
				if(responseCode == HttpURLConnection.HTTP_OK)
				{	
					//obtain the encoding returned by the server
					String encoding = webConnection.getContentEncoding();
					
					//The Content-Type can be used later to determine the nature of the content regardless of compression
					String contentType = webConnection.getContentType();
					
					//get the body of the response based from the encoding type
					InputStream resultingInputStream = null;
					if (encoding != null && encoding.equalsIgnoreCase(HTTP_HEADER_CONTENT_ENCODING_GZIP)) {
						resultingInputStream = new GZIPInputStream((InputStream)webConnection.getContent());
					}
					else if (encoding != null && encoding.equalsIgnoreCase("deflate")) {
						resultingInputStream = new InflaterInputStream((InputStream)webConnection.getContent(), new Inflater(true));
					}
					else {
						resultingInputStream = (InputStream)webConnection.getContent();
					}
					
					//build a string from the content of the HTTP response
					httpResponseBufferReader  = new BufferedReader( new InputStreamReader(resultingInputStream));
					builder = new StringBuilder();
					String line = null;
					while ((line = httpResponseBufferReader.readLine()) != null)
					{
						builder.append(line);
					}
					
					//deserialize HTTP response from JSON into the endpoint response object
					endpointResponse.esDocument = endpointJSONReader.readValue(builder.toString());
					endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
					
					//get result status and result code from document
					if(endpointResponse.esDocument != null){
						//get the result status from the esDocument
						if(endpointResponse.esDocument.resultStatus == ESDocumentConstants.RESULT_SUCCESS){
							endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_SUCCESS;
						}
						
						//get the result code from the ESdocument's configs if possible
						if(endpointResponse.esDocument.configs != null){
							endpointResponse.result_code = endpointResponse.esDocument.configs.getOrDefault(APIv1Constants.API_ORG_ENDPOINT_ATTRIBUTE_RESULT_CODE, APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_UNKNOWN);
						}
					}
					
					//get the message that corresponds with the result code
					if(langBundle.containsKey(endpointResponse.result_code)){
						endpointResponse.result_message = langBundle.getString(endpointResponse.result_code);
					}
				}else{
					endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
					endpointResponse.result_code = APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_RESPONSE;
					endpointResponse.result_message = langBundle.getString(endpointResponse.result_code) + "\n" + responseCode;
				}
				
			}catch (MalformedURLException e) {
				endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
				endpointResponse.result_code = APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_MALFORMED_URL;
				endpointResponse.result_message = langBundle.getString(endpointResponse.result_code) + "\n" + e.getLocalizedMessage();
			}catch (ProtocolException e) {
				endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
				endpointResponse.result_code = APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_REQUEST_PROTOCOL;
				endpointResponse.result_message = langBundle.getString(endpointResponse.result_code) + "\n" + e.getLocalizedMessage();
			}catch(ConnectException e){
				endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
				endpointResponse.result_code = APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_CONNECTION;
				endpointResponse.result_message = langBundle.getString(endpointResponse.result_code) + "\n" + e.getLocalizedMessage();
			}catch (IOException e) {
				endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
				endpointResponse.result_code = APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_IO;
				endpointResponse.result_message = langBundle.getString(endpointResponse.result_code) + "\n" + e.getLocalizedMessage();
			}
			catch (Exception e){
				endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
				endpointResponse.result_code = APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
				endpointResponse.result_message = langBundle.getString(endpointResponse.result_code) + "\n" + e.getLocalizedMessage();
			}
			
			finally
			{
				//close the connection, set all objects to null
				if(webConnection != null){
					webConnection.disconnect();
				}
				httpResponseBufferReader = null;
				builder = null;
				httpRequestContentWriter = null;
				webConnection = null;
			}
			
			return endpointResponse;
		}*/
		
		public function gzdecode($data) 
		{ 
			return gzinflate(substr($data,10,-8)); 
		} 
	}
?>