
package org.argus.gateway.task;

import org.argus.gateway.App;

import org.apache.http.message.BasicNameValuePair;

public class PollerTask extends HttpTask {

    public PollerTask(App app) {
        super(app, new BasicNameValuePair("action", App.ACTION_OUTGOING));
    }

    @Override
    protected void onPostExecute(Boolean result) {
        super.onPostExecute(result);
        app.markPollComplete();
    }
    
    @Override
    protected void handleUnknownContentType(String contentType)
            throws Exception
    {
        throw new Exception("Invalid response type " + contentType);
    }
}