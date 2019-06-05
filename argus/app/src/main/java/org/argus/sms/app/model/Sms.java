package org.argus.sms.app.model;

import android.content.ContentValues;
import android.database.Cursor;
import android.text.TextUtils;

import java.util.List;

import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperConstraint;

/**
 * Class containing all the data of an SMS in an Object form.
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class Sms
{

    public TypeSms mType;

    public String mDisease;

    public String mLabel;

    public String mWeek;

    public String mMonth;

    public String mYear;

    public int mId;

    public long mTimestamp;

    public Status mStatus;

    public List<Parser.ConfigField> mListData;

    public List<Parser.Constraint> mListConstraint;

    public long _Id ;

    public int mReportId;

    public long mSendDate;

    public Sms()
    {
    }

    public Sms(Sms sms)
    {
        mType = sms.mType;
        mDisease = sms.mDisease;
        mLabel = sms.mLabel;
        mWeek = sms.mWeek;
        mMonth = sms.mMonth;
        mYear = sms.mYear;
        mId = sms.mId;
        mTimestamp = sms.mTimestamp;
        mStatus = sms.mStatus;
        mListData = sms.mListData;
        if (mListData != null && mListData.size() == 0) {
            mListData = null;
        }

        mListConstraint = sms.mListConstraint;
        if (mListConstraint != null && mListConstraint.size() == 0) {
            mListConstraint = null;
        }

        mReportId = sms.mReportId;
        mSendDate = sms.mSendDate;
    }

    /**
     *
     * @param config Configuration of the Application
     * @param cursor Cursor connected to the database and moved to the correct position
     */
    public Sms(Config config, Cursor cursor)
    {
        mType = TypeSms.fromInt(cursor.getInt(cursor.getColumnIndex(SesContract.Sms.TYPE)));
        mDisease = cursor.getString(cursor.getColumnIndex(SesContract.Sms.DISEASE));
        mLabel = cursor.getString(cursor.getColumnIndex(SesContract.Sms.LABEL));
        mWeek = cursor.getString(cursor.getColumnIndex(SesContract.Sms.WEEK));
        mMonth = cursor.getString(cursor.getColumnIndex(SesContract.Sms.MONTH));
        mYear = cursor.getString(cursor.getColumnIndex(SesContract.Sms.YEAR));
        mId = cursor.getInt(cursor.getColumnIndex(SesContract.Sms.ID));
        mTimestamp = cursor.getLong(cursor.getColumnIndex(SesContract.Sms.TIMESTAMP));
        mStatus = Status.fromInt(cursor.getInt(cursor.getColumnIndex(SesContract.Sms.STATUS)));
        _Id = cursor.getLong(cursor.getColumnIndex(SesContract.Sms._ID));
        mReportId = cursor.getInt(cursor.getColumnIndex(SesContract.Sms.REPORTID));
        mSendDate = cursor.getLong(cursor.getColumnIndex(SesContract.Sms.SENDDATE));

        String textSms = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));

        if (mType == TypeSms.MONTHLY || mType == TypeSms.WEEKLY) {
            mListData = Parser.getOtherFieldsFromSmsReport(textSms, config);
            mListConstraint = Parser.getConstraintsFromSmsReport(textSms, config);
        }
        else
            mListData = Parser.getOtherFieldsFromSms(textSms, config);

        if (mListData != null && mListData.size() == 0) {
            mListData = null;
        }
    }

    /**
     * Get the content values for the current sms object. It create a {@link android.content.ContentValues} object to save it into the Database
     * @param config Configuration of the Application
     * @return ContentValues containing the data
     */
    public ContentValues getContentValues(Config config)
    {
        ContentValues cv = new ContentValues();
        if (mType != null) {
            cv.put(SesContract.Sms.TYPE, mType.toInt());
        }
        cv.put(SesContract.Sms.DISEASE, mDisease);
        cv.put(SesContract.Sms.WEEK, mWeek);
        cv.put(SesContract.Sms.MONTH, mMonth);
        cv.put(SesContract.Sms.YEAR, mYear);
        cv.put(SesContract.Sms.ID, mId);
        cv.put(SesContract.Sms.TIMESTAMP, mTimestamp);
        if (mLabel != null)
            cv.put(SesContract.Sms.LABEL, mLabel);
        if (mStatus != null) {
            cv.put(SesContract.Sms.STATUS, mStatus.toInt());
        }
        cv.put(SesContract.Sms.TEXT, toSms(mType, config, false));

        // Store the Report ID
        cv.put(SesContract.Sms.REPORTID, mReportId);

        // Store the sendDate
        cv.put(SesContract.Sms.SENDDATE, mSendDate);

        return cv;
    }

    /**
     * Override the standard equals method to compare the data inside a Sms object
     * @param o object to compare to
     * @return true if equals, false otherwise
     */
    @Override
    public boolean equals(final Object o)
    {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;

        Sms sms = (Sms) o;

        if (mId != sms.mId) return false;
        if (mLabel != sms.mLabel) return false;
        if (mTimestamp != sms.mTimestamp) return false;
        if (mDisease != null ? !mDisease.equals(sms.mDisease) : sms.mDisease != null) return false;
        if (mListData != null ? !mListData.equals(sms.mListData) : sms.mListData != null) return false;
        if (mListConstraint != null ? !mListConstraint.equals(sms.mListConstraint) : sms.mListConstraint != null) return false;
        if (mMonth != null ? !mMonth.equals(sms.mMonth) : sms.mMonth != null) return false;
        if (mStatus != sms.mStatus) return false;
        if (mType != sms.mType) return false;
        if (mWeek != null ? !mWeek.equals(sms.mWeek) : sms.mWeek != null) return false;
        if (mYear != null ? !mYear.equals(sms.mYear) : sms.mYear != null) return false;

        return true;
    }

    /**
     * Override the standard equals method to compare the data inside a Sms object
     * @return the hashcode
     */
    @Override
    public int hashCode()
    {
        int result = mType != null ? mType.hashCode() : 0;
        result = 31 * result + (mDisease != null ? mDisease.hashCode() : 0);
        result = 31 * result + (mWeek != null ? mWeek.hashCode() : 0);
        result = 31 * result + (mMonth != null ? mMonth.hashCode() : 0);
        result = 31 * result + (mYear != null ? mYear.hashCode() : 0);
        result = 31 * result + mId;
        result = 31 * result + (int) (mTimestamp ^ (mTimestamp >>> 32));
        result = 31 * result + (mStatus != null ? mStatus.hashCode() : 0);
        result = 31 * result + (mLabel != null ? mLabel.hashCode() : 0);
        result = 31 * result + (mListData != null ? mListData.hashCode() : 0);
        result = 31 * result + (mListConstraint != null ? mListConstraint.hashCode() : 0);
        return result;
    }


    /**
     * Transform an Sms object to the String representation
     * @param type type of sms
     * @param config {@link Config} for the application
     * @return the string value of the Sms
     */
    public String toSms(TypeSms type, Config config, boolean forSend)
    {
        StringBuilder sb = new StringBuilder();

        if (type == TypeSms.WEEKLY) {
            sb.append(config.getValueForKey(Config.KEYWORD_WEEKLY));
        } else if (type == TypeSms.MONTHLY) {
            sb.append(config.getValueForKey(Config.KEYWORD_MONTHLY));
        } else if (type == TypeSms.ALERT) {
            sb.append(config.getValueForKey(Config.KEYWORD_ALERT));
        }
        // Adding TEST_SUFFIX to avoid server keep useless message
        if (Config.IsTest)
            sb.append(Config.TEST_SUFFIX);
        sb.append(Parser.SEPARATOR_SPACE);

        if (!TextUtils.isEmpty(mDisease)) {
            appendField(sb, config, Config.KEYWORD_DISEASE, mDisease);
            sb.append(Parser.SEPARATOR_FIELDS);
        }
        if (!forSend && !TextUtils.isEmpty(mLabel)) {
            appendField(sb, config, Config.KEYWORD_LABEL, mLabel);
            sb.append(Parser.SEPARATOR_FIELDS);
        }
        if (!TextUtils.isEmpty(mYear)) {
            appendField(sb, config, Config.KEYWORD_YEAR, mYear);
            sb.append(Parser.SEPARATOR_FIELDS);
        }
        if (type == TypeSms.WEEKLY && !TextUtils.isEmpty(mWeek)) {
            appendField(sb, config, Config.KEYWORD_WEEK, mWeek);
            sb.append(Parser.SEPARATOR_FIELDS);
        } else if (type == TypeSms.MONTHLY && !TextUtils.isEmpty(mMonth)) {
            appendField(sb, config, Config.KEYWORD_MONTH, mMonth);
            sb.append(Parser.SEPARATOR_FIELDS);
        }
        if (mListData != null) {
            for (Parser.ConfigField cf : mListData) {
                    sb.append(cf.Name);
                    sb.append(Parser.SEPARATOR_EQUALS);
                    sb.append(cf.Type);
                    sb.append(Parser.SEPARATOR_FIELDS);
            }
        }

        //Save constraints
        if (!forSend && mListConstraint != null) {
            for (Parser.Constraint cf : mListConstraint) {
                if (cf.Constraint != null) {
                    sb.append(cf.FieldFrom);
                    sb.append(HelperConstraint.ConstraintLibrary[cf.Constraint.getValue()]);
                    sb.append(cf.FieldTo);
                    sb.append(Parser.SEPARATOR_FIELDS);
                }
            }
        }

        sb.append(Config.KEYWORD_ID);
        sb.append(Parser.SEPARATOR_EQUALS);
        sb.append(mId);
        sb.append(Parser.SEPARATOR_FIELDS);

        // Add the Report ID for reports, not alert
        if (forSend && type != TypeSms.ALERT) {
            sb.append(Config.KEYWORD_REPORT_ID);
            sb.append(Parser.SEPARATOR_EQUALS);
            sb.append(mReportId);
            sb.append(Parser.SEPARATOR_FIELDS);
        }

        if (sb.toString().endsWith(Parser.SEPARATOR_FIELDS)){
            // remove trailing SEPARATOR_FIELDS
            sb.setLength(sb.length()-Parser.SEPARATOR_FIELDS.length());
        }
        return sb.toString();
    }

    /**
     * @param alertTitle
     * @return confirm message readable by a human
     */
    public String toConfirmDialog(String alertTitle)
    {
        StringBuilder sb = new StringBuilder();

        if (alertTitle != null){
            sb.append(alertTitle);
            sb.append(" :");
        }
        else if (mLabel != null){
            sb.append("- "+ mLabel);
            sb.append(" :");
        }

        if (mListData != null) {
            for (Parser.ConfigField cf : mListData) {
                if (cf.Name != null && cf.Type != null && !TextUtils.isEmpty(cf.Type)) {
                    sb.append(" ");
                    sb.append(cf.Name.substring(0, 1) + cf.Name.substring(1).toLowerCase());
                    sb.append(Parser.SEPARATOR_EQUALS);
                    sb.append(cf.Type);
                    sb.append(Parser.SEPARATOR_FIELDS);
                }
            }
        }

        if (sb.toString().endsWith(Parser.SEPARATOR_FIELDS)){
            // remove trailing SEPARATOR_FIELDS
            sb.setLength(sb.length()-Parser.SEPARATOR_FIELDS.length());
        }
        return sb.toString();
    }

    /**
     * Add a field to the string representation of an Sms object
     * @param sb StringBuilder containing the total String
     * @param config Configuration of the Application
     * @param key Key to append
     * @param data value to append
     */
    private void appendField(final StringBuilder sb, final Config config, final String key, final String data)
    {
        sb.append(config.getValueForKey(key));
        sb.append(Parser.SEPARATOR_EQUALS);
        sb.append(data);
    }
}
