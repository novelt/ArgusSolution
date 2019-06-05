package org.argus.sms.app.model;

/**
 * Enum of data types the application can handle
 * Created by Olivier Goutet.
 * Openium 2014
 *
 */

public enum TypeData {
    NUMBER("Int"),
    TEXT("Str"),
    DATE("Dat"),
    YEAR("year"),
    MONTH("month"),
    WEEK("week");

    private final String mText;

    private TypeData(String correspondant){
        mText = correspondant;
    }

    /**
     * Get the string value of the enum
     * @return
     */
    public String toString(){
        return mText;
    }

}
