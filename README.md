


![alt tag](https://www.squizz.com/ui/resources/images/logos/squizz_logo_mdpi.png)

# SQUIZZ.com Platform API PHP Library

The [SQUIZZ.com](https://www.squizz.com) Platform API PHP Library can be used by PHP applications to access the SQUIZZ.com platform's Application Programming Interface (API), allowing data to be pushed and pulled from the API's endpoints in a clean and elegant way. The kinds of data pushed and pulled from the API using the library can include organisational data such as products, sales orders, purchase orders, customer accounts, supplier accounts, notifications, and other data that the platform supports.

This library removes the need for PHP software developers to write boilerplate code for connecting and accessing the platform's API, allowing PHP software using the platform's API to be writen faster and simpler. The library provides classes and objects that can be directly referenced within a PHP application, making it easy to manipulate data retreived from the platform, or create and send data to platform.

If you are a software developer writing a PHP application then we recommend that you use this library instead of directly calling the platform's APIs, since it will simplify your development times and allow you to easily incorporate new functionality from the API by simply updating this library.

- You can find more information about the SQUIZZ.com platform by visiting [https://www.squizz.com/docs/squizz](https://www.squizz.com/docs/squizz)
- To find more information about developing software for the SQUIZZ.com visit [https://www.squizz.com/docs/squizz/Integrate-Software-Into-SQUIZZ.com-Platform.html](https://www.squizz.com/docs/squizz/Integrate-Software-Into-SQUIZZ.com-Platform.html)
- To find more information about the platform's API visit [https://www.squizz.com/docs/squizz/Platform-API.html](https://www.squizz.com/docs/squizz/Platform-API.html)

## Getting Started

To get started using the library within PHP applications, clone or download the PHP API library and its dependent libraries from the [Release page](https://github.com/squizzdotcom/squizz-platform-api-php-library/releases) and add references to the PHP libraries in your application using your most preferred way.
The library contains dependencies on the CURL application which for debian linux based operating systems can be downloaded using a package for PHP5 apt-get install php5-curl, or for later version of PHP installing apt-get install php-curl.
The library also contains a dependency on the [Ecommerce Standards Documents PHP Library](https://github.com/squizzdotcom/ecommerce-standards-documents-php-library)

## Create Organisation API Session Endpoint
To start using the SQUIZZ.com platform's API a session must first be created. A session can only be created after credentials for a specified organisation have been given to the API and have been verified.
Once the session has been created then all other endpoints in the API can be called.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section840](https://www.squizz.com/docs/squizz/Platform-API.html#section840) for more documentation about the endpoint.

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "org\\squizz\\api\\v1";
		$esdNamespace = "org\\esd\\EcommerceStandardsDocuments";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = '/opt/squizz/esd-php-library/src/' . $fileName;
		}
		
		require $fileName;
	});
	
	use org\squizz\api\v1\endpoint\APIv1EndpointResponse;
	use org\squizz\api\v1\APIv1OrgSession;
	use org\squizz\api\v1\APIv1Constants;

	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$sessionTimeoutMilliseconds = 20000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
		//session has been created so now can call other API endpoints
		$result = "SUCCESS";
		$resultMessage = "API session has successfully been created.";
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//next steps
	//call API endpoints...
	//destroy API session when done...
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

## Validate Organisation API Session Endpoint

After a session has been created with SQUIZZ.com platform's API, if the same session is persistently being used over a long period time, then its worth validating that the session has not been destroyed by the API.
The SQUIZZ.com platform's API will automatically expire and destory sessions that have existed for a long period of time.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section842](https://www.squizz.com/docs/squizz/Platform-API.html#section842) for more documentation about the endpoint.

```php
<?php
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "org\\squizz\\api\\v1";
		$esdNamespace = "org\\esd\\EcommerceStandardsDocuments";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = '/opt/squizz/esd-php-library/src/' . $fileName;
		}
		
		require $fileName;
	});
	
	use org\squizz\api\v1\endpoint\APIv1EndpointResponse;
	use org\squizz\api\v1\APIv1OrgSession;
	use org\squizz\api\v1\APIv1Constants;
	
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$sessionTimeoutMilliseconds = 20000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//validate the session in the platform's API
	$endpointResponse = $apiOrgSession->validateOrgSession();
	
	//check the result of validating the session
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
		$result = "SUCCESS";
		$resultMessage = "API session has successfully been validated.";
	}else{
		//session failed to be validated
		$resultMessage = "API session failed to be validated. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//next steps
	//call other API endpoints...
	//destroy api session when done
	$apiOrgSession->destroyOrgSession();
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

## Validate/Create Organisation API Session Endpoint

After a session has been created with SQUIZZ.com platform's API, if the same session is persistently being used over a long period time, then a helper method in the library can be used to check if the API session is still valid, then if not have a new session be created.
The SQUIZZ.com platform's API will automatically expire and destory sessions that have existed for a long period of time.

```php
<?php
	/**
	* Copyright (C) 2017 Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "org\\squizz\\api\\v1";
		$esdNamespace = "org\\esd\\EcommerceStandardsDocuments";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = '/opt/squizz/esd-php-library/src/' . $fileName;
		}
		
		require $fileName;
	});
	
	use org\squizz\api\v1\endpoint\APIv1EndpointResponse;
	use org\squizz\api\v1\APIv1OrgSession;
	use org\squizz\api\v1\APIv1Constants;
	
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$sessionTimeoutMilliseconds = 20000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//check if the session still is valid, if not have a new session created with the same organisation API credentials
	$endpointResponse = $apiOrgSession->validateCreateOrgSession();
	
	//check the result of validating or creating a new session
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
		$result = "SUCCESS";
		$resultMessage = "API session has successfully been validated.";
	}else{
		//session failed to be validated
		$resultMessage = "API session failed to be validated. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//next steps
	//call other API endpoints...
	//destroy API session when done...
	$apiOrgSession->destroyOrgSession();
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```

## Destroy Organisation API Session Endpoint

After a session has been created with SQUIZZ.com platform's API, if after calling other endpoints there no need for the session anymore, then it's advisable to destroy the session as soon as possible.
Read [https://www.squizz.com/docs/squizz/Platform-API.html#section841](https://www.squizz.com/docs/squizz/Platform-API.html#section841) for more documentation about the endpoint.

```php
<?php
	/**
	* Copyright (C) 2017 Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	
	//set automatic loader of the library's classes
	spl_autoload_register(function($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		$apiNamespace = "org\\squizz\\api\\v1";
		$esdNamespace = "org\\esd\\EcommerceStandardsDocuments";
		
		//set absolute path to API php class files
		if(substr($namespace, 0, strlen($apiNamespace)) === $apiNamespace){
			$fileName = $_SERVER['DOCUMENT_ROOT']. '/src/' . $fileName;
		}
		//set absolute path to ESD library files
		else if(substr($namespace, 0, strlen($esdNamespace)) === $esdNamespace){
			$fileName = '/opt/squizz/esd-php-library/src/' . $fileName;
		}
		
		require $fileName;
	});
	
	use org\squizz\api\v1\endpoint\APIv1EndpointResponse;
	use org\squizz\api\v1\APIv1OrgSession;
	use org\squizz\api\v1\APIv1Constants;
	
	
	//obtain or load in an organisation's API credentials, in this example from command line arguments
	$orgID = $_GET["orgID"];
	$orgAPIKey = $_GET["orgAPIKey"];
	$orgAPIPass = $_GET["orgAPIPass"];
	$sessionTimeoutMilliseconds = 20000;
	
	echo "<div>Making a request to the SQUIZZ.com API</div><br/>";
	
	//create an API session instance
	$apiOrgSession = new APIv1OrgSession($orgID, $orgAPIKey, $orgAPIPass, $sessionTimeoutMilliseconds, APIv1Constants::SUPPORTED_LOCALES_EN_AU);
	
	//call the platform's API to request that a session is created
	$endpointResponse = $apiOrgSession->createOrgSession();
	
	//check if the organisation's credentials were correct and that a session was created in the platform's API
	$result = "FAIL";
	$resultMessage = "";
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS)
	{
	}
	else
	{
		//session failed to be created
		$resultMessage = "API session failed to be created. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	//next steps
	//call API endpoints...
	
	//destroy the session in the platform's API
	$endpointResponse = $apiOrgSession->destroyOrgSession();
	
	//check the result of destroying the session
	if($endpointResponse->result == APIv1EndpointResponse::ENDPOINT_RESULT_SUCCESS){
		$result = "SUCCESS";
		$resultMessage = "API session has successfully been destroyed.";
	}else{
		//session failed to be created
		$resultMessage = "API session failed to be destroyed. Reason: " . $endpointResponse->result_message  . " Error Code: " . $endpointResponse->result_code;
	}
	
	echo "<div>Result:<div>";
	echo "<div><b>$result</b><div><br/>";
	echo "<div>Message:<div>";
	echo "<div><b>$resultMessage</b><div><br/>";
?>
```
