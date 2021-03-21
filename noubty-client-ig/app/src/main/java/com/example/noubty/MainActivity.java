package com.example.noubty;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.annotation.RequiresApi;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.NotificationCompat;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Color;
import android.media.RingtoneManager;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.TextView;

import com.lib.APIRequest;
import com.lib.EventClass;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.Set;
import java.util.Timer;
import java.util.TimerTask;
import java.util.TreeSet;
import java.util.concurrent.locks.Lock;
import java.util.concurrent.locks.ReentrantLock;

import static com.lib.APIRequest.isConnected;

public class MainActivity extends AppCompatActivity {
	private static String TAG = "MainActivity";

    public static Lock lock = new ReentrantLock();
    private Timer eventsUpdate;
	private final Handler handler = new Handler();

    private Button scanButton;
	private static ListView eventsList;
	private static int topOfList = 0;
	private static int firstVisibleRow = 0;
	private static TreeSet<EventClass> orderedEvents;
	private static ArrayList<EventClass> events;
	private static Set<String> qrCodes;

	private Intent notificationIntent;
	static boolean notifiedNoConn = false;

	@RequiresApi(api = Build.VERSION_CODES.LOLLIPOP)
	@Override
    protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		//flashNotification();
		setContentView(R.layout.activity_main);

        scanButton = findViewById(R.id.scannbutton);
        scanButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
            	Intent i = new Intent(getApplicationContext(), Scanner.class);
                startActivity(i);
            }
        });

		eventsList = findViewById(R.id.listView);
		setEventsList(this);

		notificationIntent = new Intent(this, NotificationService.class);
		startService(notificationIntent);

		eventsUpdate = new Timer();
		eventsUpdate.schedule(new TimerTask() {
			@Override
			public void run() {
				runOnUiThread(new Runnable() {
					@Override
					public void run() {
						Log.d(TAG, "updating is running");
						MainActivity.setEventsList(MainActivity.this);
					}
				});

			}
		}, 0, 3000);
	}

	/*
	@Override
	public void onResume () {
		super.onResume();
	}
	*/

	/*
	@Override
	public void onStop() {
		super.onStop();
		startService(new Intent(this, NotificationService.class));
	}
	*/

	/*
	@Override
	public void onDestroy() {
		SharedPreferences flashed = getSharedPreferences("flash.cache", MODE_PRIVATE);
		flashed.edit().putBoolean("flashed", false);
		super.onDestroy();
	}
	*/

	static class ListAdapter extends ArrayAdapter<EventClass> {

		Context context;
		ArrayList<EventClass> rEvents;

		ListAdapter(Context context, ArrayList<EventClass> rEvents) {
			super(context, R.layout.cell, R.id.title, rEvents);
			this.context = context;
			this.rEvents = rEvents;
		}

		@NonNull
		@Override
		public View getView(int position, @Nullable View convertView, @NonNull ViewGroup parent) {
			LayoutInflater layoutInflater = (LayoutInflater) context.getApplicationContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
			View cell = layoutInflater.inflate(R.layout.cell, parent, false);
			ImageView image = cell.findViewById(R.id.image);
			TextView title = cell.findViewById(R.id.title);
			TextView turn = cell.findViewById(R.id.turn);

			image.setImageResource(events.get(position).getImage());
			title.setText(events.get(position).getTitle());
			if (events.get(position).getBeforeMe() < 1) title.setTextColor(Color.RED);
			turn.setText(events.get(position).getTurn());

			return cell;
		}
	}

	public static void setEventsList(Context context) {
		try {
			if (!isConnected()) {
				Log.d("checkConn", "No connection");
				if (!notifiedNoConn) {
					Intent i = new Intent(context.getApplicationContext(), NoConnection.class);
					context.startActivity(i);
					notifiedNoConn = true;
				}
			} else {
				Log.d("checkConn", "Connected");
				lock.lock();
				firstVisibleRow = eventsList.getFirstVisiblePosition();
				View v = eventsList.getChildAt(0);
				topOfList = (v == null) ? 0 : (v.getTop() - eventsList.getPaddingTop());
				loadEvents(context);
				ListAdapter adapter = new ListAdapter(context, events);
				eventsList.setAdapter(adapter);
				eventsList.setSelectionFromTop(firstVisibleRow, topOfList);
				lock.unlock();
			}
		} catch (InterruptedException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}

	/**
	 * load events using QR codes stored in cache
	 */
	public static void loadEvents(Context context) {
	    loadQRCodes(context);
		events = new ArrayList<>();
		orderedEvents = new TreeSet<>();
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
					addEvent(ev);
					//notify(ev, context);
					Log.d("event added: ", answer.getString("title"));
				} else if (error == 9) {
					toRemove.add(code);
					SharedPreferences sp = context.getSharedPreferences("noubty_qr_codes.cache", MODE_PRIVATE);
					SharedPreferences.Editor edit = sp.edit();
					edit.remove(code);
					edit.apply();
				}
				//TODO error = 1 ??
			} catch (IOException | JSONException e) {
				e.printStackTrace();

			}
		}
		qrCodes.removeAll(toRemove);
		for (EventClass ev : orderedEvents)
			events.add(ev);
	}

	/**
	 * add event recently captured by QR scanner
	 */
	public static void addEvent(EventClass ev) {
		orderedEvents.add(ev);
	}

	/**
	 * add event recently captured by QR scanner
	 */
	public static void loadQRCodes(Context context) {
		SharedPreferences sp = context.getSharedPreferences("noubty_qr_codes.cache", MODE_PRIVATE);
		qrCodes = sp.getAll().keySet();
	}

	private static void notify(EventClass ev, Context context) {
		if (!ev.isNotified() && ev.getBeforeMe() < 1) {
			ev.setNotified();
			int id = EventClass.getId();

			NotificationManager mNotificationManager =
					(NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);
			if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
				NotificationChannel channel = new NotificationChannel(Integer.toString(id),
						ev.getTitle(),
						NotificationManager.IMPORTANCE_DEFAULT);
				channel.setDescription("Arrivée du tour dans NoQ");
				mNotificationManager.createNotificationChannel(channel);
			}

			long[] pattern = {500,500,500,500,500,500,500,500,500};
			NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(context, Integer.toString(id))
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

	private void flashNotification() {
		SharedPreferences flashed = getSharedPreferences("flash.cache", MODE_PRIVATE);
		if (flashed.getBoolean("flashed", false))
			return;
		flashed.edit().putBoolean("flashed", true);

		SharedPreferences sp = getSharedPreferences("noubty_qr_codes.cache", MODE_PRIVATE);
		Set<String> qrCodes = sp.getAll().keySet();
		SharedPreferences.Editor edit = sp.edit();
		for (String code : qrCodes)
			edit.putBoolean(code, false);
		edit.apply();

	}
}
