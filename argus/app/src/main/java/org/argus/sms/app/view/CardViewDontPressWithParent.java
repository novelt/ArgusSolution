package org.argus.sms.app.view;

import android.content.Context;
import android.util.AttributeSet;
import android.view.View;

/**
 * Cardview not clickable with it's parent
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class CardViewDontPressWithParent extends android.support.v7.widget.CardView {
    public CardViewDontPressWithParent(final Context context) {
        super(context);
    }

    public CardViewDontPressWithParent(final Context context, final AttributeSet attrs) {
        super(context, attrs);
    }

    public CardViewDontPressWithParent(final Context context, final AttributeSet attrs, final int defStyleAttr) {
        super(context, attrs, defStyleAttr);
    }

    @Override
    public void setPressed(boolean pressed) {
        if (pressed && getParent() instanceof View && ((View) getParent()).isPressed()) {
            return;
        }
        super.setPressed(pressed);
    }
}
