/**
* Copyright (C) 2017 Squizz PTY LTD
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
*/
package org.squizz.api.v1.endpoint;

import com.fasterxml.jackson.databind.DeserializationFeature;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.ObjectReader;
import java.util.ArrayList;
import javafx.util.Pair;
import org.squizz.api.v1.APIv1Constants;
import org.squizz.api.v1.APIv1HTTPRequest;
import org.squizz.api.v1.APIv1OrgSession;
import org.esd.EcommerceStandardsDocuments.*;

/**
 * Class handles calling the SQUIZZ.com API endpoint to push and import different kinds of organisational data into the platform such as products, customer accounts, and many other data types. See the full list at https://www.squizz.com/docs/squizz/Platform-API.html#section843
 * The data being pushed must be wrapped up in a Ecommerce Standards Document (ESD) that contains records storing data of a particular type
 */
public class APIv1EndpointOrgImportESDocument
{
    public static final int IMPORT_TYPE_ID_TAXCODES = 1;
    public static final int IMPORT_TYPE_ID_PRICE_LEVELS = 2;
    public static final int IMPORT_TYPE_ID_PRODUCTS = 3;
    public static final int IMPORT_TYPE_ID_PRODUCT_PRICE_LEVEL_UNIT_PRICING = 4;
    public static final int IMPORT_TYPE_ID_PRODUCT_PRICE_LEVEL_QUANTITY_PRICING = 6;
    public static final int IMPORT_TYPE_ID_PRODUCT_CUSTOMER_ACCOUNT_PRICING = 7;
    public static final int IMPORT_TYPE_ID_ALTERNATE_CODES = 9;
    public static final int IMPORT_TYPE_ID_PRODUCT_STOCK_QUANTITIES = 10;
    public static final int IMPORT_TYPE_ID_SALES_REPRESENTATIVES = 16;
    public static final int IMPORT_TYPE_ID_CUSTOMER_ACCOUNTS = 17;
    public static final int IMPORT_TYPE_ID_SUPPLIER_ACCOUNTS = 18;
    public static final int IMPORT_TYPE_ID_CUSTOMER_ACCOUNT_CONTRACTS = 19;
    public static final int IMPORT_TYPE_ID_CUSTOMER_ACCOUNT_ADDRESSES = 20;
    public static final int IMPORT_TYPE_ID_LOCATIONS = 23;
    public static final int IMPORT_TYPE_ID_PURCHASERS = 25;
    public static final int IMPORT_TYPE_ID_SURCHARGES = 26;
    public static final int IMPORT_TYPE_ID_PAYMENT_TYPES = 27;
    public static final int IMPORT_TYPE_ID_SELL_UNITS = 28;
    
    /**
     * Calls the platform's API endpoint and pushes up and import organisation data in a Ecommerce Standards Document of a specified type
     * @param apiOrgSession existing organisation API session
     * @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
     * @param importTypeID ID of the of the type of data to import
     * @param esDocument Ecommerce Standards Document that contains records and data to to upload. Ensure the document matches the import type given
     * @return response from calling the API endpoint
     */
    public static APIv1EndpointResponseESD call(APIv1OrgSession apiOrgSession, int endpointTimeoutMilliseconds, int importTypeID, ESDocument esDocument)
    {
        ArrayList<Pair<String, String>> requestHeaders = new ArrayList<>();
        APIv1EndpointResponseESD endpointResponse = new APIv1EndpointResponseESD();
        
        try{
            //set endpoint parameters
            String endpointParams = "import_type_id="+importTypeID;
            
            //create JSON deserializer to interpret the response from the endpoint
            ObjectMapper jsonMapper = new ObjectMapper();
            jsonMapper.configure(DeserializationFeature.FAIL_ON_UNKNOWN_PROPERTIES, false);
            ObjectReader endpointJSONReader = jsonMapper.readerFor(APIv1EndpointResponse.class);
            
            //make a HTTP request to the platform's API endpoint to push the ESDocument data up
            endpointResponse = APIv1HTTPRequest.sendESDocumentHTTPRequest(APIv1Constants.HTTP_REQUEST_METHOD_POST, APIv1Constants.API_ORG_ENDPOINT_IMPORT_ESD+APIv1Constants.API_PATH_SLASH+apiOrgSession.getSessionID(), endpointParams, requestHeaders, "", esDocument, endpointTimeoutMilliseconds, apiOrgSession.getLangBundle(), endpointJSONReader, endpointResponse);
            
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
        catch(Exception ex)
        {
            endpointResponse.result = APIv1EndpointResponse.ENDPOINT_RESULT_FAILURE;
            endpointResponse.result_code = APIv1EndpointResponse.ENDPOINT_RESULT_CODE_ERROR_UNKNOWN;
			endpointResponse.result_message = apiOrgSession.getLangBundle().getString(endpointResponse.result_code) + "\n" + ex.getLocalizedMessage();
        }
        
        return endpointResponse;
    }
}
