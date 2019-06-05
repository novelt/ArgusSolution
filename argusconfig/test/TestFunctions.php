<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 1/22/2016
 * Time: 4:26 PM
 */

    require_once('../tools/common.php');
    require_once('../tools/epidemiology.php') ;
    require_once('../config/globals.php');
    require_once('../tools/Utils/Constant.php');


   /* echo date("d F Y H:i:s", Epi2Timestamp2(2016, 1));
    echo ("<br>");
    echo ("<br>");
    echo date("d F Y H:i:s", Epi2Timestamp(2016, 1));*/
    $Year = 2016 ;
    $Week = 18 ;

    echo date("d F Y H:i:s", Epi2Timestamp($Year, $Week));
    echo ("<br>");
    echo date("Y-m-d H:i:s", Epi2Timestamp($Year, $Week));
    echo ("<br>");
    echo date("Y-m-d H:i:s");
    echo ("<br>");
    echo Epi2Timestamp($Year, $Week) ;
    echo ("<br>");
    echo "Year : ";
    echo Timestamp2Epi(Epi2Timestamp($Year, $Week))['Year'];
    echo ("<br>");
    echo "Week : ";
    echo Timestamp2Epi(Epi2Timestamp($Year, $Week))['Week'];

   /* echo ("<br>");
        for($i=0 ; $i<=30; $i++)
        {
            echo ($i." : ");
            echo jddayofweek($i, 1);
            echo ("<br>");
        }
       */

    /*echo ("<br>");
    for($i=1 ; $i<=7; $i++)
    {
        echo ($i." : ");
        echo GetDayOfWeek($i);
        echo ("<br>");
    }*/
    echo ("<br>");
    echo Constant::existSpecialCharacter('testë') ? "true" : "false" ;
    echo ("<br>");
    echo round(150 / 2.3);
    echo ("<br>");
    echo 150 / 2.3;

    echo ("<br>");
    if (!function_exists("gettext"))
    {
        echo "gettext not installed";
    }
    else {
        echo "gettext installed";
    }
    echo ("<br>");
    echo ("<br>");

    echo "****************** SMS CONFIG ********************";
    echo "****************** COPY / PASTE the HTML data, not what you see in the browser ********************";

    echo ("<br>");
    //$stringToEncode = "AVDCFG#354859076123604#4567894#SET#server_url#http://avadar.afro.who.int/sesdashboard/web/services/openrosa/";
    //$stringToEncode = "AVDCFG#354859076123604#4567894#GET#data_transfer_pathway";
    //$hash = hash_hmac("md5","3548590761236044567894SETserver_urlhttp://avadar.afro.who.int/sesdashboard/web/services/openrosa/", "Novel-tSecurityKey");
    //$hash = hash_hmac("md5","3548590761236044567894GETdata_transfer_pathway", "Novel-tSecurityKey");

    //$stringToEncode = "AVDCFG#354859076123604#1#SET#sms_gateway#+41754139318";
    //$hash = hash_hmac("md5","3548590761236041SETsms_gateway+41754139318", "Novel-tSecurityKey");

    //$prefix = "ARGCFG#";
    //$stringToEncode = "353913081514520#9#SET#alert_external#false";
    //$hash = hash_hmac("md5","3539130815145209SETalert_externalfalse", "ArG*sS3cur1ty"); //ArG*sS3cur1ty

    //$stringToEncode = "AVDCFG#357871053616010#1#SET#server_url#http://google.com";
    //$hash = hash_hmac("md5","3578710536160101SETserver_urlhttp://google.com", "AvAD*rS3cur1ty");

    //$stringToEncode = "AVDCFG#357871053616010#1#SET#notification_schedule#0 11 * * 4";
    //$hash = hash_hmac("md5","3578710536160101SETnotification_schedule0 11 * * 4", "AvAD*rS3cur1ty");

    //$stringToEncode = "AVDCFG#357871053616010#1#SET#own_phone_number#+41795006507";
    //$hash = hash_hmac("md5","3578710536160101SETown_phone_number+41795006507", "AvAD*rS3cur1ty");

    $prefix = "AVDCFG#";
    $stringToEncode = "353913081514520#1#SET#server_url#http://google.com";
    $hash = hash_hmac("md5","3539130815145201SETserver_urlhttp://google.com", "AvAD*rS3cur1ty"); //ArG*sS3cur1ty

    //echo($hash);
    //echo ("<br>");
    //echo(novelt_crypt($hash));
    //echo ("<br>");
    //echo(novelt_decrypt(novelt_crypt($hash)));
    //echo ("<br>");
    $stringToEncode.= "#".$hash."#";
    echo("Config SMS : ");
    echo(htmlspecialchars($prefix.$stringToEncode));
    echo ("<br>");
    $noveltCrypt = novelt_crypt($stringToEncode);
    echo ("Config SMS Encrypted : ");
    echo ($prefix.$noveltCrypt);
    echo ("<br>");
    $noveltdecrypt = novelt_decrypt($noveltCrypt);
    echo ("Config SMS Decrypted : ");
    echo (htmlspecialchars($prefix.$noveltdecrypt));
    echo ("<br>");

    echo ("Config Ack SMS : ");
    echo(htmlspecialchars(novelt_decrypt(".5.4[.us[5[D5Qua[a$\"au"))); // AVDACK#sR9s[4uQ[.DRRu[a[a$"au
    echo ("<br>");
    //echo ("<br>");
    //echo(hash_hmac ( "md5", $stringToEncode , "MACLEF"));
    //echo ("<br>");

//echo (sprintf("%02d", 1));
//echo (sprintf("%02d", 9));
//echo (sprintf("%02d", 20));



    //echo ("<br>");
    //$test = xor_string($stringToEncode64, "MACLEFDEOUFSAMEERELAPUTE");

    //$original = xor_string($test, "MACLEFDEOUFSAMEERELAPUTE");

    //echo(novelt_decrypt(".5Ds54u9R[Q.RuDaD5R9s4DaSn}a+ ,h ,@F,%ax]]!jggUhU:U,'U>,?'Lx?'r=]g+ +:U+x;?U,:gL ;g+ ,hrz +g?! =,?+Ugas>>:DD;UsU4uD> ;9..Q54.D.D4>RD;a"));


function xor_string($string, $key) {
    for($i = 0; $i < strlen($string); $i++)
        $string[$i] = ($string[$i] ^ $key[$i % strlen($key)]);
    return $string;
}

function rot18($str) {
    $a = ord('a');
    $A = ord('A');
    $zero = ord('0');
    $quatre = ord('4');
    $neuf = ord('9');

    for ($i = 0, $len = strlen($str); $i < $len; $i++) {
        $asciiCode = ord($str[$i]);

        // Not a letter, keep what it is
        if ($asciiCode >= $zero && $asciiCode <= $quatre ) {
            $str[$i] = chr($asciiCode + 5);
        } else if ($asciiCode > $quatre && $asciiCode <= $neuf ) {
            $str[$i] = chr($asciiCode - 5);
        } else if ($asciiCode < $A || $asciiCode >= $a + 26 || ($asciiCode >= $A + 26 && $asciiCode < $a)) {
            continue;
        } else {
            // To check if should use the alphabet with upper or lower case letters
            $initialLetterOfAlphabet = $asciiCode < $a ? $A : $a;
            $str[$i] = chr(($asciiCode - $initialLetterOfAlphabet + 13) % 26 + $initialLetterOfAlphabet);
        }
    }

    return $str;
}

function novelt_crypt($str) {

    $table_uncrypt = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_abcdefghijklmnopqrstuvwxyz{|}~";
    $table_crypt = "YZea7Kq\\Jp<#0)'gu[Q.D5R9s4j^E1wc/HNoynC_6(G\"dBi\$TA2S}It-k3Xv8~{@U;z: >*xrM|%V=?!f,+]FhLmblPWO&";

    $result = "";

    for ($i = 0, $len = strlen($str); $i < $len; $i++) {
        $char = $str[$i];
        $index = strrpos($table_uncrypt, $char);
        if (false !== $index) { // found it
            $result.=$table_crypt[$index];
        } else {
            $result.=$char;
        }
    }

    return $result ;

   /* echo($table);
    echo("<br>");
    echo($table_novelt);
    echo("<br>");

    echo(strlen($table));
    echo("<br>");
    echo(strlen($table_novelt));
    echo("<br>");*/

}

function novelt_decrypt($str) {
    /*for ($i=0 ; $i <=127 ; $i++) {
        $tableAscii[$i] = chr($i);
        echo($tableAscii[$i]);
    }*/

    $table_uncrypt = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_abcdefghijklmnopqrstuvwxyz{|}~";
    $table_crypt = "YZea7Kq\\Jp<#0)'gu[Q.D5R9s4j^E1wc/HNoynC_6(G\"dBi\$TA2S}It-k3Xv8~{@U;z: >*xrM|%V=?!f,+]FhLmblPWO&";

    $result = "";

    for ($i = 0, $len = strlen($str); $i < $len; $i++) {
        $char = $str[$i];
        $index = strrpos($table_crypt, $char);
        if (false !== $index) { // found it
            $result.=$table_uncrypt[$index];
        } else {
            $result.=$char;
        }
    }

    return $result ;

}


?>