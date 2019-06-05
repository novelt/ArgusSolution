package org.argus.gateway.ui;

import android.content.Context;
import android.content.SharedPreferences;
import android.preference.DialogPreference;
import android.text.InputType;
import android.util.AttributeSet;
import android.view.View;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.EditText;

/**
 * Created by alexandre on 18/01/16.
 */
public class PasswordPreference extends DialogPreference {

    private final String    PASSWORD = "password";

    private EditText    _password;
    private CheckBox    _isShown;

    public PasswordPreference(Context context, AttributeSet attrs) {
        super(context, attrs);
        setDialogLayoutResource(org.argus.gateway.R.layout.preference_password);
    }

    @Override
    protected void onBindDialogView(View view) {
        super.onBindDialogView(view);

        _password = (EditText)view.findViewById(org.argus.gateway.R.id.txt_password);
        _isShown = (CheckBox)view.findViewById(org.argus.gateway.R.id.chk_show_password);

        SharedPreferences sharedPreferences = getSharedPreferences();

        _password.setText(sharedPreferences.getString(PASSWORD, ""));

        _isShown.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                if (isChecked) {
                    _password.setInputType(InputType.TYPE_TEXT_VARIATION_PASSWORD);
                } else {
                    _password.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);
                }
            }
        });
    }

    @Override
    protected void onDialogClosed(boolean positiveResult) {
        super.onDialogClosed(positiveResult);

        if (positiveResult) {
            SharedPreferences.Editor editor = getEditor();
            editor.putString(PASSWORD, _password.getText().toString());
            editor.commit();
        }
    }
}
