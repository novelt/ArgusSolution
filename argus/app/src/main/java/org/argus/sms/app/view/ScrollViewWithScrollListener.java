package org.argus.sms.app.view;

import android.annotation.TargetApi;
import android.content.Context;
import android.os.Build;
import android.util.AttributeSet;
import android.widget.ScrollView;

/**
 * ScrollView with a scroll listener.
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class ScrollViewWithScrollListener extends ScrollView  {

    IScrollListener mScrollListener;

    public interface IScrollListener{
        public void onScrollChanged(final int l, final int t, final int oldl, final int oldt);
    }

    public ScrollViewWithScrollListener(final Context context) {
        super(context);
    }

    public ScrollViewWithScrollListener(final Context context, final AttributeSet attrs) {
        super(context, attrs);
    }

    public ScrollViewWithScrollListener(final Context context, final AttributeSet attrs, final int defStyleAttr) {
        super(context, attrs, defStyleAttr);
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public ScrollViewWithScrollListener(final Context context, final AttributeSet attrs, final int defStyleAttr, final int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
    }

    @Override
    protected void onScrollChanged(final int l, final int t, final int oldl, final int oldt) {
        super.onScrollChanged(l, t, oldl, oldt);
        if (mScrollListener != null){
            mScrollListener.onScrollChanged(l,t,oldl,oldt);
        }
    }

    public void setScrollListener(final IScrollListener scrollListener) {
        mScrollListener = scrollListener;
    }
}
