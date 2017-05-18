package org.squizz.api.v1.endpoint;

/**
* Copyright (C) 2017 Squizz PTY LTD
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
*/

import com.fasterxml.jackson.databind.DeserializationFeature;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.ObjectReader;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.ResourceBundle;
import javafx.util.Pair;
import org.squizz.api.v1.APIv1Constants;
import org.squizz.api.v1.APIv1HTTPRequest;
import org.squizz.api.v1.APIv1OrgSession;
import org.esd.EcommerceStandardsDocuments.*;
import org.squizz.api.v1.endpoint.APIv1EndpointResponse;
import org.squizz.api.v1.endpoint.APIv1EndpointResponseESD;

/**
 * Class handles calling the SQUIZZ.com API endpoint to get different kinds of organisational data from a connected organisation in the platform such as products, stock quantities, and other data types. See the full list at https://www.squizz.com/docs/squizz/Platform-API.html#section843
 * The data being retrieved is wrapped up in a Ecommerce Standards Document (ESD) that contains records storing data of a particular type
 */
public class APIv1EndpointOrgRetrieveESDocument
{
    public static final int RETRIEVE_TYPE_ID_PRODUCTS = 3;
    public static final int RETRIEVE_TYPE_ID_PRICING = 37;
    public static final int RETRIEVE_TYPE_ID_PRODUCT_STOCK = 10;
    
    /**
     * Calls the platform's API endpoint and gets organisation data in a Ecommerce Standards Document of a specified type
     * @param apiOrgSession existing organisation API session
     * @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
     * @param retrieveTypeID ID of the type of data to retrieve
     * @param supplierOrgID unique ID of the supplier organisation in the SQUIZZ.com platform to obtain data from
     * @param customerAccountCode code of the supplier organisation's customer account. Customer account only needs to be set if the supplier organisation has assigned multiple accounts to the organisation logged into the API session (customer org) and account specific data is being obtained
     * @return response from calling the API endpoint
     */
    public static APIv1EndpointResponseESD call(APIv1OrgSession apiOrgSession, int endpointTimeoutMilliseconds, int retrieveTypeID, String supplierOrgID, String customerAccountCode)
    {
        ArrayList<Pair<String, String>> requestHeaders = new ArrayList<>();
        APIv1EndpointResponseESD endpointResponse = new APIv1EndpointResponseESD();
        ObjectReader endpointJSONReader = null;
        boolean callEndpoint = true;
        
        try{
            //set endpoint parameters
            String endpointParams = "data_type_id="+retrieveTypeID + "&supplier_org_id=" + URLEncoder.encode(supplierOrgID, StandardCharsets.UTF_8.name()) + "&customer_account_code="+URLEncoder.encode(customerAccountCode, StandardCharsets.UTF_8.name());
            
            //create JSON deserializer to interpret the response from the endpoint
            ObjectMapper jsonMapper = new ObjectMapper();
            jsonMapper.configure(DeserializationFeature.FAIL_ON_UNKNOWN_PROPERTIES, false);
            
            //set the class to use to deserialise the ecommerce standards documents that has been returned from the platform's API
            switch(retrieveTypeID){
                case RETRIEVE_TYPE_ID_PRODUCTS:
                    endpointJSONReader = jsonMapper.readerFor(ESDocumentProduct.class);
                    break;
                case RETRIEVE_TYPE_ID_PRICING:
                    endpointJSONReader = jsonMapper.readerFor(ESDocumentPrice.class);
                    break;
                case RETRIEVE_TYPE_ID_PRODUCT_STOCK:
                    endpointJSONReader = jsonMapper.readerFor(ESDocumentStockQuantity.class);
                    break;
                default:
                    callEndpoint  = false;
                    endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
                    endpointResponse.result_code = APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_INCORRECT_DATA_TYPE;
                    break;
            }
            
            //make a HTTP request to the platform's API endpoint to retrieve the specified organisation data contained in the Ecommerce Standards Document
            if(callEndpoint && endpointJSONReader != null)
            {
                endpointResponse = APIv1HTTPRequest.sendESDocumentHTTPRequest(APIv1Constants.HTTP_REQUEST_METHOD_GET, APIv1Constants.API_ORG_ENDPOINT_RETRIEVE_ESD+APIv1Constants.API_PATH_SLASH+apiOrgSession.getSessionID(), endpointParams, requestHeaders, "", null, endpointTimeoutMilliseconds, apiOrgSession.getLangBundle(), endpointJSONReader, endpointResponse);

                //check that the data was successfully pushed up
                if(!endpointResponse.result.equalsIgnoreCase(APIv1EndpointResponse.ENDPOINT_RESULT_SUCCESS))
                {
                    //check if the session still exists
                    if(endpointResponse.result.equalsIgnoreCase(APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_SESSION_INVALID)){
                        //mark that the session has expired
                        apiOrgSession.markSessionExpired();
                    }
                }
            }
        }
        catch(Exception ex)
        {
            endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
            endpointResponse.result_code = APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
			endpointResponse.result_message = apiOrgSession.getLangBundle().getString(endpointResponse.result_code) + "\n" + ex.getLocalizedMessage();
        }
        
        return endpointResponse;
    }
}
