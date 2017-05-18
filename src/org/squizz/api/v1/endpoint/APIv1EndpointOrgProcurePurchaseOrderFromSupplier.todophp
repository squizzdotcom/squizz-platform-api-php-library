/**
* Copyright (C) 2017 Squizz PTY LTD
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
*/
package org.squizz.api.v1.endpoint;

import org.esd.EcommerceStandardsDocuments.ESDocument;
import org.esd.EcommerceStandardsDocuments.ESDocumentOrderPurchase;
import org.esd.EcommerceStandardsDocuments.ESDocumentOrderSale;
import com.fasterxml.jackson.databind.DeserializationFeature;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.ObjectReader;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import javafx.util.Pair;
import org.squizz.api.v1.APIv1Constants;
import org.squizz.api.v1.APIv1HTTPRequest;
import org.squizz.api.v1.APIv1OrgSession;

/**
 * Class handles calling the SQUIZZ.com API endpoint to send one more of an organisation's purchase orders into the platform, where they are then converted into sales orders and sent to a supplier organisation for processing and dispatch.
 * This endpoint allows goods and services to be purchased by the "customer" organisation logged into the API session from their chosen supplier organisation
 */
public class APIv1EndpointOrgProcurePurchaseOrderFromSupplier 
{
    /**
     * Calls the platform's API endpoint and pushes up and import organisation data in a Ecommerce Standards Document of a specified type
     * @param apiOrgSession existing organisation API session
     * @param endpointTimeoutMilliseconds amount of milliseconds to wait after calling the the API before giving up, set a positive number
     * @param supplierOrgID unique ID of the supplier organisation in the SQUIZZ.com platform
     * @param customerAccountCode code of the supplier organisation's customer account. Customer account only needs to be set if the supplier organisation has assigned multiple accounts to the organisation logged into the API session (customer org)
     * @param esDocumentOrderPurchase Purchase Order Ecommerce Standards Document that contains one or more purchase order records
     * @return response from calling the API endpoint
     */
    public static APIv1EndpointResponseESD call(APIv1OrgSession apiOrgSession, int endpointTimeoutMilliseconds, String supplierOrgID, String customerAccountCode, ESDocumentOrderPurchase esDocumentOrderPurchase)
    {
        ArrayList<Pair<String, String>> requestHeaders = new ArrayList<>();
        APIv1EndpointResponseESD endpointResponse = new APIv1EndpointResponseESD();
        
        try{
            //set notification parameters
            String endpointParams = "supplier_org_id="+ URLEncoder.encode(supplierOrgID, StandardCharsets.UTF_8.name()) + "&customer_account_code="+URLEncoder.encode(customerAccountCode, StandardCharsets.UTF_8.name());
            
            //create JSON deserializer to interpret the response from the endpoint
            ObjectMapper jsonMapper = new ObjectMapper();
            jsonMapper.configure(DeserializationFeature.FAIL_ON_UNKNOWN_PROPERTIES, false);
            ObjectReader endpointJSONReader = jsonMapper.readerFor(ESDocumentOrderSale.class);
            
            //make a HTTP request to the platform's API endpoint to send the ESD containing the purchase orders
            endpointResponse = APIv1HTTPRequest.sendESDocumentHTTPRequest(APIv1Constants.HTTP_REQUEST_METHOD_POST, APIv1Constants.API_ORG_ENDPOINT_PROCURE_PURCHASE_ORDER_FROM_SUPPLIER+APIv1Constants.API_PATH_SLASH+apiOrgSession.getSessionID(), endpointParams, requestHeaders, "", esDocumentOrderPurchase, endpointTimeoutMilliseconds, apiOrgSession.getLangBundle(), endpointJSONReader, endpointResponse);
            
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
    
    /**
     * gets a list of order indexes that contain order lines that could not be mapped to a supplier organisation's products
     * @param esDocument Ecommerce standards document containing configuration that specifies unmapped order lines
     * @return an array containing pairs. Each pair has the index of the order, and the index of the order line that could not be mapped
     */
    public static ArrayList<Pair<Integer, Integer>> getUnmappedOrderLines(ESDocument esDocument)
    {
        ArrayList<Pair<Integer, Integer>> upmappedOrderLines = new ArrayList<>();
        
        //check that the ecommerce standards document's configs contains a key specifying the unmapped order lines
        if(esDocument.configs.containsKey(APIv1EndpointResponseESD.ESD_CONFIG_ORDERS_WITH_UNMAPPED_LINES))
        {
            //get comma separated list of order record indicies and line indicies that indicate the unmapped order lines
            String unmappedOrderLineCSV = esDocument.configs.get(APIv1EndpointResponseESD.ESD_CONFIG_ORDERS_WITH_UNMAPPED_LINES);

            //get the index of the order record and line that contained the unmapped product
            if(!unmappedOrderLineCSV.trim().isEmpty()){
                String[] unmappedOrderLineIndices = unmappedOrderLineCSV.trim().split(",");

                //iterate through each order-line index
                for(int i=0; i < unmappedOrderLineIndices.length; i++){
                    //get order index and line index
                    String[] orderLineIndex = unmappedOrderLineIndices[i].split(":");
                    if(orderLineIndex.length == 2){
                        try{
                            int orderIndex = Integer.parseInt(orderLineIndex[0]);
                            int lineIndex = Integer.parseInt(orderLineIndex[1]);
                            Pair<Integer, Integer> orderLinePair = new Pair<>(orderIndex, lineIndex);
                            upmappedOrderLines.add(orderLinePair);

                        }catch(Exception ex){
                        }
                    }
                }
            }
        }
        
        return upmappedOrderLines;
    }
    
    /**
     * gets a list of order indexes that contain order lines that could not be priced for a supplier organisation's products
     * @param esDocument Ecommerce standards document containing configuration that specifies unpriced order lines
     * @return an array containing pairs. Each pair has the index of the order, and the index of the order line that could not be priced
     */
    public static ArrayList<Pair<Integer, Integer>> getUnpricedOrderLines(ESDocument esDocument)
    {
        ArrayList<Pair<Integer, Integer>> unpricedOrderLines = new ArrayList<>();
        
        //check that the ecommerce standards document's configs contains a key specifying the unpriced order lines
        if(esDocument.configs.containsKey(APIv1EndpointResponseESD.ESD_CONFIG_ORDERS_WITH_UNPRICED_LINES))
        {
            //get comma separated list of order record indicies and line indicies that indicate the unpriced order lines
            String unpricedOrderLineCSV = esDocument.configs.get(APIv1EndpointResponseESD.ESD_CONFIG_ORDERS_WITH_UNPRICED_LINES);

            //get the index of the order record and line that contained the unpriced product
            if(!unpricedOrderLineCSV.trim().isEmpty()){
                String[] unmappedOrderLineIndices = unpricedOrderLineCSV.trim().split(",");

                //iterate through each order-line index
                for(int i=0; i < unmappedOrderLineIndices.length; i++){
                    //get order index and line index
                    String[] orderLineIndex = unmappedOrderLineIndices[i].split(":");
                    if(orderLineIndex.length == 2){
                        try{
                            int orderIndex = Integer.parseInt(orderLineIndex[0]);
                            int lineIndex = Integer.parseInt(orderLineIndex[1]);
                            Pair<Integer, Integer> orderLinePair = new Pair<>(orderIndex, lineIndex);
                            unpricedOrderLines.add(orderLinePair);

                        }catch(Exception ex){
                        }
                    }
                }
            }
        }
        
        return unpricedOrderLines;
    }
}
