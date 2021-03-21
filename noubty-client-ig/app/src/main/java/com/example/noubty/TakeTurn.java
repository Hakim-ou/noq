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

public class TakeTurn extends AsyncTask<Integer, Void, Boolean> {


    Context context;
    String additionalInfo;

    protected TakeTurn(Context context, String additionalInfo) {
        this.context = context.getApplicationContext();
        this.additionalInfo = additionalInfo;
    }
    @Override
    protected Boolean doInBackground(Integer... ints) {
        String response;
        boolean done = false;
        try {
            String  url= context.getResources().getString(R.string.apiUrl);
            response = post( url ,"{\"function\":21 , \"service_id\":" + String.valueOf(ints[0]) + ",\"additional_information\":\""+ this.additionalInfo+"\"}");
            JSONObject answer = null;
            answer = new JSONObject(response);
            int  error = answer.getInt("error");
            if(error == 0) {
                done = true;
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();

        }

        return done ;
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

    protected void onPostExecute(Boolean done) {
        super.onPostExecute(done);
        if(done) {
            Toast.makeText(this.context, "successful", Toast.LENGTH_LONG).show();
        }else{
                Toast.makeText(this.context, "error", Toast.LENGTH_LONG).show();
                // Intent intent = new Intent(context, event.class);
                // intent.putExtra("services", services);
                // intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                // context.startActivity(intent);

        }
    }

}
