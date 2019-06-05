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

public enum SubTypeSms implements Serializable {
    MODEL_NONE(0),
    MODEL_ALERT(1),
    MODEL_WEEKLY(2),
    MODEL_MONTHLY(3),
    MODEL_HEALTHFACILITY(3),
    THRESHOLD(4);

    /**
     * Correspondance map for the enum value
     */
    private static final Map<Integer, SubTypeSms> intToTypeMap = new HashMap<Integer, SubTypeSms>();
    /**
     * init of the correspondance map
     */
    static {
        for (SubTypeSms type : SubTypeSms.values()) {
            intToTypeMap.put(type.mId, type);
        }
    }

    private final int mId;

    private SubTypeSms(int i){
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
     * Get the Enum SubTypeSms value from an int
     * @param i numeric value
     * @return Enum type corresponding
     */
    public static SubTypeSms fromInt(int i) {
        SubTypeSms type = intToTypeMap.get(Integer.valueOf(i));
        if (type == null)
            return SubTypeSms.MODEL_NONE;
        return type;
    }

}
