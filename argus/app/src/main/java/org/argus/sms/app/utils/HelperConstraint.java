package org.argus.sms.app.utils;

import android.util.Pair;

import org.argus.sms.app.R;
import org.argus.sms.app.parser.Parser;
import org.argus.sms.app.view.ViewReportNumber;

import java.util.ArrayList;

/**
 * Created by alexandre on 11/02/16.
 */
public class HelperConstraint {

    // Library of constraints, used in corelation with the Constraint Enum,
    // the order is important because superior_equals contain superior...
    public final static String[] ConstraintLibrary = {">=", "<=", ">", "<",  "!="};

    // Just used to build error toast
    public final static int[] ConstraintName = {R.string.superior_equals,
            R.string.inferior_equals, R.string.superior, R.string.inferior,  R.string.different};

    // Constaint Enum better to test constraint in a switch
    public static enum Constraint {
        SUPERIOR_EQUALS(0),
        INFERIOR_EQUALS(1),
        SUPERIOR(2),
        INFERIOR(3),
        DIFFERENT(4);

        private final int value;

        private Constraint(int value) {
            this.value = value;
        }

        public int getValue() {
            return value;
        }
    }

    /**
     * Test if the testString is a constraint or not
     * @param testString the string to test
     * @return true if a constraint is found in th testString. False in other cases.
     */
    public static boolean isConstraint(String testString) {
        for (String constraint : ConstraintLibrary) {
            if (testString.contains(constraint))
                return true;
        }
        return false;
    }

    /**
     * Return a Constraint Enum representing the constraint in constraintString
     * @param constrainString the string where the constraint must be found
     * @return Constraint Enum, null in other cases.
     */
    public static Constraint getConstraint(String constrainString) {
        int constraintEnum = 0;
        for (String constraint : ConstraintLibrary) {
            if (constrainString.contains(constraint))
                return Constraint.values()[constraintEnum];
            constraintEnum++;
        }
        return null;
    }

    /**
     * This function fill the constraint field of vrnTarget and vrnLinked (eg: vrnTarget > vrnLinked...)
     * @param vrnTarget the constraint first parameter
     * @param vrnLinked the constraint second parameter
     * @param constraint the constraint
     */
    public static void AddConstraintInViewNumber(ViewReportNumber vrnTarget, ViewReportNumber vrnLinked, Constraint constraint) {
        vrnTarget.addConstraint(constraint, vrnLinked);
        // Reverse the constraint to fill the second view
        vrnLinked.addConstraint(ReverseConstraint(constraint), vrnTarget);
    }

    /**
     * revert a constraint
     * @param c the constraint to revert
     * @return the reversed constraint (eg: SUPERIOR become INFERIOR...)
     */
    public static Constraint ReverseConstraint(Constraint c) {
        switch (c) {
            case INFERIOR_EQUALS:
                return Constraint.SUPERIOR_EQUALS;
            case SUPERIOR_EQUALS:
                return Constraint.INFERIOR_EQUALS;
            case INFERIOR:
                return Constraint.SUPERIOR;
            case SUPERIOR:
                return Constraint.INFERIOR;
            default:
                return Constraint.DIFFERENT;
        }
    }

    /**
     * Use this function from a ViewReportNumber to know if all constraints are respected
     * @param number the main integer in the field of the ViewReportNumber
     * @param constraints list of all constraint attached to the ViewReportNumber
     * @return true if all constraint are respected, false in all other cases
     */
    public static boolean ConstraintsAreRespected(String number, ArrayList<Pair<Constraint, ViewReportNumber>> constraints) {
        for (Pair<Constraint, ViewReportNumber> pair : constraints) {
            if (!ConstraintIsRespected(number, pair.second.getValue(), pair.first))
                return false;
        }
        return true;
    }

    /**
     * Use this function from @ConstraintsAreRespected to know if a specific constraint is respected
     * @param number the main integer in the field of the ViewReportNumber
     * @param constFieldValue integer of the constraint field with
     * @param c the actual constraint
     * @return true if the constraint is respected, else false.
     */
    public static boolean ConstraintIsRespected(String number, String constFieldValue, Constraint c) {
        int nb = 0;
        int constFieldNb = 0;

        try { nb = Integer.valueOf(number);}
        catch (NumberFormatException ex){}

        try { constFieldNb = Integer.valueOf(constFieldValue);}
        catch (NumberFormatException ex){}

        switch (c) {
            case DIFFERENT:
                return nb != constFieldNb;
            case INFERIOR:
                return nb < constFieldNb;
            case SUPERIOR:
                return nb > constFieldNb;
            case INFERIOR_EQUALS:
                return nb <= constFieldNb;
            case SUPERIOR_EQUALS:
                return nb >= constFieldNb;
            default:
                return true;
        }
    }
}
