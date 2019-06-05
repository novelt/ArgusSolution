/**
 * Created by eotin on 7/28/2015.
 */

/** General **/
function displaySuccessMessage(message)
{
    $.alert(message, {
        title: false,
        closeTime: 3000,
        autoClose: true,
        position: ['center'],
        withTime: false,
        type: 'success',
        isOnly: true
    });
}

function displayErrorMessage(message)
{
    $.alert(message, {
        title: false,
        closeTime: 3000,
        autoClose: true,
        position: ['center'],
        withTime: false,
        type: 'danger',
        isOnly: true
    });
}

var myApp;
myApp = myApp || (function () {
        var pleaseWaitDiv = $('<div class="modal hide" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false"></div>');
        return {
            showPleaseWait: function() {
                pleaseWaitDiv.modal();
            },
            hidePleaseWait: function () {
                pleaseWaitDiv.modal('hide');
            },

        };
    })();


/****** General functions *******/
function encodeString(str)
{
    return encodeURIComponent(str).replace("%20", '+', 'g');
}

function DisplayReportMenu(e, chartIcon, nodeId)
{
    argusContextualMenu.displayMenu(e, chartIcon, nodeId);
    return false ;
}


/**** ContextMenu ******/
var argusContextualMenu ;
function ArgusContextMenu()
{
    this.treeView = $("#tree");
    this.currentNode;

    this.modalReport = $("#myModal");
    this.modalReportClose = $("#myModalClose");
    this.baseUrl;
    this.reportingSiteUrl;
    this.reportingDashboardUrl;
    this.pdfDashboardUrl;

    this.dashboardUrl;

    //Diseases
    this.diseasesList;

    // Context Menu Weekly
    this.contextMenuWeekly = $("#contextMenuWeekly");
    //Context Menu Monthly
    this.contextMenuMonthly = $("#contextMenuMonthly");

    // Argus Report entries
    // Weekly Reports
    this.aSendWeeklyCompletenessTimelinessBySite = $("#aSendWeeklyCompletenessTimelinessBySite");
    this.aSendWeeklyCompletenessTimelinessByWeek = $("#aSendWeeklyCompletenessTimelinessByWeek");
    this.aValidateWeeklyCompletenessTimelinessBySite = $("#aValidateWeeklyCompletenessTimelinessBySite");
    this.aValidateWeeklyCompletenessTimelinessByWeek = $("#aValidateWeeklyCompletenessTimelinessByWeek");
    this.dividerWeeklyReport = $("#dividerWeeklyReport");

    // Monthly Reports
    this.aSendMonthlyCompletenessTimelinessBySite = $("#aSendMonthlyCompletenessTimelinessBySite");
    this.aSendMonthlyCompletenessTimelinessByMonth = $("#aSendMonthlyCompletenessTimelinessByMonth");
    this.aValidateMonthlyCompletenessTimelinessBySite = $("#aValidateMonthlyCompletenessTimelinessBySite");
    this.aValidateMonthlyCompletenessTimelinessByMonth = $("#aValidateMonthlyCompletenessTimelinessByMonth");
    this.dividerMonthlyReport = $("#dividerMonthlyReport");

    // Custom report entries
    this.aCompletenessTimelinessWeekly = $("#aCompletenessTimelinessWeekly");
    this.aCompletenessTimelinessByWeek = $("#aCompletenessTimelinessByWeek");
    this.aCompletenessTimelinessMonthly = $("#aCompletenessTimelinessMonthly");
    this.aCompletenessTimelinessByMonth = $("#aCompletenessTimelinessByMonth");
    this.aDiseasesWeekly = $("#aDiseases");
    this.aDiseasesMonthly = $("#aDiseasesMonthly");

    this.aResumeWeekly = $("#aResumeWeekly");

    // Php reports
    this.aContactList = $("#aContactList");
    this.aAlertRecipientList = $("#aAlertRecipientList");

    // SMS reports
    this.dividerSMSTraffic = $("#dividerSMSTraffic");
    this.aWeeklySMSTraffic = $("#aWeeklySMSTraffic");
    this.aMonthlySMSTraffic = $("#aMonthlySMSTraffic");

    // Sous menu for cases report to select diseases
    this.btnWeeklyDiseases = $("#btnWeeklyDiseases");
    this.btnMonthlyDiseases = $("#btnMonthlyDiseases");

    // Menu for selecting week & year
    this.btnResumeWeekly = $("#btnResumeWeekly");

    this.modalMenuForWeeklyDisease = $("#modalWeeklyDiseases");
    this.modalViewReportWeekly = $("#modalViewReportWeekly");
    this.modalMenuForMonthlyDisease = $("#modalMonthlyDiseases");
    this.modalViewReportMonthly = $("#modalViewReportMonthly");

    this.modalMenuForWeeklyResume = $("#modalWeeklyResume");
    this.modalViewWeeklyResume = $("#modalViewWeeklyResume");
    this.modalDownloadWeeklyResume = $("#modalDownloadWeeklyResume");

    this.optResumeWeeklyWeek = $("#optResumeWeeklyWeek") ;
    this.optResumeWeeklyYear = $("#optResumeWeeklyYear") ;

    // translation words
    this.siteW;
    this.periodW;
    this.fromW;
    this.toW;
    this.fromWeekW;
    this.toWeekW;
    this.fromMonthW;
    this.toMonthW;
    this.weekW;

    this.init = function(baseUrl, reportingSiteUrl, reportingDashboardUrl, pdfDashboardUrl ){
        this.baseUrl = baseUrl;
        this.reportingSiteUrl = reportingSiteUrl;
        this.reportingDashboardUrl = reportingDashboardUrl;
        this.pdfDashboardUrl = pdfDashboardUrl;
        this.initMenuEntries();
        this.setWeeklyResumeLabels();

        var _self = this;

        $(document).click(function () {
            _self.hideMenus();
        });

        this.modalReportClose.bind('click', function(){
            _self.hideIframeReport();
        });
    };

    this.initDiseases = function (urlWebApiDiseases)
    {
        var _self = this ;

        $.get(urlWebApiDiseases, function(data){
            _self.diseasesList = data.diseases;
        });
    };

    this.initDashboard = function (urlDashboard){
        this.dashboardUrl = urlDashboard ;
    };

    this.initTranslationWords = function(siteW, periodW, fromW, toW, fromWeekW, toWeekW, fromMonthW, toMonthW,  weekW ) {
        this.siteW = siteW;
        this.periodW = periodW;
        this.fromW = fromW;
        this.toW = toW;
        this.fromWeekW = fromWeekW;
        this.toWeekW = toWeekW;
        this.fromMonthW = fromMonthW;
        this.toMonthW = toMonthW;
        this.weekW = weekW;
    };

    this.hideMenus = function(){
        this.contextMenuWeekly.hide();
        this.contextMenuMonthly.hide();
    };

    this.displayMenu = function (e, icon, nodeId){
        if (argusContext.getPeriod() == 'Weekly'){
            this.displayContextMenu(e, icon, nodeId, this.contextMenuWeekly)
        }
        else {
            this.displayContextMenu(e, icon, nodeId, this.contextMenuMonthly)
        }

    };

    this.displayContextMenu = function (e, icon, nodeId, contextMenu){
        this.initNodeElement(nodeId);

        contextMenu.css({
            left: icon.position().left + 55,
            top: e.pageY - icon.position().top - 120
        });

        // Enable , Disable weekly report regarding Export Data right
        var exportRight = this.currentNode.export;

        if (exportRight == true){
            this.aResumeWeekly.show();
        }
        else{
            this.aResumeWeekly.hide();
        }

        // Activate / Deactivate validation reports on leaf nodes
        var level = this.currentNode.level;
        if (level == 'HF'){
            this.aValidateWeeklyCompletenessTimelinessBySite.hide();
            this.aValidateWeeklyCompletenessTimelinessByWeek.hide();
            this.aValidateMonthlyCompletenessTimelinessBySite.hide();
            this.aValidateMonthlyCompletenessTimelinessByMonth.hide();
            this.dividerWeeklyReport.hide();
            this.dividerMonthlyReport.hide();
        }
        else {
            this.aValidateWeeklyCompletenessTimelinessBySite.show();
            this.aValidateWeeklyCompletenessTimelinessByWeek.show();
            this.aValidateMonthlyCompletenessTimelinessBySite.show();
            this.aValidateMonthlyCompletenessTimelinessByMonth.show();
            this.dividerWeeklyReport.show();
            this.dividerMonthlyReport.show();
        }

        contextMenu.show();
    };

    this.initNodeElement = function(nodeId){
        this.currentNode = this.treeView.treeview('getNode',nodeId );
    };

    this.hideIframeReport = function(){
        this.modalReport.modal({show:false});
    };

    this.displayIframeReport = function(siteId, rapport, title, name, reportType, diseasesIds, exportRight){

        var urlReport = rapport;

        //get the selected range start - end date
        var startDate = argusContext.getStartDate();
        var endDate = argusContext.getEndDate();

        // Calculate Week numbers & Years
        var weekNumberFrom = argusContext.getWeekNumber(startDate);
        var weekNumberTo = argusContext.getWeekNumber(endDate);
        // Case When startDate of first week of year begin in previous year. (29/12/2014 is first day for 2015 first week)
        var yearFromWeekly =  argusContext.getRectifiedYear(startDate);
        var yearFromMonthly =  argusContext.getYear(startDate);

        // Case When startDate of week of year ends in next year.
        var yearToMonthly = argusContext.getYear(endDate);
        var yearToWeekly = argusContext.getRectifiedYear(endDate);
        var monthFrom = argusContext.getMonthName(startDate);
        var monthTo = argusContext.getMonthName(endDate);

        // To fill DateRange filter
        var rangeDate = '&macros[Range][]=' + startDate + '&macros[Range][]=' + endDate;

        //Specific case for Diseases reports, rewrite title with selected diseases
        var formatTitle = this.formatTitle(title, diseasesIds, reportType);

        var period = '&macros[Period]=' + argusContext.getPeriod();
        var site = '&macros[Site]=' + siteId;
        var diseases = '&macros[Diseases]=' + diseasesIds;
        var locale = '&macros[Locale]=' + argusContext.getLocale();
        var reportName = '&reportname=' + encodeString(formatTitle);
        //var lang = '&lang=' + argusContext.getLocale();
        var exportEnabled = '&export=' + exportRight;

        var reportDetails = '';
        if (reportType == 'Weekly') {
            reportDetails = '[' + name + ' : ' + this.fromWeekW + ' <b>' + this.weekW + ' ' + weekNumberFrom + ' - ' + yearFromWeekly + ' </b>' + this.toWeekW + ' <b>' + this.weekW + ' ' + weekNumberTo + ' - ' + yearToWeekly + '</b>' + ']';
        }
        else if(reportType == 'Monthly') {
            reportDetails = '[' + name + ' : ' + this.fromMonthW + ' <b>' + monthFrom + ' - ' + yearFromMonthly + ' </b>' + this.toMonthW + ' <b>' + monthTo + ' - ' + yearToMonthly + '</b>' + ']' ;
        }

        this.modalReport.find('#reportTitle').html(formatTitle);
        this.modalReport.find('#reportSite').html(this.siteW + ' : ' + name);
        this.modalReport.find('#reportPeriod').html(this.periodW + ' : ' + this.fromW + ' ' + moment(startDate).format('L') + ' ' + this.toW + ' ' + moment(endDate).format('L'));
        this.modalReport.find('#fullTitle').html(formatTitle);
        this.modalReport.find('#fullTitleDetails').html(reportDetails);

        var reportDetailsParam = '&reportdetails=' + encodeString(reportDetails);
        //var urlGlobal = this.baseUrl + this.reportingSiteUrl + urlReport + rangeDate + period + site + diseases + reportName + reportDetailsParam + exportEnabled + lang;
        var urlGlobal = this.baseUrl + this.reportingSiteUrl + urlReport + rangeDate + period + site + diseases + locale + reportName + reportDetailsParam + exportEnabled ;

        this.modalReport.find('iframe').attr("src", urlGlobal);
        this.modalReport.modal({show:true});
    };

    this.loadReport = function(title, report, period){
        var siteId = this.currentNode.href;
        var exportRight = this.currentNode.export;
        var name = this.currentNode.text ;

        // Get Diseases selected
        var diseases;
        if (period == 'Weekly'){
            diseases = this.getSelectedDiseases(this.modalMenuForWeeklyDisease);
        }
        else if (period == 'Monthly'){
            diseases = this.getSelectedDiseases(this.modalMenuForMonthlyDisease);
        }

        this.displayIframeReport(siteId, report, title, name, period, diseases, exportRight);
    };

    this.loadDashboard = function(download){
        var urlDashboard = this.dashboardUrl;

        urlDashboard = urlDashboard.replace('HREF',this.currentNode.href);
        urlDashboard = urlDashboard.replace('PERIOD_TIME',argusContext.getPeriod());
        urlDashboard = urlDashboard.replace('WEEK_NUMBER',this.getSelectedResumeWeeklyWeek());
        urlDashboard = urlDashboard.replace('MONTH_NUMBER',0);
        urlDashboard = urlDashboard.replace('YEAR',this.getSelectedResumeWeeklyYear());

        var _self = this;

        $.get(urlDashboard, function(data){

            _self.modalReport.find('#reportTitle').html(data.reportTitle);
            _self.modalReport.find('#reportSite').html(_self.siteW + ' : ' + data.siteName);
            _self.modalReport.find('#reportPeriod').html(_self.periodW + ' : ' + data.period);
            _self.modalReport.find('#fullTitle').html(data.reportTitle);
            _self.modalReport.find('#fullTitleDetails').html(data.reportDetails);

            var url = "";

            if (download) {
                //url = _self.baseUrl + _self.reportingDashboardUrl + data.pathReport;
                url = _self.baseUrl + _self.pdfDashboardUrl + data.pathReport + '?contentDisposition=attachment&title='+ _self.url_encode(data.reportDetails);
                _self.modalReport.find('iframe').attr("src", url);
                //_self.modalReport.modal({show:true});
            }
            else {
                url = _self.baseUrl + _self.pdfDashboardUrl + data.pathReport + '?contentDisposition=inline&title='+ _self.url_encode(data.reportDetails);
                var win = window.open(url, '_blank');
            }
        });
    };

    this.url_encode = function ( url ) {
        return url.split( '' ).map(function ( c ) {
            return /[ÀÈÉÊàèéêë]/.test( c ) ? '%' + c.charCodeAt( 0 ).toString( 16 ).toUpperCase() : c;
        }).join( '' );
    };

    this.initMenuEntries = function(){

        var _self = this;

        // Argus Weekly Reports
        this.aSendWeeklyCompletenessTimelinessBySite.bind('click', function(){
            _self.loadReport($(this).html(), "SendCompletenessTimelinessBySite.php", "Weekly");
        });

        this.aSendWeeklyCompletenessTimelinessByWeek.bind('click', function(){
            _self.loadReport($(this).html(), "SendCompletenessTimelinessByPeriod.php", "Weekly");
        });

        this.aValidateWeeklyCompletenessTimelinessBySite.bind('click', function(){
            _self.loadReport($(this).html(), "ValidateCompletenessTimelinessBySite.php", "Weekly");
        });

        this.aValidateWeeklyCompletenessTimelinessByWeek.bind('click', function(){
            _self.loadReport($(this).html(), "ValidateCompletenessTimelinessByPeriod.php", "Weekly");
        });

        this.aWeeklySMSTraffic.bind('click', function() {
            _self.loadReport($(this).html(), "SMSTraffic.php", "Weekly");
        });

        // this.aMonthlySMSTraffic.bind('click', function() {
        //     _self.loadReport($(this).html(), "SMSTraffic.php", "Monthly");
        // });

        // Argus Monthly Reports
        this.aSendMonthlyCompletenessTimelinessBySite.bind('click', function(){
            _self.loadReport($(this).html(), "SendCompletenessTimelinessBySite.php", "Monthly");
        });

        this.aSendMonthlyCompletenessTimelinessByMonth.bind('click', function(){
            _self.loadReport($(this).html(), "SendCompletenessTimelinessByPeriod.php", "Monthly");
        });

        this.aValidateMonthlyCompletenessTimelinessBySite.bind('click', function(){
            _self.loadReport($(this).html(), "ValidateCompletenessTimelinessBySite.php", "Monthly");
        });

        this.aValidateMonthlyCompletenessTimelinessByMonth.bind('click', function(){
            _self.loadReport($(this).html(), "ValidateCompletenessTimelinessByPeriod.php", "Monthly");
        });

        // Custom Reports
        this.aCompletenessTimelinessWeekly.bind('click', function(){
            //_self.loadReport($(this).html(), "TimelinessCompletenessByHealthFacilityWeekly.sql", "TimelinessCompletenessByDistrictWeekly.sql", "Weekly");
            //_self.loadReport($(this).html(), "WeeklyTimelinessCompletenessBySite.php", "Weekly");
            _self.loadReport($(this).html(), "SendCompletenessTimelinessBySite.php", "Weekly");
        });

        this.aCompletenessTimelinessByWeek.bind('click', function(){
            //_self.loadReport($(this).html(), "TimelinessCompletenessByWeekByHealthFacility.sql", "TimelinessCompletenessByWeekByDistrict.sql", "Weekly");
            // _self.loadReport($(this).html(), "WeeklyTimelinessCompletenessByWeek.php", "Weekly");
            _self.loadReport($(this).html(), "SendCompletenessTimelinessByPeriod.php", "Weekly");
        });

        this.aCompletenessTimelinessMonthly.bind('click', function(){
            // _self.loadReport($(this).html(), "TimelinessCompletenessByHealthFacilityMonthly.sql", "TimelinessCompletenessByDistrictMonthly.sql", "Monthly");
            //_self.loadReport($(this).html(), "MonthlyTimelinessCompletenessBySite.php", "Monthly");
            _self.loadReport($(this).html(), "SendCompletenessTimelinessBySite.php", "Monthly");
        });

        this.aCompletenessTimelinessByMonth.bind('click', function(){
            //_self.loadReport($(this).html(), "TimelinessCompletenessByMonthByHealthFacility.sql", "TimelinessCompletenessByMonthByDistrict.sql", "Monthly");
            //_self.loadReport($(this).html(), "MonthlyTimelinessCompletenessByMonth.php", "Monthly");
            _self.loadReport($(this).html(), "SendCompletenessTimelinessByPeriod.php", "Monthly");
        });

        this.aDiseasesWeekly.bind('click', function(){
            var title = $(this).attr('format-title');
            _self.modalMenuForWeeklyDisease.modal({show:true});
            _self.modalViewReportWeekly.unbind();
            _self.modalViewReportWeekly.bind('click', function(){
                //_self.loadReport(title, "NumberOfCasesPerDiseaseByHealthFacility.sql", "NumberOfCasesPerDiseaseByDistrict.sql", "Weekly");
                _self.loadReport(title, "NumberOfCasesPerDiseaseByPeriod.php", "Weekly");
            });
        });

        this.aDiseasesMonthly.bind('click', function(){
            var title = $(this).attr('format-title');
            _self.modalMenuForMonthlyDisease.modal({show:true});
            _self.modalViewReportMonthly.unbind();
            _self.modalViewReportMonthly.bind('click', function(){
                //_self.loadReport(title, "NumberOfCasesPerDiseaseByHealthFacilityMonthly.sql", "NumberOfCasesPerDiseaseByDistrictMonthly.sql", "Monthly");
                _self.loadReport(title, "NumberOfCasesPerDiseaseByPeriod.php", "Monthly");
            });
        });

        this.aResumeWeekly.bind('click', function(){
            _self.modalMenuForWeeklyResume.modal({show:true});
            _self.modalViewWeeklyResume.unbind();
            _self.modalViewWeeklyResume.bind('click', function(){
                _self.loadDashboard(false);
            });
            _self.modalDownloadWeeklyResume.unbind();
            _self.modalDownloadWeeklyResume.bind('click', function(){
                var _oldContent = $(this).html();
                var _button = $(this);
                _self.loadDashboard(true);
            });
        });

        this.aContactList.bind('click', function(){
            _self.loadReport($(this).html(), "ListAssociatedContacts.php", "Weekly");
        });

        this.aAlertRecipientList.bind('click', function(){
            _self.loadReport($(this).html(), "ListAlertRecipients.php", "Weekly");
        });
    };

    this.setDropDownValuesForWeeklyResumePopup = function(){

        var year = moment().set('year', this.getSelectedResumeWeeklyYear()) ;
        var numberOfWeekInYear = year.weeksInYear() ;

        if (this.getSelectedResumeWeeklyYear() == moment().year()){
            numberOfWeekInYear = (moment().week() - 1) > 0 ? (moment().week() - 1) : 1  ;
        }

        this.optResumeWeeklyWeek.find('option').remove().end() ;
        for(var w = 1; w <= numberOfWeekInYear ; w++ )
        {
            // Add Select options
            this.optResumeWeeklyWeek.append(
                $('<option></option>').val(w).html(w)
            );
        }

        this.setWeeklyResumeLabels();
    };

    this.setWeeklyResumeLabels = function() {
        var week = this.getSelectedResumeWeeklyWeek() ;
        var year = this.getSelectedResumeWeeklyYear() ;

        var start = moment().set('year', year) ;
        start.set('week', week );

        $("#lblResumeWeeklyFrom").html(start.day(argusContext.getEpiFirstDay()).format(argusContext.getDateFormat())) ;
        $("#lblResumeWeeklyTo").html(start.day(argusContext.getEpiFirstDay()+6).format(argusContext.getDateFormat())) ;
    };

    this.getSelectedResumeWeeklyWeek = function(){
        return this.optResumeWeeklyWeek.val();
    };

    this.getSelectedResumeWeeklyYear = function(){
        return this.optResumeWeeklyYear.val();
    };

    this.getSelectedDiseases = function(ctxMenu){
        var ids = "";

        ctxMenu.find("input").each(function() {
            if ($(this).is(':checked')) {
                ids += $(this).attr('data-value');
                ids += ",";
            }
        });

        if (ids.length > 0) {
            return ids.substring(0,ids.length-1);
        }
        return "";
    };

    /**
     * Replace %VALUES% & %DISEASES% with selected diseases in the context menu
     *
     * @param title
     * @param diseases
     * @returns {*}
     */
    this.formatTitle = function(title, diseases, period){

        if ( (title.indexOf('%VALUES%') == -1) && (title.indexOf('%DISEASES%' == -1)) ){
            return title;
        }

        var diseasesIds = diseases.split(',');

        var diseasesTitle = "";
        var indicatorsTitle = "";

        for (var i=0 ; i < diseasesIds.length; i++){
            var id = diseasesIds[i];

            for (var z=0 ; z < this.diseasesList.length; z++){
                var oDis = this.diseasesList[z];
                if (oDis.id == id) {
                    if (diseasesTitle.length == 0) {
                        diseasesTitle += oDis.nam;
                    }
                    else{
                        diseasesTitle += ', ' + oDis.nam;
                    }

                    for (var y=0 ; y < oDis.dval.length ; y++ ){
                        var indic = oDis.dval[y].nam ;
                        var per = oDis.dval[y].per;

                        if (per == period && indicatorsTitle.indexOf(indic) == -1){
                            if (indicatorsTitle.length == 0) {
                                indicatorsTitle += indic;
                            }
                            else{
                                indicatorsTitle += ', ' + indic;
                            }
                        }
                    }

                    break;
                }
            }
        }

        return title.replace('%DISEASES%', diseasesTitle).replace('%VALUES%', indicatorsTitle) ;
    };

}


/***** Argus Context ********/
var argusContext ;
function ArgusContext(){

    // General variables
    this.epiFirstDay;
    this.locale;
    this.dateFormat = "DD/MM/YYYY";

    // DateRangePicker
    this.dateRangePicker = $("#dateRangePicker");
    this.dateRangePickerMonthly = $("#dateRangePickerMonthly");

    // Ranges
    this.rangeStartWeekly = $("#rangeStartWeekly");
    this.rangeEndWeekly = $("#rangeEndWeekly");
    this.rangeStartMonthly = $("#rangeStartMonthly");
    this.rangeEndMonthly = $("#rangeEndMonthly");

    // Option Radio (Weekly  Monthly)
    this.optRadioWeekly;
    this.optRadioMonthly;

    // Status
    this.optStatus;

    // Div Filters
    this.divFilterType = $("#divFilterType");
    this.divFilterStatus = $("#divFilterStatus");
    this.pathTwigFilterType;
    this.pathTwigFilterStatus;

    //TreeView
    this.tree = $("#tree");
    this.pathUrlTreeView ;

    //Liste Reports
    this.divListeReport = $("#divListeReport");
    this.pathUrlListReport;

    //List Part Report
    this.pathUrlListPartReport;

    //Liste Alerts
    this.divListeAlerts = $("#divListeAlerts");
    this.divOldAlert;
    this.pathUrlListAlert;

    //Export Report Data
    this.pathUrlExportReportData;
    this.pathUrlExportAlertData;

    // translation words ;
    this.lastWeekW;
    this.last4WeeksW;
    this.lastMonthW;
    this.yearToDateW;
    this.chooseW;
    this.cancelW;
    this.fromW;
    this.toW;
    this.customW;
    this.completenessW;

    this.init = function(epiFirstDay, locale){
        this.epiFirstDay = epiFirstDay ;
        this.locale = locale;

        // Format date regarding locale
       /* if (this.locale == 'fr')
        {
            this.dateFormat = "DD/MM/YYYY";
        }*/

        // Set custom locale to have some ISO week number even if Monday is not the first day of week.
        moment.locale(this.locale +'_'+ this.getEpiFirstDay());

        var _self = this;
        $(document).click(function () {
            _self.hideConfirmations();
        });
    };

    this.initTreeView = function (pathUrlTreeView){
        this.pathUrlTreeView = pathUrlTreeView;
    };

    this.initListeReport = function(pathUrlListReport){
      this.pathUrlListReport = pathUrlListReport;
    };

    this.initListePartReport = function(pathUrlListPartReport){
        this.pathUrlListPartReport = pathUrlListPartReport;
    };

    this.initListeAlert = function(pathUrlListAlert){
        this.pathUrlListAlert = pathUrlListAlert;
    };

    this.initExportReportData = function(pathUrlExportReportData){
        this.pathUrlExportReportData = pathUrlExportReportData;
    };

    this.initExportAlertData = function(pathUrlExportAlertData){
        this.pathUrlExportAlertData = pathUrlExportAlertData;
    };

    this.initTwigFilterType = function(pathTwigFilterType){
        this.pathTwigFilterType = pathTwigFilterType ;
    };

    this.initTwigFilterStatus = function(pathTwigFilterStatus){
        this.pathTwigFilterStatus = pathTwigFilterStatus ;
    };

    this.initTranslationWords = function(lastWeekW, last4WeeksW, lastMonthW, yearToDateW, chooseW, cancelW, fromW, toW, customW, completenessW){
        this.lastWeekW = lastWeekW;
        this.last4WeeksW = last4WeeksW;
        this.lastMonthW = lastMonthW;
        this.yearToDateW = yearToDateW;
        this.chooseW = chooseW;
        this.cancelW = cancelW;
        this.fromW = fromW;
        this.toW = toW;
        this.customW = customW;
        this.completenessW = completenessW;
    };

    this.getEpiFirstDay = function(){
        if (this.epiFirstDay == 7) // Sunday Case
        {
            this.epiFirstDay = 0 ;
        }

        return this.epiFirstDay;
    };

    this.getDateFormat = function (){
        return this.dateFormat;
    };

    this.getStartDate = function(){
        if (this.getPeriod() == 'Weekly') {
            return this.rangeStartWeekly.val();
        }
        else if (this.getPeriod() == 'Monthly'){
            return this.rangeStartMonthly.val();
        }

        return 'null';
    };

    this.getEndDate = function(){
        if (this.getPeriod() == 'Weekly') {
            return this.rangeEndWeekly.val();
        }
        else if (this.getPeriod() == 'Monthly'){
            return this.rangeEndMonthly.val();
        }

        return 'null';
    };

    this.getLocale = function(){
        return this.locale;
    };

    this.getWeekNumber = function(date){
        return moment(date).week();
    };

    this.getRectifiedYear = function(date){
        var weekNumber = this.getWeekNumber(date);

        if (weekNumber <= 2 ){
            return moment(date).add(30, 'days').year() ;
        }
        else if (weekNumber >= 52 ){
            return moment(date).add(-30, 'days').year();
        }
        else{
            return moment(date).year() ;
        }

    };

    this.getYear = function(date){
        return moment(date).year() ;
    };

    this.getMonthName = function(date){
        return  moment(date).format('MMMM');
    };

    this.updateDatePicker = function(){
        if (this.optRadioWeekly.is(':checked')){
            this.dateRangePicker.show();
            this.dateRangePickerMonthly.hide();
        }
        else if (this.optRadioMonthly.is(':checked'))
        {
            this.dateRangePicker.hide();
            this.dateRangePickerMonthly.show();
        }
    };

    this.getStatus = function(){
        if (this.optStatus) {
            return this.optStatus.val();
        }

        return 'null';
    };

    this.setStatus = function(status){
        if (this.optStatus.find("option[value='" + status +"']").length > 0) {
            this.optStatus.val(status);
        }
    };

    this.getPeriod = function(){
        if (this.optRadioWeekly && this.optRadioWeekly.is(':checked')){
            return this.optRadioWeekly.val();
        }
        else if (this.optRadioMonthly && this.optRadioMonthly.is(':checked')){
            return this.optRadioMonthly.val();
        }
        else if (this.optRadioWeekly) {
            return 'None';
        }

        return 'null';
    };

    this.setPeriod = function(period) {
        if (period == 'Weekly'){
            if (this.optRadioWeekly && this.optRadioWeekly.is(':enabled')){
                this.optRadioWeekly.attr('checked', 'checked');
            }
        }
        else if (period == 'Monthly'){
            if (this.optRadioMonthly && this.optRadioMonthly.is(':enabled')){
                this.optRadioMonthly.attr('checked', 'checked');
            }
        }
    };

    this.getSelectedNode = function(){
        var nodes = this.tree.treeview('getSelected');
        var siteId = nodes[0].href ;

        return siteId ;
    };

    this.displayTreeView = function(){
        var _self = this ;

        $.ajax({
            type: "GET",
            url: _self.pathUrlTreeView,
            //timeout: 5000,
            dataType: "json",
            success: function(response) {

                _self.tree.treeview({
                    data: response,
                    onNodeSelected: function(event, data) {
                        _self.refreshAll();
                    }
                });

                //var resultsSearch = _self.findNodeWithPass(currentNodeSearch);
                var resultsSearch = _self.tree.treeview('getSelected'); // Get the selected node and refresh data
                for (i = 0 ; i < resultsSearch.length ; i ++) {
                    if (resultsSearch[i].home == true || resultsSearch.length == 1) {
                        _self.tree.treeview('selectNode', [ resultsSearch[i].nodeId, { silent: true } ]);
                        _self.refreshAll();
                        break ;
                    }
                }

            },
            error: function()
            {
                myApp.hidePleaseWait();
            }
        });
    };

    this.findNodeWithPass = function(path) {
        var result = this.tree.treeview('search', [ path, { ignoreCase: true, exactMatch: true} ]);
        this.tree.treeview('clearSearch');
        return result ;
    };

    this.changeReportMode = function(){
        this.updateDatePicker();
        this.refreshListReport();
    };

    this.refreshAll = function() {
        myApp.showPleaseWait();
        this.refreshFilters();
        this.refreshAlerts();
    };

    this.refreshAlerts = function(){
        var pathGetAlerts = this.pathUrlListAlert;
        pathGetAlerts = pathGetAlerts.replace('HREF', this.getSelectedNode());

        var _self = this ;

        $.get(pathGetAlerts, function(data){
            _self.divListeAlerts.html(data);
            _self.divOldAlert = $("#accordionAlerts");
        });
    };

    this.displayOldAlerts = function(btn){
        $(btn).hide();
        this.divOldAlert.fadeIn();
    };

    this.refreshListReport = function(){
        myApp.showPleaseWait();

        var pathGetListeReport = this.pathUrlListReport;
        pathGetListeReport = pathGetListeReport.replace('HREF',this.getSelectedNode());
        pathGetListeReport = pathGetListeReport.replace('FILTER_STATUS',this.getStatus());
        pathGetListeReport = pathGetListeReport.replace('STARTDATE',this.getStartDate());
        pathGetListeReport = pathGetListeReport.replace('ENDDATE',this.getEndDate());
        pathGetListeReport = pathGetListeReport.replace('PERIOD_TIME',this.getPeriod());

        var _self = this ;
        $.get(pathGetListeReport, function(data){

            myApp.hidePleaseWait();
            _self.divListeReport.html(data);

            //Confirmation
            $("[data-toggle=confirmation]").each( function (index, el){
                var _el = $(el);
                _el.confirmation({
                    html:"true",
                    popout:"true",
                    btnOkIcon:"fa fa-check",
                    btnCancelIcon:"fa fa-remove",
                    onConfirm: function() {
                        if (_el.attr('data-type') == 'Reject') {
                            RejectReports(_el.attr('data-id'));
                        }
                        else {
                            ValideReports(_el.attr('data-id'));
                        }
                    }
                });
            });


            // Ajax loading partReports when clicking on the a
            $("#accordionPrincipal a").click(function () {
                var href = this.hash;
                var fullReportId = href.replace("#collapseR","");

                if (! $('#accordionFullReport'+fullReportId).text().trim()) {

                    var pathGetListePartReport = _self.pathUrlListPartReport;
                    pathGetListePartReport = pathGetListePartReport.replace('FULLREPORTID', fullReportId);
                    $('#btnGroupFullReport'+fullReportId +' button').attr('disabled', 'disabled');

                    $.get(pathGetListePartReport, function (data) {
                        $('#accordionFullReport' + fullReportId).html(data);
                        $('#btnGroupFullReport'+ fullReportId + ' button').removeAttr('disabled');

                        $(function () {
                            $('#accordionFullReport' + fullReportId + ' [data-toggle="tooltip"]').tooltip({template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner modal-sm"></div></div>'})
                        });

                        // Popover
                        $("div[rel=popover]").each( function (index, el){
                            var _el = $(el);
                            var id = _el.attr('id');
                            _el.popover({
                                html: true,
                                placement: "right",
                                title: _self.completenessW,
                                content: function(){
                                    return $("#pPartReport"+id).find("div[rel=popover-content-"+id+"]").html();
                                },
                                trigger: "hover"
                            });
                        });
                    });
                }
            });
        });
    };

    this.refreshFilters = function() {
        this.refreshFilterType();
    };

    this.refreshFilterType = function(){
        //saving olf value
        var oldPeriod = this.getPeriod();

        var pathTwigFilterType = this.pathTwigFilterType;
        var _self = this;

        pathTwigFilterType = pathTwigFilterType.replace('SITEID',this.getSelectedNode());

        $.get(pathTwigFilterType, function (data) {
            _self.divFilterType.html(data);
            _self.optRadioWeekly = $("#optRadioWeekly");
            _self.optRadioMonthly = $("#optRadioMonthly");

            //Set olValue set if possible
            _self.setPeriod(oldPeriod);

            _self.refreshFilterStatus();
        });
    };

    this.refreshFilterStatus = function(){
        //saving olf value
        var oldStatus = this.getStatus();

        var pathTwigFilterStatus = this.pathTwigFilterStatus;
        var _self = this;

        pathTwigFilterStatus = pathTwigFilterStatus.replace('SITEID',this.getSelectedNode());
        pathTwigFilterStatus = pathTwigFilterStatus.replace('PERIOD_TIME',this.getPeriod());

        $.get(pathTwigFilterStatus, function (data) {
            _self.divFilterStatus.html(data);
            _self.optStatus = $("#optStatus");

            //Set olValue set if possible
            _self.setStatus(oldStatus);

            _self.changeReportMode();
        });
    };

    this.hideConfirmations = function(){
        $("[data-toggle=confirmation]").confirmation('hide');
    };

    this.exportReportData = function(){
        var pathExportData = this.pathUrlExportReportData;
        pathExportData = pathExportData.replace('FILTER_STATUS',this.getStatus());
        pathExportData = pathExportData.replace('STARTDATE',this.getStartDate());
        pathExportData = pathExportData.replace('ENDDATE',this.getEndDate());
        pathExportData = pathExportData.replace('PERIOD_TIME',this.getPeriod());

        window.location = pathExportData ;
    };

    this.exportAlertData = function(){
        var pathExportData = this.pathUrlExportAlertData;
        pathExportData = pathExportData.replace('STARTDATE',this.getStartDate());
        pathExportData = pathExportData.replace('ENDDATE',this.getEndDate());

        window.location = pathExportData ;
    };

    return this;
}
