package org.argus.sms.app.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.database.Cursor;
import android.support.v4.widget.CursorAdapter;
import android.text.TextUtils;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.List;
import java.util.Locale;

import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.fragment.FragmentHistory;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperSms;
import org.argus.sms.app.utils.HelperReport;

/**
 * Created by Olivier Goutet.
 * Openium 2014
 *
 * Adapter used to provide data to the History ListView ({@link FragmentHistory}
 *
 */
public class AdapterHistory extends CursorAdapter implements View.OnClickListener {
    private final static String TAG = AdapterHistory.class.getSimpleName();
    private final static boolean DEBUG = true;
    private final SimpleDateFormat mSimpleDateFormatterMonth;
    private FragmentHistory mListener;
    private int mBaseColor;


    /**
     * Constructor of the Adapter
     * @param context Application context
     * @param fragment fragment linked with the Adapter
     * @param c cursor connected to the data
     */
    public AdapterHistory(final Context context, final FragmentHistory fragment, final Cursor c) {
        super(context, c, 0);
        mListener = fragment;
        mSimpleDateFormatterMonth = new SimpleDateFormat("MMMM yyyy", Locale.getDefault());
    }


    /**
     * Create a view for a specific line
     * @param context application context
     * @param cursor cursor linked with the data
     * @param viewGroup parent view
     * @return
     */
    @Override
    public View newView(final Context context, final Cursor cursor, final ViewGroup viewGroup) {
        View root = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.list_item_history, viewGroup, false);
        ViewHolder holder = new ViewHolder(root);
        root.setTag(holder);
        holder.statusParent.setOnClickListener(this);
        mBaseColor = holder.title.getCurrentTextColor();
        return root;
    }


    /**
     * Configure the created view
     *
     * @param view view of the current line
     * @param context application context
     * @param cursor cursor moved at the correct position
     */
    @Override
    public void bindView(final View view, final Context context, final Cursor cursor) {
        ViewHolder holder = (ViewHolder) view.getTag();
        boolean needHeader = false;
        // get First record Date
        Date currentDate = this.getDateForHistoryOrder(cursor);
        Calendar currentCalendar = new GregorianCalendar();
        currentCalendar.setTime(currentDate);

        if (cursor.getPosition() == 0) {
            needHeader = true;
        } else {
            int position = cursor.getPosition();
            mCursor.moveToPosition(position-1);
            Date previousDate = this.getDateForHistoryOrder(cursor);
            Calendar previousCalendar = new GregorianCalendar();
            previousCalendar.setTime(previousDate);
            mCursor.moveToPosition(position);

            if (BuildConfig.DEBUG && DEBUG){
                Log.d(TAG, "bindView separatorText="+mSimpleDateFormatterMonth.format(currentDate)+ " previousMonth="+mSimpleDateFormatterMonth.format(previousDate));
            }

            if ((currentCalendar.get(Calendar.YEAR) != previousCalendar.get(Calendar.YEAR)) ||
                    (currentCalendar.get(Calendar.MONTH) != previousCalendar.get(Calendar.MONTH))) {
                needHeader = true;
            }
        }

        if (needHeader) {
            holder.header.setVisibility(View.VISIBLE);
            holder.headerText.setText(mSimpleDateFormatterMonth.format(currentDate));
         }else {
            holder.header.setVisibility(View.GONE);
        }

        String week = mCursor.getString(cursor.getColumnIndex(SesContract.Sms.WEEK));
        holder.dateNumber.setText(week);
        String month = mCursor.getString(cursor.getColumnIndex(SesContract.Sms.MONTH));
        TypeSms typeSms = TypeSms.fromInt(mCursor.getInt(cursor.getColumnIndex(SesContract.Sms.TYPE)));
        holder.calendar.setVisibility(View.VISIBLE);
        setDrawableInTextView(holder.subTitle, 0);
        holder.statusParent.setVisibility(View.VISIBLE);
        holder.title.setVisibility(View.VISIBLE);
        holder.subTitle.setSingleLine(true);
        holder.textError.setText("");
        holder.cell.setBackgroundResource(R.drawable.card_round_rect_white);
        holder.subTitle.setTextColor(mBaseColor);

        String statusList = mCursor.getString(cursor.getColumnIndex("statuslist"));
        Status status = HelperSms.getStatusFromStatusList(statusList);

        switch (typeSms) {
            case WEEKLY:
                setDrawableInTextView(holder.subTitle, R.drawable.ic_status_weekly);
                //holder.title.setText(Config.getInstance(mContext).getValueForKey(Config.KEYWORD_WEEKLY));
                holder.title.setText(R.string.report_weekly_short);
                holder.subTitle.setText(mCursor.getString(cursor.getColumnIndex("list")));
                holder.dateText.setText(R.string.week);

                holder.dateText.setVisibility(View.VISIBLE);
                holder.dateNumber.setVisibility(View.VISIBLE);
                holder.alertPicture.setVisibility(View.GONE);
                break;
            case MONTHLY:
                setDrawableInTextView(holder.subTitle, R.drawable.ic_status_monthly);
                //holder.title.setText(Config.getInstance(mContext).getValueForKey(Config.KEYWORD_MONTHLY));
                holder.title.setText(R.string.report_monthly_short);
                holder.subTitle.setText(mCursor.getString(cursor.getColumnIndex("list")));
                holder.dateText.setText(R.string.month);
                holder.dateNumber.setText(month);
                holder.dateText.setVisibility(View.VISIBLE);
                holder.dateNumber.setVisibility(View.VISIBLE);
                holder.alertPicture.setVisibility(View.GONE);
                break;
            case ALERT: {
                holder.subTitle.setSingleLine(false);
                //holder.title.setText(Config.getInstance(mContext).getValueForKey(Config.KEYWORD_ALERT));
                holder.title.setText(R.string.alert);
                String subTitle = getTextForAdditionnalFields(cursor);
                holder.subTitle.setText(subTitle);

                holder.dateText.setVisibility(View.GONE);
                holder.dateNumber.setVisibility(View.GONE);
                holder.alertPicture.setVisibility(View.VISIBLE);

                holder.alertPicture.setImageResource(R.drawable.ic_status_alert);

                String errorText= mCursor.getString(cursor.getColumnIndex(SesContract.Sms.SMSCONFIRM));
                if (!TextUtils.isEmpty(errorText) && status != Status.RECEIVED){
                    holder.textError.setText(errorText);
                }
                break;
            }
            case THRESHOLD: {
                holder.cell.setBackgroundResource(R.drawable.card_round_rect_red);
                holder.statusParent.setVisibility(View.GONE);
                holder.title.setVisibility(View.GONE);
                holder.subTitle.setTextColor(mContext.getResources().getColor(R.color.oms_white));
                holder.subTitle.setSingleLine(false);
                setDrawableInTextView(holder.subTitle, R.drawable.ic_status_alert_white);
                holder.subTitle.setCompoundDrawablePadding(mContext.getResources().getDimensionPixelSize(R.dimen.history_drawable_padding));
                String subTitle = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));
                holder.subTitle.setText(subTitle);
                holder.calendar.setVisibility(View.GONE);
                holder.title.setVisibility(View.GONE);
                holder.alertPicture.setVisibility(View.GONE);
                break;
            }
            default: {
                holder.statusParent.setVisibility(View.GONE);
                holder.title.setVisibility(View.GONE);
                holder.subTitle.setSingleLine(false);
                setDrawableInTextView(holder.subTitle, R.drawable.ic_status_info);
                holder.subTitle.setCompoundDrawablePadding(mContext.getResources().getDimensionPixelSize(R.dimen.history_drawable_padding));
                String subTitle = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));
                holder.subTitle.setText(subTitle);
                holder.calendar.setVisibility(View.GONE);
                holder.title.setVisibility(View.GONE);
                holder.alertPicture.setVisibility(View.GONE);
            }
        }

        switch (status) {
            case SENT:
                holder.status.setImageResource(R.drawable.ic_sent);
                break;
            case RECEIVED:
                holder.status.setImageResource(R.drawable.ic_received);
                break;
            case ERROR:
                holder.status.setImageResource(R.drawable.ic_error);
                break;
            case RECEIVED_BUT_NOT_OK:
                holder.status.setImageResource(R.drawable.ic_status_alert_black);
                break;
            case PARTIAL:
                holder.status.setImageResource(R.drawable.ic_sent);
                break;
            default:
                holder.status.setImageResource(R.drawable.ic_partial);
        }
    }

    /**
     * Return the History Order date
     * TODO : Maybe use the specific fields Week & Month & Year for the different type of submissions to retrieve the correct date and use epidemiologic functions
     *
     * @param cursor
     * @return
     */
    private Date getDateForHistoryOrder(final Cursor cursor)
    {
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd"); // History Order date formant
        Date date = new Date();

        try {
            int columnIndex = cursor.getColumnIndex(SesContract.Sms.DATE_HISTORY_ORDER);
            String dateOrder = mCursor.getString(columnIndex);
            date = format.parse(dateOrder);
        }
        catch (Exception ex)
        {
            Log.e(TAG, "error occurs in getDateForHistoryOrder function");
            ex.printStackTrace();
        }

        return date ;
    }

    @SuppressLint("NewApi")
    private void setDrawableInTextView(final TextView view, final int ressource) {
        int currentapiVersion = android.os.Build.VERSION.SDK_INT;
        if (currentapiVersion >= android.os.Build.VERSION_CODES.JELLY_BEAN_MR1){
            view.setCompoundDrawablesRelativeWithIntrinsicBounds(ressource, 0, 0, 0);
        } else{
            view.setCompoundDrawablesWithIntrinsicBounds(ressource, 0, 0, 0);
        }
    }

    /**
     * Get the subtitle for the cursor moved at the correct position
     * @param cursor
     * @return the subtitle text
     */
    private String getTextForAdditionnalFields(final Cursor cursor) {
        String sms = cursor.getString(cursor.getColumnIndex(SesContract.Sms.TEXT));
        List<Parser.ConfigField> listData = Parser.getOtherFieldsFromSms(sms, Config.getInstance(mContext));
        return HelperReport.buildTextFromAdditionnalValues(listData);
    }

    /**
     * Called when the status is clicked
     * @param view view clicked
     */
    @Override
    public void onClick(final View view) {
         mListener.onClickStatus(view);
    }

    /**
     * View Holder to improve Adapter speed by avoiding multiple findViewById call ({@link android.view.View}).
     */
    public class ViewHolder {

        public View header;
        public TextView headerText;
        public View cell;
        public TextView title;
        public TextView subTitle;
        public TextView textError;
        public View calendar;
        public TextView dateNumber;
        public TextView dateText;
        public View statusParent;
        public ImageView status;
        public ImageView alertPicture;

        /**
         * Constructor of the ViewHolder. find all usefull view in the line
         * @param v line view
         */
        public ViewHolder(final View v) {
            header = v.findViewById(R.id.list_item_history_header);
            headerText = (TextView) v.findViewById(R.id.list_item_history_header_TextView);
            // ----------------------------------------------------------------
            cell = v.findViewById(R.id.list_item_cardView_cell);
            title = (TextView) v.findViewById(R.id.list_item_history_TextView_title);
            subTitle = (TextView) v.findViewById(R.id.list_item_history_TextView_subtitle);
            textError = (TextView) v.findViewById(R.id.list_item_history_TextView_errorText);

            calendar = v.findViewById(R.id.list_item_history_CardView_Calendar);
            dateNumber = (TextView) v.findViewById(R.id.list_item_history_TextView_dateNumber);
            dateText = (TextView) v.findViewById(R.id.list_item_history_TextView_dateText);
            statusParent = (View) v.findViewById(R.id.list_item_history_summary_CardView_status);
            status = (ImageView) v.findViewById(R.id.list_item_history_summary_ImageView_status);
            alertPicture = (ImageView) v.findViewById(R.id.list_item_history_image);
        }
    }
}
