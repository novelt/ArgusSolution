package org.argus.gateway.task;

import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.SharedPreferences;
import android.net.DhcpInfo;
import android.net.wifi.WifiManager;
import android.os.AsyncTask;
import android.os.Handler;
import android.preference.PreferenceManager;
import android.util.Log;

import java.io.IOException;
import java.net.DatagramPacket;
import java.net.DatagramSocket;
import java.net.InetAddress;
import java.net.SocketTimeoutException;

public class ServerFinderTask extends AsyncTask<Void, Void, String>  {

    private DatagramSocket  mSocket;
    private Context         mContext;
    private ProgressDialog  mProgressDialog;

    static final int        MSG_DISMISS_DIALOG = 0;

    public ServerFinderTask(Context ctx, ProgressDialog progressDial) {
        mContext = ctx;
        mProgressDialog = progressDial;
    }

    /**
     * Broadcast a request for the server adresse.
     * @return
     */
    protected String getServerIP() {

        Log.d("SES", "Getting server IP");

        String retMsg = "Waiting for server";

        try {

            //Open a socket
            mSocket = new DatagramSocket();

            mSocket.setBroadcast(true);

            byte[] sendData = "DISCOVER_SES_SERVER_REQUEST".getBytes();

            InetAddress broadcastAddr = getBroadcastAddress();

            Log.d("SES", "Sending  packet to: " + broadcastAddr.toString());

            DatagramPacket sendPacket = new DatagramPacket(sendData, sendData.length, broadcastAddr, 9999);

            mSocket.send(sendPacket);

            Log.d("SES", "Request packet sent!");

        } catch (Exception e) {

            Log.e("SES", "Cannot broadcast packet", e);

            retMsg = "Broadcast fail";

        }

        //Wait for a response
        byte[] recvBuf = new byte[15000];

        try {

            DatagramPacket receivePacket = new DatagramPacket(recvBuf, recvBuf.length);

            mSocket.setSoTimeout(15000);

            mSocket.receive(receivePacket);

            //We have a response
            String message = new String(receivePacket.getData()).trim();

            mSocket.close();

            retMsg = receivePacket.getAddress().toString().replace("/", "") + message;

        } catch (SocketTimeoutException e) {

            Log.e("SES", "Timeout", e);

            mSocket.close();

            return null;

        } catch (Exception e) {

            Log.e("SES", "Error when receiving answer", e);

            return null;

        }

        //Close the port!
        mSocket.close();

        return retMsg;

    }

    /**
     * Calculate the broadcast IP we need to send the packet along. If we send it
     * to 255.255.255.255, it never gets sent. I guess this has something to do
     * with the mobile network not wanting to do broadcast.
     */
    private InetAddress getBroadcastAddress() throws IOException {

        WifiManager wifi = (WifiManager) mContext.getSystemService(Context.WIFI_SERVICE);
        DhcpInfo dhcp = wifi.getDhcpInfo();
        // handle null somehow

        int broadcast = (dhcp.ipAddress & dhcp.netmask) | ~dhcp.netmask;
        byte[] quads = new byte[4];
        for (int k = 0; k < 4; k++)
            quads[k] = (byte) (broadcast >> (k * 8));
        return InetAddress.getByAddress(quads);
    }

    @Override
    protected String doInBackground(Void... params) {

        Log.d("SES", "Get IP in background");

        return getServerIP();
    }

    @Override
    protected void onPostExecute(String result) {
        AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
        if (result != null && !result.isEmpty()) {
            SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(mContext);
            SharedPreferences.Editor editor = sp.edit();
            editor.putString("server_url", result);
            editor.commit();
            builder.setMessage("Server url : " + result);
        } else
            builder.setMessage("Server not found");


        final AlertDialog mAlertDialog = builder.create();

        Handler mHandler = new Handler() {
            public void handleMessage(android.os.Message msg) {
                switch (msg.what) {
                    case MSG_DISMISS_DIALOG:
                        if (mAlertDialog != null && mAlertDialog.isShowing()) {
                            mAlertDialog.dismiss();
                        }
                        break;

                    default:
                        break;
                }
            }
        };

        mAlertDialog.show();
        mHandler.sendEmptyMessageDelayed(MSG_DISMISS_DIALOG, 5000);

        mProgressDialog.dismiss();
    }
}