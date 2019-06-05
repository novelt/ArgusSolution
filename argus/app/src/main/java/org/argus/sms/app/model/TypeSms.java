package org.argus.sms.app.model;

import java.io.Serializable;
import java.util.HashMap;
import java.util.Map;

/**
 * Enum of different SMS types the application can receive
 * Created by Olivier Goutet.
 * Openium 2014
 *
 */

public enum TypeSms implements Serializable {
    OTHER(0),
    MODEL(1),
    CONFIRM(2),
    ERROR(3),
    ALERT(4),
    WEEKLY(5),
    MONTHLY(6),
    CONFIG(7),
    THRESHOLD(8),;

    /**
     * Correspondance map for the enum value
     */
    private static final Map<Integer, TypeSms> intToTypeMap = new HashMap<Integer, TypeSms>();

    /**
     * init of the correspondance map
     */
    static {
        for (TypeSms type : TypeSms.values()) {
            intToTypeMap.put(type.mId, type);
        }
    }

    private final int mId;

    private TypeSms(int i){
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
     * Get the Enum TypeSms value from an int
     * @param i numeric value
     * @return Enum type corresponding
     */
    public static TypeSms fromInt(int i) {
        TypeSms type = intToTypeMap.get(Integer.valueOf(i));
        if (type == null)
            return TypeSms.OTHER;
        return type;
    }

}
