/**
 * 
 */
package org.argus.sms.app.provider;

import android.net.Uri;
import android.provider.BaseColumns;

import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.TypeSms;

/**
 * Contact to discribe the database of the application
 * @author Olivier Goutet
 * Openium - 2014
 * 
 */
public final class SesContract {

	private SesContract() {
		// does nothing
	}

	/*
	 * Tables' columns declaration
	 */

    /**
     * All columns of the table SMS
     */
	public interface SmsColumns {
		String DISEASE = "DISEASE";
		String WEEK = "WEEK";
        String MONTH = "MONTH";
		String YEAR = "YEAR";
		String TYPE = "TYPE";
        String SUBTYPE = "SUBTYPE";
		String ID = "ID_column";
		String TEXT = "TEXT_column";
		String TIMESTAMP = "TIMESTAMP";
		String STATUS = "STATUS";
		String SMSCONFIRM = "SMSCONFIRM";
		String LABEL = "LABEL";
		String REPORTID = "REPORTID";
		String SENDDATE = "SENDDATE";

		// Specific calculated field for History display
		String DATE_HISTORY_ORDER = "DateForHistoryOrder";
		String TYPE_FOR_HISTORY_DETAILS = "TypeForHistoryDetails";

	}

	/*
	 * Provider URI management
	 */
	public static final String CONTENT_AUTHORITY = BuildConfig.APPLICATION_ID+".provider";
	public static final Uri BASE_CONTENT_URI = Uri.parse("content://" + CONTENT_AUTHORITY);
	private static final String CONTENT_TYPE_FORMAT = "vnd.android.cursor.dir/vnd.".concat(CONTENT_AUTHORITY).concat(".");
	private static final String CONTENT_ITEM_TYPE_FORMAT = "vnd.android.cursor.item/vnd.".concat(CONTENT_AUTHORITY).concat(".");

	public static final String PATH_SMS = "sms";

	// --------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------

    /**
     * Sms class implementing the differents columns of the database
     */
	public static class Sms implements SmsColumns, BaseColumns {
		private static final String PATH = PATH_SMS;
		// -----------------------------
		public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH).build();
		public static final String CONTENT_TYPE = CONTENT_TYPE_FORMAT.concat(PATH);
		public static final String CONTENT_ITEM_TYPE = CONTENT_ITEM_TYPE_FORMAT.concat(PATH);

		public static final String PATH_BASEID = "baseid";
		public static final String PATH_HISTORY = "history";
		public static final String PATH_REPORTID = "reportid";


        /**
         * Get the {@link Uri} in database for the specific row in database at the unique identifier
         * @param _id unique identifier
         * @return The {@link Uri} to the unique row in database
         */
		public static Uri buildBaseIdUri(final long _id) {
			return CONTENT_URI.buildUpon().appendPath(PATH_BASEID).appendPath(Long.toString(_id)).build();
		}

        /**
         * Get the History {@link Uri} in database
         * @return History {@link Uri}
         */
        public static Uri buildHistoryUri() {
            return CONTENT_URI.buildUpon().appendPath(PATH_HISTORY).build();
        }

        /**
         * Get the id for a the uri given
         * @param uri {@link Uri} to parse
         * @return the unique id in {@link Uri}
         */
		public static String getBaseIdUri(final Uri uri) {
			return uri.getLastPathSegment();
		}

		/**
		 * Get the Last Report Id {@link Uri} in database
		 * @return Last Report Id {@link Uri}
		 */
		public static Uri buildLastReportIdUri() {
			return CONTENT_URI.buildUpon().appendPath(PATH_REPORTID).build();
		}
	}


	public static String getDetailsSelection() {
		return SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.STATUS + "=? AND " + SesContract.Sms.DISEASE + "=?";
	}

	public static String[] getDetailsSelectionArgs(TypeSms type, Status status, String disease) {
		return new String[]{String.valueOf(type.toInt()), String.valueOf(status.toInt()), disease};
	}

	public static String getDetailsSelectionId() {
		return SesContract.Sms.TYPE + "=? AND " + SesContract.Sms.STATUS + "=? AND " + SesContract.Sms.DISEASE + "=? AND " + SesContract.Sms.ID + "=?";
	}
	public static String[] getDetailsSelectionArgsId(TypeSms type, Status status, String disease, Integer id) {
		return new String[]{String.valueOf(type.toInt()), String.valueOf(status.toInt()), disease, id.toString() };
	}

}
