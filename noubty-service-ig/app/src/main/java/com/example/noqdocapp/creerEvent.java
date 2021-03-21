package com.example.noqdocapp;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.google.android.material.textfield.TextInputEditText;

import java.io.IOException;
import java.util.Random;

public class creerEvent extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        Log.d("myTag", "hi");
        setContentView(R.layout.activity_creer_event);


        Button button = (Button) findViewById(R.id.newServiceButton);
        button.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                EditText titleInput = (EditText) findViewById(R.id.serviceTitle);
                String title = titleInput.getText().toString();
                if(title.equals("")){
                    Context context = getApplicationContext();
                    Toast.makeText(creerEvent.this, context.getResources().getString(R.string.titleImpermitted), Toast.LENGTH_SHORT).show();
                }else{
                    SharedPreferences sp = getSharedPreferences("NoQDocAppPrivateData",MODE_PRIVATE);
                    try {
                        Service.addService(title, sp);
                    } catch (IOException e) {
                        e.printStackTrace();
                    }
                    Intent intent = new Intent(getApplicationContext(), MainActivity.class);
                    //intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                    startActivity(intent);
                    finish();
                }
            }
        });
    }
}