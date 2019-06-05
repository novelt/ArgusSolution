import { UrlService } from './../url/url.service';
import { FilterService } from '../filter/filter.service';
import { BaseService } from '../base.service';
import { AuthenticationService } from '../../../core/authentication/authentication.service';
import { Http, ResponseContentType, Headers, URLSearchParams } from '@angular/http';
import { Injectable } from '@angular/core';
import { Ng4LoadingSpinnerService } from 'ng4-loading-spinner';
import { Router } from '@angular/router';
import 'rxjs/add/operator/map';

const WEEKLY_REPORT_DETAILS_URL = '/api/weeklyEpiReportDetails';
const PDF_REPORT_URL = 'PdfConvDashBoard';

@Injectable()
export class EpidemiologicReportService extends BaseService { // implements IReportService 

  constructor(private http: Http, 
              authenticationService: AuthenticationService,
              router : Router,
              filterService: FilterService,
              private spinnerService: Ng4LoadingSpinnerService,
              private urlService: UrlService) {
    super(authenticationService, router, filterService);
  }

  public getWeeklyReportDetails(siteId: number = undefined, year: number = undefined, week: number = undefined) {
    console.log('getWeeklyReportDetails');
    this.spinnerService.show();

    return this.http.get(this.urlService.getServerUrl() + this.getLanguage() + WEEKLY_REPORT_DETAILS_URL 
                          + '/' + siteId + '/' + year + '/' + week ,
        { headers: this.getAuthorizationHeaders() } )
      .map(response => response.json())
      .catch((error: Response) => this.catchError(error))
      //.finally(() => this.spinnerService.hide());
  }

  public downloadWeeklyReport(pathReport: string, details: string) {
    console.log('downloadWeeklyReport: ' + pathReport + ' : ' + details);
    this.spinnerService.show();

    let headers = new Headers();
    headers.set('Accept', 'application/pdf');

    let params = new URLSearchParams();
    params.append('contentDisposition', 'inline');
    params.append('title', details);
    
    return this.http.get(this.urlService.getServerReportUrl() + PDF_REPORT_URL + '/' + pathReport,
      { headers: headers, search: params, responseType: ResponseContentType.Blob })
      .map(response => {
        return {
          filename: pathReport + '.pdf',
          data: response.blob()
        };
      })
    .catch((error: Response) => this.catchError(error))
    .finally(() => this.spinnerService.hide()); 
  }
    
  public downloadFile(res: any) {
    var url= window.URL.createObjectURL(res.data);
    var a = document.createElement('a');
    document.body.appendChild(a);
    a.setAttribute('style', 'display: none');
    a.href = url;
    a.download = res.filename;
    a.click();
    window.URL.revokeObjectURL(url);
    a.remove();
  }
  
}
