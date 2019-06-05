package org.argus.sms.app;

/**
 * Created by alexandre on 22/01/16.
 */
public class ConfigTest {
    public static final String[] SYNCH_SMS = {"TA: ALERT EVENT=Str,DATE=Dat,PLACE=Str 0,CASES=Int 0,HOSPITALIZED=Int 0,DEATH=Int 0",
           // "TA: ALERT HOSPITALISATION=Int 0,DECES=Int 0,DECES<=CAS",
            "TW: REPORT DISEASE=MEAS,LBL=Measles,YEAR=Int,WEEK=Int,CASES,DEATH",
            "TW: REPORT DISEASE=MENI,LBL=Meningitis,YEAR=Int,WEEK=Int,CASES,DEATH",
            "TW: REPORT DISEASE=CHOL,LBL=Cholera,YEAR=Int,WEEK=Int,CASES,DEATH",
            "TW: REPORT DISEASE=DIAR,LBL=Diarrhoea,YEAR=Int,WEEK=Int,CASES,DEATH",
            "TM: REPORT DISEASE=YEFE,LBL=Yellow fever,YEAR=Int,MONTH=Int,CASES,DEATH",
            "TM: REPORT DISEASE=RAGE,LBL=Rage,YEAR=Int,MONTH=Int,CASES,DEATH",
            "TM: REPORT DISEASE=MALA,LBL=Malaria,YEAR=Int,MONTH=Int,CASES,DEATH",
            "TM: REPORT DISEASE=DIPH,LBL=Diphteria,YEAR=Int,MONTH=Int,CASES,DEATH",
           // "TW: REPORT DISEASE=COQUELUCHE,ZOMBIES,TATATA,TITITITITI,TOTOd,TUTUTUTUTU",
           // "TW: REPORT DISEASE=COQUELUCHE,asd,fasgq,dfureu,sdgwee,zikrtketw,DE>=Z,DE<=C,sdgwee!=zikrtketw",
           // "TW: REPORT DISEASE=MAPI,LBL=Diarrhée grave,YEAR=Int,WEEK=Int,CASES,DEATH",
           // "TM: REPORT DISEASE=MENINGITE,LBL=Méningite,YEAR=Int,MONTH=Int,CASES=Int,DECES=Int,DECES<=CASES",
            //"TM: REPORT DISEASE=COQUELUCHE,LBL=Diarrhée grave,YEAR=Int,MONTH=Int,CASES=Int,DECES=Int",
            "CF: M4ConfAlert=The alert confirmation wasn't received, please contact our manager",
            "CF: M4ConfW=The week report confirmation wasn't received, please contact our manager",
            "CF: M4ConfM=The month report confirmation wasn't received, please contact our manager",
            "CF: HFName=CS Dédé Makouba",
            "CF: NbMsg=14,NbCharMax=150, Server=+41796925547/+41754139314, WeekStart=1, D4ConfAlert=5, D4ConfW=6, D4ConfM=7"};

        // Before reduction :
        // ANDROIDID=219 TEMPLATE-WEEKLY: REPORT DISEASE=MENINGITE , WEEK=Integer , CAS=Integer , DECES=Integer , RESSUSCITE=Integer
        // After reduction
        // ANDROIDID=219 TW: REPORT DISEASE=MENINGITE,WEEK,CAS,DECES,RESSUSCITE

    public static final String ANDROIDID = "ANDROIDID=";
}
