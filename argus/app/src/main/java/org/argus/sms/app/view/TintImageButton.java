package org.argus.sms.app.view;

import android.content.Context;
import android.graphics.Color;
import android.graphics.ColorFilter;
import android.graphics.LightingColorFilter;
import android.graphics.drawable.Drawable;
import android.graphics.drawable.LayerDrawable;
import android.util.AttributeSet;
import android.widget.ImageButton;

/**
 * ImageButton with automatic tint
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class TintImageButton extends ImageButton {

    public TintImageButton(Context context) {
        super(context);
    }

    public TintImageButton(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    public TintImageButton(Context context, AttributeSet attrs, int defStyle) {
        super(context, attrs, defStyle);
    }

    @Override
    public void setImageDrawable(Drawable d) {
        TintButtonBackgroundDrawable layer = new TintButtonBackgroundDrawable(d);
        super.setImageDrawable(layer);
    }

    /**
     * The stateful LayerDrawable used by this button.
     */
    protected class TintButtonBackgroundDrawable extends LayerDrawable {

        protected ColorFilter _pressedFilter = new LightingColorFilter(Color.LTGRAY, 1);
        protected int _disabledAlpha = 100;

        public TintButtonBackgroundDrawable(Drawable d) {
            super(new Drawable[] { d });
        }

        @Override
        protected boolean onStateChange(int[] states) {
            boolean enabled = false;
            boolean pressed = false;

            for (int state : states) {
                if (state == android.R.attr.state_enabled)
                    enabled = true;
                else if (state == android.R.attr.state_pressed)
                    pressed = true;
            }

            mutate();
            if (enabled && pressed) {
                setColorFilter(_pressedFilter);
            } else if (!enabled) {
                setColorFilter(null);
                setAlpha(_disabledAlpha);
            } else {
                setColorFilter(null);
            }

            invalidateSelf();

            return super.onStateChange(states);
        }

        @Override
        public boolean isStateful() {
            return true;
        }
    }
}
