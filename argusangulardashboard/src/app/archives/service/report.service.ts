import { REPORTS_DATA_URL } from './../../shared/service/report/abstract-report.service';
import { AppError } from './../../shared/common/Error/app-error';
import { Ng4LoadingSpinnerService } from 'ng4-loading-spinner';
import { Report } from './../../shared/model/report';
import { FilterService } from './../../shared/service/filter/filter.service';
import { AuthenticationService } from './../../core/authentication/authentication.service';
import { Router, ActivatedRoute } from '@angular/router';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { AbstractReportService } from '../../shared/service/report/abstract-report.service';
import { UrlService } from '../../shared/service/url/url.service';

export class ReportService extends AbstractReportService {

  constructor(http: Http,
              authenticationService: AuthenticationService,
              router : Router,
              filterService: FilterService,
              spinnerService: Ng4LoadingSpinnerService,
              urlService: UrlService) {
    super(authenticationService, 
          router, 
          filterService, 
          http, 
          spinnerService,
          urlService);
   }

  loadListOfReports(forceLoad?:boolean): void {
    if (this.getSelectedSiteId() == null) { // No Site selected
      return ;
    }

    if (this.getSelectedPeriod() == null) { // No Period selected
      return ;
    }

    if (this.getSelectedDateRangeStart() == null || this.getSelectedDateRangeEnd() == null) { // No Site selected
      return ;
    }
    
    this.spinnerService.show();
    this.http.get(this.urlService.getServerUrl() + this.getLanguage() + REPORTS_DATA_URL
                + '/' + this.getSelectedSiteId()
                + '/' + this.getSelectedPeriod()               
                + '/' + this.getSelectedDateRangeStart()
                + '/' + this.getSelectedDateRangeEnd(),
                //+ '/' + this.getSelectedStatuses(),
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
   * Here, we refresh the current report validated/rejected and go back to the list
   * 
   * @param report 
   */
  public manageAction(report: Report, activatedRoute : ActivatedRoute): void {
    // Get the updated report
    if (report != null) {
      this.refreshReport(report);
    }

    this.router.navigate(['../'], { relativeTo: activatedRoute });
  }
}
