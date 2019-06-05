package org.argus.sms.app.model;

import java.util.HashMap;
import java.util.Map;

/**
 * Different status of an SMS
 * Created by Olivier Goutet.
 * Openium 2014
 */

public enum Status {
    UNKNOWN(0),
    SENT(1),
    ERROR(2),
    RECEIVED(3),
    DRAFT(4),
    PARTIAL(5),
    RECEIVED_BUT_NOT_OK(6);


    /**
     * Correspondance map for the enum value
     */
    private static final Map<Integer, Status> intToTypeMap = new HashMap<Integer, Status>();
    /**
     * init of the correspondance map
     */
    static {
        for (Status type : Status.values()) {
            intToTypeMap.put(type.mId, type);
        }
    }

    private final int mId;

    private Status(int i){
        mId = i;
    }

    /**
     * Int value of a specific enum value
     * @return int corresponding value
     */
    public int toInt(){
        return mId;
    }

    /**
     * Get the Enum Status value from an int
     * @param i numeric value
     * @return Enum type corresponding
     */
    public static Status fromInt(int i) {
        Status type = intToTypeMap.get(Integer.valueOf(i));
        if (type == null)
            return Status.UNKNOWN;
        return type;
    }
}
