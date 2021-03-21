package com.example.noubty;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.media.RingtoneManager;
import android.os.Handler;
import android.os.IBinder;
import android.util.Log;
import android.widget.Toast;

import androidx.annotation.Nullable;
import androidx.core.app.NotificationCompat;

import com.lib.APIRequest;
import com.lib.EventClass;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.HashSet;
import java.util.Set;
import java.util.Timer;
import java.util.TimerTask;

public class NotificationService extends Service {

    private Timer timer;
    private TimerTask timerTask;
    private static String TAG = "NotificationService";
    private int SECS = 10;

    private boolean notifiedNoConn = false;

    //we are going to use a handler to be able to run in our TimerTask
    private final Handler handler = new Handler();


    @Nullable
    @Override
    public IBinder onBind(Intent arg0) {
        return null;
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        super.onStartCommand(intent, flags, startId);
        startTimer();
        return START_STICKY;
    }

    @Override
    public void onDestroy() {
        stopTimerTask();
		Toast.makeText(this, "Notification Service of NoQ is shutting down", Toast.LENGTH_SHORT).show();
        super.onDestroy();


    }

    public void startTimer() {
        //set a new Timer
        timer = new Timer();
        //initialize the TimerTask's job
        initializeTimerTask();

        timer.schedule(timerTask, 5000, SECS * 1000); //
    }

    public void stopTimerTask() {
        //stop the timer, if it's not already null
        if (timer != null) {
            timer.cancel();
            timer = null;
        }
    }

    public void initializeTimerTask() {

        timerTask = new TimerTask() {
            public void run() {

                //use a handler to run a toast that shows the current timestamp
                handler.post(new Runnable() {
                    public void run() {
                        notification();
                    }
                });
            }
        };
    }

    private void notification() {
        try {
            if (!APIRequest.isConnected()) {
                Log.d(TAG, "No Connexion");
                if (!notifiedNoConn) {
                    Toast.makeText(this, "Pas de connexion", Toast.LENGTH_SHORT).show();
                    notifiedNoConn = true;
                }
                return;
            }
        } catch (InterruptedException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }
        notifiedNoConn = false;
        SharedPreferences sp = getSharedPreferences("noubty_qr_codes.cache", MODE_PRIVATE);
        SharedPreferences.Editor edit = sp.edit();
        Set<String> qrCodes = sp.getAll().keySet();
        String response;
        Set<String> toRemove = new HashSet<>();
        for (String code : qrCodes) {
            try {
                APIRequest rqt = new APIRequest(9);
                rqt.putExtra("code", Integer.parseInt(code));
                response = rqt.execute();
                JSONObject answer = new JSONObject(response);
                int error = answer.getInt("error");
                if (error == 0) {
                    EventClass ev = new EventClass(answer.getString("title"), answer.getInt("myTurn"), answer.getInt("beforeMe"));
                    notify(code, ev);
                    Log.d("event notified: ", answer.getString("title"));
                } else if (error == 9) {
                    toRemove.add(code);
                    edit.remove(code);
                    edit.apply();
                }
                //TODO error = 1 ??
            } catch (IOException | JSONException e) {
                e.printStackTrace();

            }
        }
        qrCodes.removeAll(toRemove);
    }

    private void notify(String code, EventClass ev) {
        if (!isNotified(code) && ev.getBeforeMe() < 1) {
            setNotified(code);
            int id = EventClass.getId();
            NotificationManager mNotificationManager =
                    (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
            if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                NotificationChannel channel = new NotificationChannel(Integer.toString(id),
                        ev.getTitle(),
                        NotificationManager.IMPORTANCE_DEFAULT);
                channel.setDescription("Arrivée du tour dans NoQ");
                mNotificationManager.createNotificationChannel(channel);
            }
            long[] pattern = {500,500,500,500,500,500,500,500,500};
            NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(this, Integer.toString(id))
                    .setSmallIcon(ev.getImage())
                    .setContentTitle(ev.getTitle())
                    .setContentText(ev.getTurn())
                    .setPriority(NotificationCompat.PRIORITY_DEFAULT)
                    .setSound(RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION))
                    .setVibrate(pattern);
                    //.setAutoCancel(true); // clear notification after click
        	/* TO GO TO A PAGE ON CLICK
				Intent intent = new Intent(getApplicationContext(), ACTIVITY_NAME.class);
				PendingIntent pi = PendingIntent.getActivity(this, 0, intent, PendingIntent.FLAG_UPDATE_CURRENT);
				mBuilder.setContentIntent(pi);
        	 */
            mNotificationManager.notify(id, mBuilder.build());
        }

    }

    private boolean isNotified(String code) {
        SharedPreferences sp = getSharedPreferences("noubty_qr_codes.cache", MODE_PRIVATE);
        return sp.getBoolean(code, true);
    }

    private void setNotified(String code) {
        SharedPreferences sp = getSharedPreferences("noubty_qr_codes.cache", MODE_PRIVATE);
        SharedPreferences.Editor edit = sp.edit();
        edit.putBoolean(code, true);
        edit.apply();
    }
}
