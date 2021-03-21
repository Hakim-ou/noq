package com.lib;

import android.util.Log;

import androidx.annotation.NonNull;

import com.example.noubty.Event;
import com.example.noubty.R;

import java.util.HashSet;
import java.util.Set;

public class EventClass implements  Comparable<EventClass> {
    private String title;
    private int image;
    private int myTurn;
    private int beforeMe;
    private static Set<EventClass> notified = new HashSet<>();

    private static String TAG = "EventClass";
    private static int id = 1000;

    public EventClass(String title, int myTurn, int beforeMe) {
        this.title = title;
        this.myTurn = myTurn;
        this.beforeMe = beforeMe;
        this.image = R.drawable.event;
    }

    public static EventClass loadEvent(int qrCode) {
        //TODO
        return new EventClass("Title", 1, 0);
    }

    public String getTitle() {
        return title;
    }

    public int getImage() {
        return image;
    }

    public int getBeforeMe() {
        return beforeMe;
    }

    public String getTurn() {
        return (beforeMe < 1) ? "C'est votre tour !" :
                                "tour " + myTurn + " - " + beforeMe + " personnes en attente";
    }

    public boolean isNotified() {
        for (EventClass ev : notified) {
            if (this.equals(ev)) return true;
        }
        return false;
    }

    public void setNotified() {
        Log.d("notification", notified.size() + " event notified");
        notified.add(this);
        if (notified.size() >= 20) {
            notified = new HashSet<>();
            Log.d(TAG, "empty notified set");
        }
    }

    public static int getId() {
        return id++;
    }

    @NonNull
    @Override
    public String toString() {
        return title;
    }

    @Override
    public boolean equals(Object o) {
        if (o instanceof EventClass)
            return beforeMe == ((EventClass)o).beforeMe && myTurn == ((EventClass)o).myTurn
                    && title.equals(((EventClass)o).title);
        else return false;
    }

    @Override
    public int hashCode() {
        return title.hashCode() + beforeMe + myTurn;
    }

    @Override
    public int compareTo(EventClass ev) {
        if (beforeMe > ev.beforeMe)
            return 1;
        if (beforeMe < ev.beforeMe)
            return -1;
        return title.compareTo(ev.title);
    }

}
