package com.example.noqdocapp;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Point;
import android.os.Bundle;
import android.util.Log;
import android.view.Display;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.TextView;

import java.io.IOException;

import androidmads.library.qrgenearator.QRGContents;
import androidmads.library.qrgenearator.QRGEncoder;

//TODO analyse security
//TODO review design
public class MainActivity extends AppCompatActivity {

    Service myService ;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        Log.d("myTag", "showing view" );
        setContentView(R.layout.activity_main);
        boolean notExist = true;
        SharedPreferences sp = getSharedPreferences("NoQDocAppPrivateData", MODE_PRIVATE);
        myService = new Service(sp.getInt("serviceId", -1));
        updateView();

        Button next = (Button) findViewById(R.id.nextButton);
        Button newTurn = (Button) findViewById(R.id.newTurnButton);
        next.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View view) {
                try {
                    myService.nextTurn();
                } catch (IOException e) {
                    e.printStackTrace();
                }
                updateView();
            }
        });
        newTurn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                int turnId = myService.takeTurn();
                Intent intent = new Intent(getApplicationContext(), QrCode.class);
                intent.putExtra("turnId", turnId);
                startActivity(intent);
                updateView();
            }
        });
    }

    private void updateView(){
        Button next = (Button) findViewById(R.id.nextButton);
        TextView title = (TextView) findViewById(R.id.titleEvent);
        title.setText(myService.getTitle());
        TextView actualTurn = (TextView) findViewById(R.id.actualTurn);
        actualTurn.setText(Integer.toString(myService.getActualTurn()));
        TextView waiting = (TextView) findViewById(R.id.waiting);
        waiting.setText(Integer.toString(myService.getRemainingTurns()));
        if(myService.getRemainingTurns()==0){
            next.setText(R.string.toZero);
        }else{
            next.setText(R.string.next);
        }


    }


}