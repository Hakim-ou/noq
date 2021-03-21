package com.example.noubty;

import android.content.Context;
import android.os.AsyncTask;
import android.widget.Toast;

import com.squareup.okhttp.MediaType;
import com.squareup.okhttp.OkHttpClient;
import com.squareup.okhttp.Request;
import com.squareup.okhttp.RequestBody;
import com.squareup.okhttp.Response;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

public class GetServices extends AsyncTask<Integer, Void, JSONArray> {


    Context context;

    protected GetServices(Context context) {
        this.context = context.getApplicationContext();
    }
    @Override
    protected JSONArray doInBackground(Integer... ints) {
        String response;
        JSONArray services = null;
        String info = null;
        try {
            String  url= context.getResources().getString(R.string.apiUrl);
            response = post( url ,"{\"function\":4 , \"event_id\":" + String.valueOf(ints[0]) + "}");
            JSONObject answer = null;
            answer = new JSONObject(response);
            int  error = answer.getInt("error");
            if(error == 0) {
                services = answer.getJSONArray("services");
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();

        }

        return services ;
    }
    public static final MediaType JSON
            = MediaType.parse("application/json; charset=utf-8");

    OkHttpClient client = new OkHttpClient();
    String post(String url, String json) throws IOException {
        RequestBody body = RequestBody.create(JSON, json);
        Request request = new Request.Builder()
                .url(url)
                .post(body)
                .build();
        Response response = client.newCall(request).execute();
        return response.body().string();
    }

    protected void onPostExecute(JSONArray services) {
        super.onPostExecute(services);
        if(services == null) {
            Toast.makeText(this.context, "unknown error", Toast.LENGTH_LONG).show();
        }else{
            if(services.length() == 1) {
                try {
                    int service_id = services.getJSONObject(0).getInt("id");
                    new TakeTurn(context, "feature not treated yet ").execute(service_id);//TODO
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }else{
                Toast.makeText(this.context, "so many", Toast.LENGTH_LONG).show();
                // Intent intent = new Intent(context, event.class);
                // intent.putExtra("services", services);
                // intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                // context.startActivity(intent);

            }
        }
    }

}
