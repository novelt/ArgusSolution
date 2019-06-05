import { UrlService } from './../../shared/service/url/url.service';
import { FilterService } from '../../shared/service/filter/filter.service';
import { AppError } from '../../shared/common/Error/app-error';
import { AuthenticationService } from '../../core/authentication/authentication.service';
import { Report } from '../../shared/model/report';
import { Http } from '@angular/http';
import { Injectable } from '@angular/core';
import 'rxjs/add/operator/map';
import 'rxjs/add/operator/finally';
import { Ng4LoadingSpinnerService } from 'ng4-loading-spinner';
import { Router, ActivatedRoute } from '@angular/router';
import { AbstractReportService, TODO_REPORTS_DATA_URL } from '../../shared/service/report/abstract-report.service';

@Injectable()
export class TodoReportService extends AbstractReportService {

  constructor(http: Http, 
              authenticationService: AuthenticationService,
              router : Router,
              filterService: FilterService, 
              spinnerService: Ng4LoadingSpinnerService,
              urlService: UrlService ) {
    super(authenticationService, 
      router, 
      filterService, 
      http,
      spinnerService,
      urlService);
   }

  /** Reports */

  /**
   * Load Lsit of Reports without versions
   * 
   * @param forceLoad 
   */
  loadListOfReports(forceLoad?:boolean) {     
    console.log('loadListOfReports');
    this.spinnerService.show();
    this.http.get(this.urlService.getServerUrl() + this.getLanguage() + TODO_REPORTS_DATA_URL,
                { headers: this.getAuthorizationHeaders() })
                .map(response => response.json().reports)
                .catch((error: Response) => this.catchError(error))
                .finally(() => {
                  this.spinnerService.hide();
                  console.log('loadListOfReports this.spinnerService.hide();');
                })
                .subscribe(
                  reports => {
                    this.dataStore.reports = this.jsonConvert.deserializeArray(reports, Report); 
                    this._reports.next(Object.assign({}, this.dataStore).reports);
                  },
                  (error: AppError) => this.subscribeError(error));
  }

  /**
   * Manage action after Validation / Rejection
   * Here, we open automatically the next Report
   * 
   * @param report 
   */
  public manageAction(report: Report, activatedRoute : ActivatedRoute): void {
    // Delete report from list 
    let index = this.remove(report);
     // Get next report
    if (index < 0) {
      index = 0;
    } else if (index >= this.getLoadedListOfReportsReports().length) {
      index = this.getLoadedListOfReportsReports().length -1;
    }

    let nextReport = this.getLoadedListOfReportsReports()[index];

    if (nextReport != null) {
      this.router.navigate(['../' + nextReport.getId()], { relativeTo: activatedRoute, queryParams: { sridx: index, srid : nextReport.getId() } });
    } else {
      this.router.navigate(['../'], { relativeTo: activatedRoute });
    }
  }
}
