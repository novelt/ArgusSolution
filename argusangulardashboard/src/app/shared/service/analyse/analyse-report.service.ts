import { Period } from './../../constant/period';
import { Moment } from 'moment';
import { TranslateService } from '@ngx-translate/core';
import { FilterService } from './../filter/filter.service';
import { BaseService } from './../base.service';
import { AuthenticationService } from './../../../core/authentication/authentication.service';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { UrlService } from '../url/url.service';

export interface ReportInformation {
    id: number,
    prefix: string, 
    name: {
        weekly : string,
        monthly : string
    },
    file: string,
}

const ANALYSE_REPORT_URL = 'report/?report=SESReports/';

// Parameters
const DATE_RANGE_START = 'macros[Range][]';
const DATE_RANGE_END = 'macros[Range][]';
const PERIOD = 'macros[Period]';
const SITE = 'macros[Site]';
const DISEASE = 'macros[Diseases]';
const LOCALE = 'macros[Locale]';
// REPORT_NAME & REPORT_DETAILS used for the PDF 
const REPORT_NAME = 'reportname'; //'%3Cb%3EEnvoi+des%20rapports%3C%2Fb%3E%20-%20Rapport%20de%20promptitude%20et%20compl%C3%A9tude%20hebdomadaire'
const REPORT_DETAILS = 'reportdetails' //%5BR%C3%A9gion+Bas%20%3A%20De%20la%20%3Cb%3ESemaine%2013%20-%202018%20%3C%2Fb%3E%C3%A0%20la%20%3Cb%3ESemaine%2016%20-%202018%3C%2Fb%3E%5D';
const EXPORT = 'export';

// http://localhost/sesDashboardReports/report/?report=SESReports/SendCompletenessTimelinessBySite.php&macros[Range][]=2018-03-26&macros[Range][]=2018-04-22&macros[Period]=Weekly&macros[Site]=4&macros[Diseases]=13&macros[Locale]=fr&reportname=%3Cb%3EEnvoi+des%20rapports%3C%2Fb%3E%20-%20Rapport%20de%20promptitude%20et%20compl%C3%A9tude%20hebdomadaire&reportdetails=%5BR%C3%A9gion+Bas%20%3A%20De%20la%20%3Cb%3ESemaine%2013%20-%202018%20%3C%2Fb%3E%C3%A0%20la%20%3Cb%3ESemaine%2016%20-%202018%3C%2Fb%3E%5D&export=true

@Injectable()
export class AnalyseReportService extends BaseService { 

    private seletedDiseases: Array<{ id: number, checked: boolean }> = new Array();

    /** Translations */
    private sPrefixReporting: {str: string} = {str: ''};
    private sPrefixValidating: {str: string} = {str: ''};

    private sWeeklyTC: {str: string} = {str: ''};
    private sWeeklyTCTrend: {str: string} = {str: ''};
    private sWeeklyCases: {str: string} = {str: ''};

    private sMonthlyTC: {str: string} = {str: ''};
    private sMonthlyTCTrend: {str: string} = {str: ''};
    private sMonthlyCases: {str: string} = {str: ''};

    private sWeek: {str: string} = {str: ''};
    private sFromWeek: {str: string} = {str: ''};
    private sToWeek: {str: string} = {str: ''};

    private sMonth: {str: string} = {str: ''};
    private sFromMonth: {str: string} = {str: ''};
    private sToMonth: {str: string} = {str: ''};

    constructor(authenticationService: AuthenticationService,
                router : Router,
                filterService: FilterService,
                private translateService: TranslateService,
                private urlService: UrlService) {
        super(authenticationService, router, filterService);

        this.initReportsTranslations();
   }

    private initReportsTranslations() {
        this.translateService.get('analyses.details.prefix.data_reporting').subscribe((res: string) => { this.sPrefixReporting.str = res;});
        this.translateService.get('analyses.details.prefix.data_validation').subscribe((res: string) => { this.sPrefixValidating.str = res;});

        this.translateService.get('analyses.details.title.weekly_timeliness_completeness').subscribe((res: string) => { this.sWeeklyTC.str = res;});
        this.translateService.get('analyses.details.title.weekly_timeliness_completeness_trend').subscribe((res: string) => { this.sWeeklyTCTrend.str = res;});
        this.translateService.get('analyses.details.title.weekly_number_of_cases').subscribe((res: string) => { this.sWeeklyCases.str = res;});

        this.translateService.get('analyses.details.title.monthly_timeliness_completeness').subscribe((res: string) => { this.sMonthlyTC.str = res;});
        this.translateService.get('analyses.details.title.monthly_timeliness_completeness_trend').subscribe((res: string) => { this.sMonthlyTCTrend.str = res;});
        this.translateService.get('analyses.details.title.monthly_number_of_cases').subscribe((res: string) => { this.sMonthlyCases.str = res;});
    
        this.translateService.get('filter.week').subscribe((res: string) => {this.sWeek.str = res;});
        this.translateService.get('filter.from_week').subscribe((res: string) => {this.sFromWeek.str = res;});
        this.translateService.get('filter.to_week').subscribe((res: string) => {this.sToWeek.str = res;});

        this.translateService.get('filter.month').subscribe((res: string) => {this.sMonth.str = res;});
        this.translateService.get('filter.from_month').subscribe((res: string) => {this.sFromMonth.str = res;});
        this.translateService.get('filter.to_month').subscribe((res: string) => {this.sToMonth.str = res;});
        
    }

    public getAnalyseUrl(report: ReportInformation) {

        let url = new URL(this.urlService.getServerReportUrl() + ANALYSE_REPORT_URL + report.file);
        url.searchParams.append(DATE_RANGE_START, this.getSelectedDateRangeStart());
        url.searchParams.append(DATE_RANGE_END, this.getSelectedDateRangeEnd());
        url.searchParams.append(PERIOD, this.getSelectedPeriod());
        url.searchParams.append(SITE, '' + this.getSelectedSiteId());
        url.searchParams.append(DISEASE, ''+ this.getSelectedDiseases());
        url.searchParams.append(LOCALE, this.getLanguage());
        url.searchParams.append(REPORT_NAME, this.getSelectedPeriod() == Period.WEEKLY ?  report.name.weekly : report.name.monthly);
        url.searchParams.append(REPORT_DETAILS, this.getReportDetails());
        url.searchParams.append(EXPORT, '' + this.isSiteExportable());
       
        return url.toString();
       
        /*let url = this.urlService.getServerReportUrl() + ANALYSE_REPORT_URL;
        url += report.file;
        url += '&' + DATE_RANGE_START + '=' + this.getSelectedDateRangeStart();
        url += '&' + DATE_RANGE_END + '=' + this.getSelectedDateRangeEnd();
        url += '&' + PERIOD + '=' +  this.getSelectedPeriod();
        url += '&' + SITE + '=' + this.getSelectedSiteId() ;
        url += '&' + DISEASE + '=' + this.getSelectedDiseases() ;
        url += '&' + LOCALE + '=' + this.getLanguage() ;
        url += '&' + REPORT_NAME + '=' + encodeURI(report.name).replace('&', '%26') ; //this.sanitizer.sanitize(SecurityContext.HTML, report.name) ;
        url += '&' + REPORT_DETAILS + '=' + '' ;
        url += '&' + EXPORT + '=' + this.isSiteExportable() ;

        console.log(url);
        return url;*/
   }    

    private isSiteExportable() {
        let selectedSite = this.filterService.getSelectedSite();
        if (selectedSite != null) {
            return selectedSite.isExportable();
        }

        return false;
    }

    private getSelectedDiseases() {
        let concatDiseaseIds = '';
        
        this.seletedDiseases.forEach(disease => {
            if (disease.checked) {
                concatDiseaseIds += disease.id + ',';
            }
        });

        return concatDiseaseIds;
    }

    private  getReportDetails(): string {
        let dateStart = this.filterService.getSelectedDateRangeStart();
        let dateEnd = this.filterService.getSelectedDateRangeStart();

        // Create Report Details and push it to getAnalyseURL
        // depending the period selected
        return this.filterService.getSelectedPeriod() == Period.WEEKLY ? 
                this.getWeeklyDetails(dateStart, dateEnd) : 
                this.getMonthlyDetails(dateStart, dateEnd) ;
    }

    private getWeeklyDetails(dateStart: Moment, dateEnd: Moment): string {
        let details = '[' 
        + this.filterService.getSelectedSite().getName() 
        + ': ' + this.sFromWeek.str + ' ' + this.sWeek.str + ' <b>' + dateStart.week() + ' - ' + this.getRectifiedYear(dateStart)
        + '</b> ' + this.sToWeek.str + ' ' + this.sWeek.str + ' <b>' + dateEnd.week() + ' - ' + this.getRectifiedYear(dateEnd)
        +'</b>]';

        return details;
    }

    private getMonthlyDetails(dateStart: Moment, dateEnd: Moment): string {
        let details = '[' 
        + this.filterService.getSelectedSite().getName() 
        + ': ' + this.sFromMonth.str + ' ' + this.sMonth.str + ' <b>' + dateStart.month() + ' - ' + this.getRectifiedYear(dateStart)
        + '</b> ' + this.sToMonth.str + ' ' + this.sMonth.str + ' <b>' + dateEnd.month() + ' - ' + this.getRectifiedYear(dateEnd)
        +'</b>]';

        return details;
    }

    private getRectifiedYear(date: Moment) {
        var weekNumber = date.week();
          
        if (weekNumber <= 2 ){
            return date.clone().add(30, 'days').year() ;
        }
        else if (weekNumber >= 52 ){
            return date.clone().add(-30, 'days').year();
        }
        else{
            return date.year() ;
        }
    } 

    public setSelectedDisease(id: number, checked: boolean){
        let disease = this.seletedDiseases.find(disease => disease.id === id);
        if (disease != null) {
            disease.checked = checked;
        } else {
            this.seletedDiseases.push({id, checked}); 
        }
    }

    public getNumberOfSelectedDiseases() {
        let selectedDiseases = this.seletedDiseases.filter(disease => disease.checked === true);
        return selectedDiseases.length;
    }

    public isDiseaseSelected(id: number) {
        let disease = this.seletedDiseases.find(disease => disease.id === id);
        if (disease != null) {
            return disease.checked;
        }

        return false ;
    }

    public getAnalyseReportsAvailable(): Array<ReportInformation> {
       return  [{
            id: 1,
            prefix: this.sPrefixReporting.str, 
            name: {
                weekly : this.sWeeklyTC.str,
                monthly : this.sMonthlyTC.str
            },
            file: 'SendCompletenessTimelinessBySite.php',
          },
          {
            id: 2,
            prefix: this.sPrefixReporting.str, 
            name: {
                weekly : this.sWeeklyTCTrend.str,
                monthly : this.sMonthlyTCTrend.str
            },
            file: 'SendCompletenessTimelinessByPeriod.php',
          },
          {
            id: 3,
            prefix: this.sPrefixValidating.str, 
            name: {
                weekly : this.sWeeklyTC.str,
                monthly : this.sMonthlyTC.str
            },
            file: 'ValidateCompletenessTimelinessBySite.php',
          },
          {
            id: 4,
            prefix: this.sPrefixValidating.str, 
            name: {
                weekly : this.sWeeklyTCTrend.str,
                monthly : this.sMonthlyTCTrend.str
            },
            file: 'ValidateCompletenessTimelinessByPeriod.php',
          },
          {
            id: 5,
            prefix: '', 
            name: {
                weekly : this.sWeeklyCases.str,
                monthly : this.sMonthlyCases.str
            },
            file: 'NumberOfCasesPerDiseaseByPeriod.php',
          }];
    }
}
