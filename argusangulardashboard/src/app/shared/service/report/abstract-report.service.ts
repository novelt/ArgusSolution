import { UrlService } from './../url/url.service';
import { AppError } from '../../common/Error/app-error';
import { Http } from '@angular/http';
import { FilterService } from '../filter/filter.service';
import { JsonConvert } from 'json2typescript';
import { ReportVersion } from '../../model/reportVersion';
import { Report } from '../../model/report';
import { Observable, BehaviorSubject } from 'rxjs';
import { Router, ActivatedRoute } from '@angular/router';
import { BaseService } from '../base.service';
import { AuthenticationService } from '../../../core/authentication/authentication.service';
import { Injectable } from '@angular/core';
import { Ng4LoadingSpinnerService } from 'ng4-loading-spinner';

export const REPORTS_DATA_URL = '/api/reportsData';
export const TODO_REPORTS_DATA_URL = '/api/todoReportsData';
const REPORT_DATA_URL = '/api/reportData';
const REPORT_VERSION_DATA_URL = '/api/reportsVersionData';
const VALIDATE_REPORT_VERSION_DATA_URL = '/api/reportsData/validate';
const REJECT_REPORT_VERSION_DATA_URL = '/api/reportsData/reject';

@Injectable()
export abstract class AbstractReportService extends BaseService {

  /** JSON converter */
  protected jsonConvert: JsonConvert = new JsonConvert();

  protected _reports: BehaviorSubject<Report[]>;
  protected _statuses: BehaviorSubject<Array<{status: string, count:number}>>;

  protected dataStore: {
    reports: Report[],
    statuses: Array<{status: string, count:number}>,
  };
  
  constructor(authenticationService: AuthenticationService,
              protected router : Router,
              filterService: FilterService,
              protected http: Http,
              protected spinnerService: Ng4LoadingSpinnerService,
              protected urlService: UrlService) {
    super(authenticationService, router, filterService);

    this.init();
  }

  private init() {
    this.dataStore = {
      reports: [], 
      statuses: [],
    };
    this._reports = <BehaviorSubject<Report[]>> new BehaviorSubject([]);
    this._statuses = <BehaviorSubject<Array<{status: string, count:number}>>> new BehaviorSubject([]);
  }

   /********** Period *************/
  public getPeriod(): Observable<string> {
    return this.filterService.getPeriod() ;
  }

  public changeSelectedPeriod(period : string): void {
    this.filterService.setSelectedPeriod(period);
  }

   /********* Reports *************/
  public getListOfReports(): Observable<Report[]> {
    return this._reports.asObservable();
  }

  public getLoadedListOfReportsReports(): Report[] {
    return this.dataStore.reports;
  }

  public abstract loadListOfReports(forceLoad?:boolean): void;
  
  public emptyListOfReports(): void {
    this.dataStore.reports = new Array<Report>();
    this._reports.next(Object.assign({}, this.dataStore).reports);
  }

  /**
   * Get index of report in the data store list.
   * 
   * @param report 
   */
  public getReportIndex(report: Report) : number {
    return this.getLoadedListOfReportsReports().findIndex((el: Report) => el.getId() == report.getId());
  }

  /**
   * Remove report from list
   */
  public remove(reportToRemove: Report): number {
    if (reportToRemove == null) {
      return -1 ;
    }

    let index = this.getLoadedListOfReportsReports().findIndex(report => report.getId() == reportToRemove.getId());
    
    if (index >= 0) {
      this.dataStore.reports.splice(index, 1);
      this._reports.next(Object.assign({}, this.dataStore).reports);
    }

    return index;
  }

  /**
   * Refresh reports get update from server
   * 
   * @param reportToRefresh 
   */
  public refreshReport(reportToRefresh: Report): void {
    console.log('refreshReport');
    this.spinnerService.show();
    this.http.get(this.urlService.getServerUrl() + this.getLanguage() + REPORT_DATA_URL + '/' + reportToRefresh.getId(),
                { headers: this.getAuthorizationHeaders() })
                .map(response => response.json().reports)
                .catch((error: Response) => this.catchError(error))
                .finally(() => {
                  this.spinnerService.hide();
                  console.log('refreshReport this.spinnerService.hide();');
                })
                .subscribe(
                  reports => {
                    let dReports = this.jsonConvert.deserializeArray(reports, Report); 

                    dReports.forEach((report: Report) => {
                      let index = this.getReportIndex(report);
                      this.dataStore.reports[index] = report;
                    });

                    this._reports.next(Object.assign({}, this.dataStore).reports);
                  },
                  (error: AppError) => this.subscribeError(error));
  }

  /**
   * Return report from dataStore reports list
   * 
   * @param reportId 
   */
  public getReport(reportId:number): Report|null {
    let report = null;
    
    this.dataStore.reports.some(function(el, index) {
      report = el;
      return el.getId() === reportId;
    });

    return report ;
  }

  /***** Reports Status / count ****/
  
   /**
   * Get a list of Object status / count
   * 
   * @param reportList 
   */
  public loadStatusList(period: string): void {
    let result: Array<{status: string, count:number}> = new Array() ;
    
    let statuses: Array<string> = new Array() ;
    let nbElement: Array<number> = new Array() ;

    this.dataStore.reports
       .filter((report : Report) => {
         return report.getPeriod() === period;
       })
      .forEach(element => {
        let index = statuses.indexOf(element.getStatus());
        if (index == -1) { // New Status
          statuses.push(element.getStatus());
          nbElement.push(1);
        } else {
          nbElement[index] += 1;
        }
      });

    statuses.forEach((element, index) => {
      result.push({status:element, count: nbElement[index]})
    });
   
    this.dataStore.statuses = result; 
    this._statuses.next(Object.assign({}, this.dataStore).statuses); 
  }

  public getStatusList(): Observable<{status: string, count: number}[]> {
    return this._statuses.asObservable();
  }
   
   /***** Report Versions *********/
  public getReportVersions(reportId : number): Observable<ReportVersion[]> {
    if (!reportId) {
      return null ;
    }
    
    let report = this.getReport(reportId);
    if (report != null) {
      return report.getReportVersions();
    } else {
      return null;
    }
  }

  /**
   * Load list of Report Versions
   * 
   */
  public loadReportVersions(reportId : number): void {
    console.log('loadReportVersion reportId:' + reportId);
    this.spinnerService.show();
    this.http.get(this.urlService.getServerUrl() + this.getLanguage() + REPORT_VERSION_DATA_URL + '/' + reportId
            , { headers: this.getAuthorizationHeaders() }
            )
            .map(response => response.json().reportVersions)
            .catch((error: Response) => this.catchError(error))
            .finally(() => this.spinnerService.hide())
            .subscribe(
              reportVersions => {
                // Find Report with reportId
                let report = this.getReport(reportId);

                if (report !== null) {
                  let versions = this.jsonConvert.deserializeArray(reportVersions, ReportVersion);
                  report.setReportVersions(versions);
                }
            },
            (error: AppError) => this.subscribeError(error));       
  }

  /**
   * Return the reportVersion
   * 
   * @param reportId 
   * @param reportVersionId 
   */
  public getReportVersion(reportId: number, reportVersionId : number) : ReportVersion|null {
    let reportVersion = null;
    if (!reportVersionId || !reportId) {
      return null ;
    }
    
    let report = this.getReport(reportId);

    if (report !== null && report.getVersions() != null) {
        report.getVersions().some(function(version, index) {
          reportVersion = version;
          return version.getId() === reportVersionId;
        });
    }

    return reportVersion ;
  }

  /**
   * Validate the report
   * 
   * @param reportId 
   * @param reportVersionId 
   */
  public validateReportVersion(reportId: number, reportVersionId: number): Observable<Response> {
    console.log('validateReportVersion reportId: ' + reportId + ', reportVersionId: ' + reportVersionId);
    this.spinnerService.show();
    //return Observable.of(new Response()).finally(() => this.spinnerService.hide());
    return this.http.get(this.urlService.getServerUrl() + this.getLanguage() + VALIDATE_REPORT_VERSION_DATA_URL + '/' + reportId + '/' + reportVersionId ,
        { headers: this.getAuthorizationHeaders() })
        .catch((error: Response) => this.catchError(error))
        .finally(() => this.spinnerService.hide());     
  }

  /**
   * Reject the report
   * 
   * @param reportId 
   * @param reportVersionId 
   */
  public rejectReportVersion(reportId: number, reportVersionId: number): Observable<Response> {
    console.log('rejectReportVersion reportId: ' + reportId + ', reportVersionId: ' + reportVersionId);
    this.spinnerService.show();
    //return Observable.of(new Response()).finally(() => this.spinnerService.hide());
    return this.http.get(this.urlService.getServerUrl() + this.getLanguage() + REJECT_REPORT_VERSION_DATA_URL + '/' + reportId + '/' + reportVersionId ,
        { headers: this.getAuthorizationHeaders() })
        .catch((error: Response) => this.catchError(error))
        .finally(() => this.spinnerService.hide());  
  }

  public abstract manageAction(report: Report, activatedRoute: ActivatedRoute): void;

  public emptyData() {
    this.init();
  }
}
