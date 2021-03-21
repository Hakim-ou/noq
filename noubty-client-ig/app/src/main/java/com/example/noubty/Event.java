package com.example.noubty;

import androidx.appcompat.app.AppCompatActivity;

import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

import org.json.JSONException;
import org.json.JSONObject;

public class Event extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_event);
        TextView titleView = findViewById(R.id.title);
        TextView descriptionView = findViewById(R.id.description);
        TextView scheduleView = findViewById(R.id.schedule);
        Button takeTurnButton = findViewById(R.id.takeTurn);
        String title = null;
        String description = null;
        String schedule = null;
        int id=1;

        String info = getIntent().getStringExtra("info");
        try {

            JSONObject entry= new JSONObject(info);
            JSONObject information = entry.getJSONObject("info");
            title = information.getString("title");
            description = information.getString("description");
            schedule = information.getString("schedule");
            id = information.getInt("id");
        } catch (JSONException e) {
            e.printStackTrace();
        }
        titleView.setText(title);
        descriptionView.setText(description);
        scheduleView.setText(schedule);
        final int finalId = id;
        takeTurnButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                new GetServices(getApplicationContext()).execute(finalId);
            }
        });


    }
}