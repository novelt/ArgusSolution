package org.argus.sms.app.utils;

import android.content.Context;
import android.view.View;
import android.widget.LinearLayout;

import java.util.List;

import org.argus.sms.app.model.Status;
import org.argus.sms.app.model.SubTypeSms;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.view.ViewReportItemSummary;
import org.argus.sms.app.view.ViewReportItemSummaryHistory;
import org.argus.sms.app.view.ViewReportItemSummaryReport;

/**
 * Helper class for reports and history
 *
 * Created by Olivier Goutet.
 * Openium 2014
 */
public class HelperReport {

    /**
     * Add an item in the container
     * @param report true if a report, false if a history
     * @param activityContext activity context
     * @param itemsContainer Container for items
     * @param title Title to display
     * @param items list of items to display
     * @param status Status of the item
     * @param tag tag to save
     * @param listener Click listener
     */
    public static void addItem(boolean report, Context activityContext, LinearLayout itemsContainer, String title, List<Parser.ConfigField> items, Status status,SubTypeSms subType,String smsConfirm, String tag, View.OnClickListener listener) {
        String text = buildTextFromAdditionnalValues(items);
        ViewReportItemSummary vris;
        if (report) {
            vris = new ViewReportItemSummaryReport(activityContext, title, text, status);
        } else {
            vris = new ViewReportItemSummaryHistory(activityContext, title, text, status, subType, smsConfirm);
        }
        vris.getChildAt(0).setTag(tag);
        LinearLayout.LayoutParams ll = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        itemsContainer.addView(vris, ll);
        vris.getChildAt(0).setOnClickListener(listener);
    }

    /**
     * Create the text from the list of items
     * @param items list of items to display
     * @return the text
     */
    public static String buildTextFromAdditionnalValues(final List<Parser.ConfigField> items) {
        StringBuilder sb = new StringBuilder();
        for (Parser.ConfigField pair : items) {
                sb.append(pair.Name);
                sb.append(" ");
                if (pair.Type.equals(String.valueOf(Integer.MIN_VALUE)) || pair.Type.isEmpty()) {
                    sb.append("-");
                } else {
                    sb.append(pair.Type);
                }
                sb.append(" ");
        }
        if (sb.length() > 0) {
            sb.setLength(sb.length() - 1);
        }
        return sb.toString();
    }

    /**
     * Check if items are all valid
     * @param items list of items to check
     * @return true if items valid, false otherwise
     */
    public static boolean areDataValid(List<Parser.ConfigField> items) {

        for (Parser.ConfigField pair : items) {
            if (pair.Type.equals(String.valueOf(Integer.MIN_VALUE)) || pair.Type == null) {
                return false;
            }
        }
        return true;
    }
}
