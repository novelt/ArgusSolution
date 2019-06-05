package org.argus.sms.app.fragment;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.Configuration;
import android.os.Bundle;
import android.preference.Preference;
import android.preference.PreferenceCategory;
import android.preference.PreferenceScreen;
import android.support.v4.preference.PreferenceFragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.EditText;
import android.widget.Toast;

import java.util.Locale;
import org.argus.sms.app.BuildConfig;
import org.argus.sms.app.R;
import org.argus.sms.app.activity.ActivitySettings;
import org.argus.sms.app.activity.ActivitySynchronization;
import org.argus.sms.app.model.Config;
import org.argus.sms.app.utils.HelperAlert;
import org.argus.sms.app.utils.HelperPreference;

import fr.openium.androkit.sharedpreference.OKSharedPreferenceHelper;

/**
 * Settings fragment
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class FragmentSettings  extends PreferenceFragment {

    private final static String TAG = ActivitySettings.class.getSimpleName();
    private final static boolean DEBUG = true;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setupSimplePreferencesScreen();
    }


    /**
     * Shows the simplified settings UI if the device configuration if the
     * device configuration dictates that a simplified, single-pane UI should be
     * shown.
     */
    private void setupSimplePreferencesScreen() {

        // In the simplified UI, fragments are not used at all and we instead
        // use the older PreferenceActivity APIs.

        // Add 'general' preferences.
        addPreferencesFromResource(R.xml.pref_general);

        // Listener for the server phone number
        findPreference(getString(R.string.prefs_sms_gateway_out)).setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {
            @Override
            public boolean onPreferenceChange(final Preference preference, final Object o) {
                if (BuildConfig.DEBUG && DEBUG) {
                    Log.d(TAG, "onPreferenceChange ");
                }
                boolean changed = true;
                String original = HelperPreference.getServerPhoneNumber(getActivity());
                String current = (String) o;
                if (original != null && current != null && current.equals(original)) {
                    changed = false;
                } else {
                    HelperPreference.clearCurrentSyncData(getActivity());
                }
                return changed;
            }
        });

        // Listener for the synchronisation button
        findPreference(getString(R.string.prefs_sync)).setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(final Preference preference) {
                Intent i = new Intent(getActivity(), ActivitySynchronization.class);
                startActivity(i);
                return true;
            }
        });

        // Listener for the language button
        findPreference(getString(R.string.prefs_language)).setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {
            @Override
            public boolean onPreferenceChange(final Preference preference, final Object o) {
                if (BuildConfig.DEBUG && DEBUG) {
                    Log.d(TAG, "onPreferenceChange ");
                }
                boolean changed = true;
                String original = HelperPreference.getLanguage(getActivity());
                String current = (String) o;
                if (original != null && current != null && current.equals(original)) {
                    changed = false;
                } else { // Change the local
                    Locale locale = new Locale(current);
                    Locale.setDefault(locale);
                    Configuration config = new Configuration();
                    config.locale = locale;
                    getActivity().onConfigurationChanged(config);
                }
                return changed;
            }
        });

        // Listener for the test mode checkbox
        findPreference(getString(R.string.prefs_test)).setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {
            @Override
            public boolean onPreferenceChange(Preference preference, Object newValue) {
                Config.IsTest = Boolean.parseBoolean(newValue.toString());

                return true;
            }
        });


        // Listener for the change password button
        findPreference(getString(R.string.prefs_settings_password)).setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(final Preference preference) {

                LayoutInflater li = LayoutInflater.from(getActivity());
                View definePassword = li.inflate(R.layout.define_password, null);
                final EditText password = (EditText) definePassword
                        .findViewById(R.id.edit_Text_Dialog_Password_settings);
                final EditText confirmPassword = (EditText) definePassword
                        .findViewById(R.id.edit_Text_Dialog_Password_settings_confirmation);

                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity())
                        .setView(definePassword)
                        .setTitle(getString(R.string.define_new_password))
                        .setNegativeButton(R.string.annuler, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                                dialog.cancel();
                            }
                        })
                        .setPositiveButton(R.string.ok, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                                //Do nothing here because we override this button later to change the close behaviour.
                                //However, we still need this because on older versions of Android unless we
                                //pass a handler the button doesn't get instantiated
                            }
                        });

                final AlertDialog dialog = builder.create();
                dialog.show();

                //Overriding the handler immediately after show
                dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(new View.OnClickListener()
                {
                    @Override
                    public void onClick(View v)
                    {
                        if (password.getText().toString().isEmpty()) {
                            Toast.makeText(getActivity(), getString(R.string.password_cannot_be_empty), Toast.LENGTH_SHORT).show();
                        } else {
                            if (!password.getText().toString().equals(confirmPassword.getText().toString())) {
                                Toast.makeText(getActivity(), getString(R.string.passwords_do_not_match), Toast.LENGTH_SHORT).show();
                                password.setText("");
                                confirmPassword.setText("");
                            } else {
                                OKSharedPreferenceHelper.saveStringInSharedPreference(getActivity(), getString(R.string.prefs_settings_password), password.getText().toString());
                                Toast.makeText(getActivity(), getString(R.string.password_changed), Toast.LENGTH_SHORT).show();
                                dialog.dismiss();
                            }
                        }
                    }
                });

                return true;
            }
       });

        // Display the Alert preference menu only if the Argus Alert application is installed on this device
        if (! HelperAlert.isArgusAlertApplicationInstalled(getContext())) {
            PreferenceScreen screen = getPreferenceScreen();
            PreferenceCategory mCategory = (PreferenceCategory) findPreference("prefs_category_config_alert_external");
            screen.removePreference(mCategory);
        }


        // Display or not Config SMS Configurations
        if (!BuildConfig.SMS_CONFIGURATION_SETTING_ENABLED) {
            PreferenceScreen screen = getPreferenceScreen();
            PreferenceCategory mCategory = (PreferenceCategory) findPreference("prefs_category_config_sms");
            screen.removePreference(mCategory);
        }
    }
}
