package com.example.noqdocapp;

import android.content.SharedPreferences;
import android.util.Log;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.Random;

public class Service {
    private int serviceId = 0;
    private int actualTurn = 0;
    private int remainingTurns = 0;
    private String title;

    public Service(int serviceId){
        this.serviceId = serviceId;
        //TODO
        setTitle();
        setActualTurn();
        setRemainingTurns();
    }

    public int getServiceId(){
        return serviceId;
    }

    public int getActualTurn(){
        return actualTurn;
    }

    public void setActualTurn() {
        APIRequest actualTurnRequest =new APIRequest(136);
        actualTurnRequest.putExtra("service_id",serviceId);
        try {
            this.actualTurn = new JSONObject(actualTurnRequest.execute()).getInt("current_turn");
        } catch (JSONException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public int getRemainingTurns() {
        return remainingTurns;
    }

    public void setRemainingTurns() {
        APIRequest lastTurnRequest = new APIRequest(20);
        lastTurnRequest.putExtra("service_id",serviceId);
        try {
            this.remainingTurns = new JSONObject(lastTurnRequest.execute()).getInt("last_turn")-actualTurn;
        } catch (JSONException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public String getTitle() {
        return title;
    }

    public void setTitle() {
        APIRequest titleRequest =new APIRequest(41);
        titleRequest.putExtra("service_id",serviceId);
        try {
            this.title = new JSONObject(titleRequest.execute()).getString("title");
        } catch (JSONException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public static void addService(String title, SharedPreferences sp) throws IOException {
        SharedPreferences.Editor edit = sp.edit();
        Random rand = new Random();
        APIRequest existsRequest =new APIRequest(40);
        int newId = rand.nextInt((int) Math.pow(10,25));
        existsRequest.putExtra("service_id",newId);
        while(true){
            try {
                if ((new JSONObject(existsRequest.execute()).getInt("exists")==0)) break;
            } catch (JSONException e) {
                e.printStackTrace();
            }
            newId = rand.nextInt((int) Math.pow(10,25));
            existsRequest.putExtra("service_id",newId);
        }
        APIRequest addService =new APIRequest(230);
        addService.putExtra("service_id",newId);
        addService.putExtra("title",title);
        addService.execute();
        edit.putInt("serviceId",newId);
        edit.apply();
    }

    public void nextTurn() throws IOException {
        APIRequest nextTurnRequest =new APIRequest(137);
        nextTurnRequest.putExtra("service_id",serviceId);
        nextTurnRequest.execute();
        if(remainingTurns == 0){
            APIRequest toZero =new APIRequest(43);
            toZero.putExtra("service_id",serviceId);
            toZero.execute();
            actualTurn = 0;
            remainingTurns = 0;
        }else {
            actualTurn += 1;
            remainingTurns -= 1;
        }
    }

    public int takeTurn(){
        //TODO
        Random rand = new Random();
        int newId = rand.nextInt((int) Math.pow(10,25));
        APIRequest existsRequest =new APIRequest(42);
        existsRequest.putExtra("turn_id",newId);
        while(true) {
            try {
                if (new JSONObject(existsRequest.execute()).getInt("exists")==0) break;
            } catch (IOException | JSONException e) {
                e.printStackTrace();
            }
            newId = rand.nextInt((int) Math.pow(10,25));
            existsRequest.putExtra("turn_id", newId);
        }
        APIRequest takeTurnRequest =new APIRequest(21);
        takeTurnRequest.putExtra("service_id",serviceId);
        takeTurnRequest.putExtra("turn_id",newId);
        try {
            takeTurnRequest.execute();
        } catch (IOException e) {
            e.printStackTrace();
        }
        remainingTurns += 1;
        return newId;

    }
}
