package org.argus.gateway.task;

import android.os.AsyncTask;
import android.os.Build;

import org.apache.http.HttpEntity;
import org.argus.gateway.App;
import org.argus.gateway.JsonUtils;
import org.argus.gateway.XmlUtils;
import java.io.IOException;
import java.nio.charset.Charset;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import org.apache.http.Header;
import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.mime.FormBodyPart;
import org.apache.http.entity.mime.MultipartEntity;
import org.apache.http.entity.mime.content.StringBody;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONObject;
import org.w3c.dom.Document;

public class BaseHttpTask extends AsyncTask<String, Void, Boolean> {
       
    protected App app;
    protected String url;    
    protected List<BasicNameValuePair> params = new ArrayList<BasicNameValuePair>();    

    private List<FormBodyPart> formParts;
    protected boolean useMultipartPost = false;    
    protected HttpPost post;
    protected Throwable requestException;
    
    public BaseHttpTask(App app, String url, BasicNameValuePair... paramsArr)
    {
        this.url = url;
        this.app = app;                
        params = new ArrayList<BasicNameValuePair>(Arrays.asList(paramsArr));
        
        params.add(new BasicNameValuePair("version", "" + app.getPackageInfo().versionCode));
    }
    
    public void addParam(String name, String value)
    {
        params.add(new BasicNameValuePair(name, value));
    }    
    
    public void setFormParts(List<FormBodyPart> formParts)
    {
        useMultipartPost = true;
        this.formParts = formParts;
    }                     

    protected HttpPost makeHttpPost() throws Exception
    {
        HttpPost httpPost = new HttpPost(url);
                
        httpPost.setHeader("User-Agent", app.getText(org.argus.gateway.R.string.app_name) + "/" + app.getPackageInfo().versionName + " (Android; SDK "+Build.VERSION.SDK_INT + "; " + Build.MANUFACTURER + "; " + Build.MODEL+")");

        if (useMultipartPost)
        {
            MultipartEntity entity = new MultipartEntity();//HttpMultipartMode.BROWSER_COMPATIBLE);

            Charset charset = Charset.forName("UTF-8");

            for (BasicNameValuePair param : params)
            {
                entity.addPart(param.getName(), new StringBody(param.getValue(), charset));
            }

            for (FormBodyPart formPart : formParts)
            {
                entity.addPart(formPart);
            }
            httpPost.setEntity(entity);                                                
        }
        else
        {
            httpPost.setEntity(new UrlEncodedFormEntity(params, "UTF-8"));
        }        
        
        return httpPost;
    }
    
    protected Boolean doInBackground(String... ignored)
    {    
        try
        {
            post = makeHttpPost();
            
            HttpClient client = app.getHttpClient();
            HttpResponse response =  client.execute(post);
            return handleHttpResponse(response);
        }     
        catch (Throwable ex) 
        {
            requestException = ex;
            
            try
            {
                String message = ex.getMessage();
                HttpResponse response = null ;
                // workaround for https://issues.apache.org/jira/browse/HTTPCLIENT-881
                if ((ex instanceof IOException) 
                        && message != null && message.equals("Connection already shutdown"))
                {
                    // app.log("Retrying request");
                    post = makeHttpPost();
                    HttpClient client = app.getHttpClient();

                    response = client.execute(post);
                }

                return handleHttpResponse(response);
            }
            catch (Throwable ex2)
            {
                requestException = ex2;
            }            
        }   
        
        return false;
    }    
           
    protected String getErrorText(HttpResponse response)    
            throws Exception
    {
        String contentType = getContentType(response);
        String error = null;
        
        if (contentType.startsWith("application/json"))
        {
            JSONObject json = JsonUtils.parseResponse(response);
            error = JsonUtils.getErrorText(json);
        }
        else if (contentType.startsWith("text/xml"))
        {
            Document xml = XmlUtils.parseResponse(response);
            error = XmlUtils.getErrorText(xml);
        }

        if (error == null)
        {
            error = "HTTP " + response.getStatusLine().getStatusCode() + " " + response.getStatusLine().getReasonPhrase() ;
        }
        return error;
    }
    
    protected String getContentType(HttpResponse response)
    {
        Header contentTypeHeader = response.getFirstHeader("Content-Type");
        return (contentTypeHeader != null) ? contentTypeHeader.getValue() : "";
    }

    private Boolean handleHttpResponse(HttpResponse response)
    {
        Boolean result = false;

        if (response != null)
        {
            try
            {
                int statusCode = response.getStatusLine().getStatusCode();

                if (statusCode == 200)
                {
                    handleResponse(response);
                    result = true;
                }
                else if (statusCode >= 400 && statusCode <= 499)
                {
                    handleErrorResponse(response);
                    handleFailure();
                }
                else
                {
                    throw new Exception("HTTP " + statusCode);
                }
            }
            catch (Throwable ex)
            {
                post.abort();
                handleResponseException(ex);
                handleFailure();
            }

            try
            {
                HttpEntity entity = response.getEntity();
                if (entity != null) {
                    entity.consumeContent();
                }

            }
            catch (Exception ex)
            {
                handleResponseException(ex);
            }
        }
        else
        {
            handleRequestException(requestException);
            handleFailure();
        }

        return result;
    }
    
    @Override
    protected void onPostExecute(Boolean result)
    {
    }
    
    protected void handleResponse(HttpResponse response) throws Exception
    {
    }
    
    protected void handleErrorResponse(HttpResponse response) throws Exception
    {
    }        
    
    protected void handleFailure()
    {
    }            

    protected void handleRequestException(Throwable ex)
    {       
    }

    protected void handleResponseException(Throwable ex)
    {       
    }    
        
}