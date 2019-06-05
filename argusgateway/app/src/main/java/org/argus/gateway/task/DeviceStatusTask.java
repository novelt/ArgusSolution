
package org.argus.gateway.task;

import org.apache.http.HttpResponse;
import org.apache.http.message.BasicNameValuePair;
import org.argus.gateway.App;

public class DeviceStatusTask extends HttpTask {

    public DeviceStatusTask(App app) {
        super(app, new BasicNameValuePair("action", App.ACTION_DEVICE_STATUS));
    }

    @Override
    protected void handleResponse(HttpResponse response) throws Exception
    {
        app.log("Device status sent");
    }
}