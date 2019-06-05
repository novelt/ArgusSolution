package org.argus.sms.app.fragment;

import android.content.AsyncQueryHandler;
import android.content.ContentResolver;
import android.content.ContentValues;
import android.database.Cursor;
import android.os.Bundle;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.widget.LinearLayoutCompat;
import android.util.Log;
import android.util.Pair;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.LinearLayout;

import java.util.ArrayList;
import java.util.List;

import butterknife.ButterKnife;
import butterknife.InjectView;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.model.Sms;
import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.TypeSms;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.provider.SesContract;
import org.argus.sms.app.utils.HelperConstraint;
import org.argus.sms.app.view.ViewReportNumber;

/**
 * Report detail Fragment
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentReportDetail extends AbstractFragment implements LoaderManager.LoaderCallbacks<Cursor>, View.OnClickListener {
    private final static String KEY_TYPE = "key_type";
    private final static String KEY_DISEASE = "key_disease";

    private final static String TAG = FragmentReportDetail.class.getSimpleName();
    private final static boolean DEBUG = true;

    private TypeSms mTypeSms;
    private String mDisease;

    private int LOADER_DETAIL = 1;
    @InjectView(R.id.fragment_report_detail_LinearLayout)
    protected LinearLayoutCompat mLinearLayout;

    @InjectView(R.id.fragment_report_Button_ok)
    protected Button mButtonOk;
    private List<Pair<Integer, ViewReportNumber>> mListReportsItems = new ArrayList<>();
    private ArrayList<Sms> mSms;

    /**
     * Create a new instance of FragmentReportDetail configured with the params value
     * @param typeSms type of sms
     * @param disease disease
     * @returna new FragmentReportDetail
     */
    public static FragmentReportDetail newInstance(TypeSms typeSms, String disease) {
        FragmentReportDetail fragment = new FragmentReportDetail();
        Bundle args = new Bundle();
        args.putSerializable(KEY_TYPE, typeSms);
        args.putString(KEY_DISEASE, disease);
        fragment.setArguments(args);
        return fragment;
    }

    public FragmentReportDetail() {
        // Required empty public constructor
    }


    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            mTypeSms = (TypeSms) getArguments().getSerializable(KEY_TYPE);
            mDisease = getArguments().getString(KEY_DISEASE);
        }
        mSms = new ArrayList<>();

        setHasOptionsMenu(true);
        getLoaderManager().initLoader(LOADER_DETAIL, null, this);
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View mainView = inflater.inflate(R.layout.fragment_report_detail, container, false);
        ButterKnife.inject(this, mainView);
        mButtonOk.setOnClickListener(this);
        return mainView;
    }


    @Override
    public void onCreateOptionsMenu(final Menu menu, final MenuInflater inflater) {
        inflater.inflate(R.menu.menu_fragment_detail, menu);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();
        //noinspection SimplifiableIfStatement
        if (id == R.id.action_zero) {
            onClickZero();
            return true;
        } else if (id == android.R.id.home) {
            hideKeyboard();
            getActivity().onBackPressed();
        }
        return super.onOptionsItemSelected(item);
    }

    /**
     * Add the report items on the screen
     * @param items items to add
     */
    private void addReportsItemsToScreen(final List<Parser.ConfigField> items, final List<Parser.Constraint> constraints,  Integer smsNumber) {
        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        if (items == null)
            return;
        for (Parser.ConfigField pair : items) {
            ViewReportNumber vrn = null;
            try {
                int value = Integer.parseInt(pair.Type);
                vrn = new ViewReportNumber(getActivity(), getActivity(), pair.Name, value);
            } catch (Exception e) {
                vrn = new ViewReportNumber(getActivity(), getActivity(), pair.Name, pair.IsOptionnal);
            }
            mLinearLayout.addView(vrn, lp);
            mListReportsItems.add(new Pair<>(smsNumber, vrn));
        }

        if (constraints != null) {
            for (Parser.Constraint pair : constraints) {
                ViewReportNumber viewOne = getConstraintView(pair.FieldFrom.trim());
                ViewReportNumber viewTwo = getConstraintView(pair.FieldTo.trim());
                if (viewOne != null && viewTwo != null) {
                    HelperConstraint.AddConstraintInViewNumber(viewOne, viewTwo, pair.Constraint);
                }
            }
        }
    }

    /**
     * get the ViewReportNumber with title beginning with begins
     * @param begins first character of title of the ViewReportNumber
     * @return the viewReportNumber found or null
     */
    private ViewReportNumber getConstraintView(String begins) {
        for (Pair<Integer, ViewReportNumber> reportItem : mListReportsItems)
            if (reportItem.second.getKey().startsWith(begins))
                return reportItem.second;
        return null;
    }

    /**
     * Set all the items to 0
     */
    private void onClickZero() {
        for (Pair<Integer,ViewReportNumber> vrn : mListReportsItems) {
            vrn.second.setZero();
        }
    }

    @Override
    public Loader<Cursor> onCreateLoader(final int i, final Bundle bundle) {
        CursorLoader loader = new CursorLoader(mContext,
                SesContract.Sms.CONTENT_URI,
                null,
                SesContract.getDetailsSelection(),
                SesContract.getDetailsSelectionArgs(mTypeSms, Status.DRAFT, mDisease), null);
        return loader;
    }

    @Override
    public void onLoadFinished(final Loader<Cursor> cursorLoader, final Cursor cursor) {
        if (cursorLoader.getId() == LOADER_DETAIL && cursor != null && cursor.moveToFirst()) {
            Integer smsNumber = 0;
            mLinearLayout.removeAllViews();
            mListReportsItems.clear();
            mSms.clear();
            do {
                Sms sms = new Sms(Config.getInstance(mContext), cursor);
                sms.mType = mTypeSms;
                List<Parser.ConfigField> listData = sms.mListData;
                List<Parser.Constraint> listConstraint = sms.mListConstraint ;
                addReportsItemsToScreen(listData, listConstraint, smsNumber);
                mSms.add(sms);
                smsNumber++;
            } while (cursor.moveToNext());
        }
    }

    @Override
    public void onLoaderReset(final Loader<Cursor> cursorLoader) {

    }

    @Override
    public void onStop() {
        super.onStop();
        if (dataValid()) {
            if (mSms != null && mSms.size() > 0) {
                for (int i = 0; i < mSms.size(); i++) {
                    List<Parser.ConfigField> listPair = new ArrayList<Parser.ConfigField>();
                    for (Pair<Integer, ViewReportNumber> number : mListReportsItems) {
                        if (number.first == i) {
                            listPair.add(new Parser.ConfigField(number.second.getKey(), number.second.getValue()));
                        }
                    }
                    mSms.get(i).mListData = listPair;
                    ContentValues cv = mSms.get(i).getContentValues(Config.getInstance(mContext));
                    new UpdateMessage(mContext.getContentResolver()).startUpdate(
                            12,
                            null,
                            SesContract.Sms.CONTENT_URI,
                            cv,
                            SesContract.getDetailsSelectionId(),
                            SesContract.getDetailsSelectionArgsId(mSms.get(i).mType, Status.DRAFT, mSms.get(i).mDisease, mSms.get(i).mId)
                    );
                }
            }
        }
    }

    /**
     * Check if data is valid
     * @return true if valid, false otherwise
     */
    private boolean dataValid() {
        boolean valid = true;
        for (Pair<Integer, ViewReportNumber> number : mListReportsItems) {
            if (!number.second.isValid()) {
                valid = false;
                break;
            }
        }
        return valid;
    }

    /**
     * Update message in the database
     */
    private static class UpdateMessage extends AsyncQueryHandler {


        public UpdateMessage(final ContentResolver cr) {
            super(cr);
        }

        @Override
        protected void onUpdateComplete(final int token, final Object cookie, final int result) {
            super.onUpdateComplete(token, cookie, result);
            if (BuildConfig.DEBUG && DEBUG) {
                Log.d(TAG, "onUpdateComplete result=" + result);
            }
        }
    }

    @Override
    public void onClick(final View view) {
        if (view == mButtonOk) {
            hideKeyboard();
            getActivity().onBackPressed();
        }
    }

    private void hideKeyboard() {
        for(Pair<Integer, ViewReportNumber> vrn : mListReportsItems){
            vrn.second.hideKeyboard();
        }
    }

}
