package org.argus.sms.app.smsConfig.security;

/**
 * Novel-T Encryption
 */
public class NovelTEncryption {

    private static String table_uncrypt = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_abcdefghijklmnopqrstuvwxyz{|}~";
    private static String table_crypt = "YZea7Kq\\Jp<#0)'gu[Q.D5R9s4j^E1wc/HNoynC_6(G\"dBi$TA2S}It-k3Xv8~{@U;z: >*xrM|%V=?!f,+]FhLmblPWO&";

    public static String encrypt(String str)
    {
        if (str == null || str.isEmpty()) {
            return "";
        }

        String result = "";

        for(int i = 0 ; i < str.length() ; i++) {
            char car = str.charAt(i);
            int index = table_uncrypt.indexOf(car);

            if (index >= 0) {
                result += table_crypt.charAt(index);
            } else {
                result += car;
            }
        }

        return result ;
    }

    public static String decrypt(String str)
    {
        if (str == null || str.isEmpty()) {
            return "";
        }

        String result = "";

        for(int i = 0 ; i < str.length() ; i++) {
            char car = str.charAt(i);
            int index = table_crypt.indexOf(car);

            if (index >= 0) {
                result += table_uncrypt.charAt(index);
            } else {
                result += car;
            }
        }

        return result ;
    }

}
