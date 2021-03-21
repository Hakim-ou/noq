package com.lib;

import android.content.Context;
import android.content.Intent;
import android.util.Log;

import com.example.noubty.NoConnection;
import com.squareup.okhttp.MediaType;
import com.squareup.okhttp.OkHttpClient;
import com.squareup.okhttp.Request;
import com.squareup.okhttp.RequestBody;
import com.squareup.okhttp.Response;

import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

public class APIRequest {
    //static final String url = "http://192.168.1.115/";
    private static String url = "http://192.168.1.104:8080/";

    private static final MediaType JSON = MediaType.parse("application/json; charset=utf-8");
    private static final OkHttpClient client = new OkHttpClient();
    private final int functionCode;
    private final Map<String, Object> params = new HashMap<>();

    public APIRequest(int functionCode) {
        this.functionCode = functionCode;

    }

    public void putExtra(String key, Object value) {
        this.params.put(key, value);
    }

    public void putExtra(String key, int value) {
        this.params.put(key, new Integer(value));
    }


    public String execute() throws IOException {

        final Response[] response = new Response[1];
        Thread thread = new Thread() {
            @Override
            public void run() {

                StringBuilder jsonBuilder = new StringBuilder("{");
                for (Map.Entry<String, Object> entry : params.entrySet()) {
                    if (entry.getValue() instanceof String) {
                        jsonBuilder.append("\"").append(entry.getKey()).append("\":\"").append(entry.getValue()).append("\", ");
                    } else {
                        jsonBuilder.append("\"").append(entry.getKey()).append("\":").append(entry.getValue()).append(", ");
                    }
                }
                String json = jsonBuilder.toString() + ("\"function\":" + functionCode + "}");
                Log.d("json ask", json);
                try {
                    RequestBody body = RequestBody.create(JSON, json);
                    Request request = new Request.Builder()
                            .url(url)
                            .post(body)
                            .build();
                    response[0] = client.newCall(request).execute();

                } catch (IOException e) {
                    e.printStackTrace();
                }
            }
        };
        thread.start();
        try {
            thread.join();
        } catch (InterruptedException e) {
            e.printStackTrace();
        }

        String answer = response[0].body().string();
        Log.d("reqresp", answer);
        return answer;
    }

    public static boolean isConnected() throws InterruptedException, IOException {
        String command1 = "ping -i 5 -c 1 google.com";
        String command2 = "ping -i 5 -c 1 facebook.com";
        String ip = url.substring(7, 21);
        Log.d("ping", ip);
        String command = "ping -i 5 -c 1 " + ip;
        //return (Runtime.getRuntime().exec(command).waitFor() == 0);
                /*
                || Runtime.getRuntime().exec(command1).waitFor() == 0
                || Runtime.getRuntime().exec(command2).waitFor() == 0);
                 */
        return true;

    }
}
