package com.argus.gateway;

import android.os.SystemClock;

import org.junit.Test;

import static org.junit.Assert.*;

/**
 * To work on unit tests, switch the Test Artifact in the Build Variants view.
 */
public class ExampleUnitTest {
    @Test
    public void addition_isCorrect() throws Exception {
        assertEquals(4, 2 + 2);
    }

    @Test
    public void orderOfCalculation()
    {
        int second = 1000;
        int minute = second * 60;
        long now = 25000;
        long nextRetryTime = now + 24 * 60 * minute;

        assertEquals(nextRetryTime, 86400000 + now);
    }
}