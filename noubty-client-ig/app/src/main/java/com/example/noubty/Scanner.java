package com.example.noubty;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;

import android.Manifest;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.widget.TextView;
import android.widget.Toast;

import com.budiyev.android.codescanner.CodeScanner;
import com.budiyev.android.codescanner.CodeScannerView;
import com.budiyev.android.codescanner.DecodeCallback;
import com.google.zxing.Result;
import com.karumi.dexter.Dexter;
import com.karumi.dexter.PermissionToken;
import com.karumi.dexter.listener.PermissionDeniedResponse;
import com.karumi.dexter.listener.PermissionGrantedResponse;
import com.karumi.dexter.listener.PermissionRequest;
import com.karumi.dexter.listener.single.PermissionListener;
import com.lib.APIRequest;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

public class Scanner extends AppCompatActivity {

    public static final String TAG = "Scanner";

    CodeScanner codeScanner;
    CodeScannerView scannView;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_scanner);
        scannView = findViewById(R.id.scannerview);
        codeScanner = new CodeScanner(this, scannView);
        codeScanner.setDecodeCallback(new DecodeCallback() {
            @Override
            public void onDecoded(@NonNull final Result result) {

                String response;
                try {
                    APIRequest rqt = new APIRequest(9);
                    rqt.putExtra("code", Integer.parseInt(result.getText()));
                    response = rqt.execute();
                    JSONObject answer = new JSONObject(response);
                    int  error = answer.getInt("error");
                    if (error == 0) {
                        SharedPreferences sp = getSharedPreferences("noubty_qr_codes.cache", MODE_PRIVATE);
                        SharedPreferences.Editor edit = sp.edit();
                        edit.putBoolean(result.getText(), false);// not notified
                        edit.apply();
                        Log.d(TAG, "code added successfully: " + result.getText());
                        finish();
                    } else if(error == 9){
                        Log.d(TAG, "qr code doesn't correspond to any turn");
                        runOnUiThread(new Runnable() {
                            @Override
                            public void run() {
                                Toast.makeText(getApplicationContext(), "qr code doesn't correspond to any turn", Toast.LENGTH_LONG).show();
                                finish();
                            }
                        });
                    } else if(error == 1){
                        Log.d(TAG, "qr code doesn't correspond to any event");
                        runOnUiThread(new Runnable() {
                            @Override
                            public void run() {
                                Toast.makeText(getApplicationContext(), "qr code doesn't correspond to any event", Toast.LENGTH_LONG).show();
                                finish();
                            }
                        });
                    }
                } catch (IOException | JSONException e) {
                    e.printStackTrace();
                }
            }
        });

    }

    @Override
    protected void onResume() {
        super.onResume();
        requestForCamera();
    }

    private void requestForCamera() {
        Dexter.withActivity(this).withPermission(Manifest.permission.CAMERA).withListener(new PermissionListener() {
            @Override
            public void onPermissionGranted(PermissionGrantedResponse permissionGrantedResponse) {
                codeScanner.startPreview();
            }

            @Override
            public void onPermissionDenied(PermissionDeniedResponse permissionDeniedResponse) {
                Toast.makeText(Scanner.this, "camera permission required", Toast.LENGTH_SHORT).show();
            }

            @Override
            public void onPermissionRationaleShouldBeShown(PermissionRequest permissionRequest, PermissionToken permissionToken) {
                permissionToken.continuePermissionRequest();
            }
        }).check();
    }

}
