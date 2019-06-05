package org.argus.sms.app.view;

/**
 * Interface defining the different methods for a report item
 * Created by Olivier Goutet.
 * Openium 2014
 */
public interface IReportItems {

    public String getKey();
    public String getValue();
    public boolean isValid();
    public boolean isOptionnal();
    public boolean isEmpty();
    public void isWrong();
    public void setZero();
    public void setOnValueChangeListener(IReportItemsChangeListener listener);
    public void hideKeyboard();
}
