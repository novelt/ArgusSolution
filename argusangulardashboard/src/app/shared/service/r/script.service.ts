import { Analyse } from './../../model/analyse';
import { UrlService } from '../url/url.service';
import { Observable } from 'rxjs/Observable';
import { Script } from '../../model/script';
import { BehaviorSubject } from 'rxjs';
import { JsonConvert } from 'json2typescript';
import { AppError } from '../../common/Error/app-error';
import { Ng4LoadingSpinnerService } from 'ng4-loading-spinner';
import { FilterService } from '../filter/filter.service';
import { Router } from '@angular/router';
import { AuthenticationService } from '../../../core/authentication/authentication.service';
import { BaseService } from '../base.service';
import { Injectable } from '@angular/core';
import { Http, ResponseContentType, Headers, URLSearchParams } from '@angular/http';

const R_SCRIPTS_URL = '/api/rScripts';
const R_ANALYSES_URL = '/api/rAnalyses';
const R_ANALYSES_DL_URL = '/api/rDownloadAnalyse/';

@Injectable()
export class ScriptService extends BaseService {

   /** JSON converter */
   private jsonConvert: JsonConvert = new JsonConvert();

  private _scripts: BehaviorSubject<Script[]>;
  private _analyses: BehaviorSubject<Analyse[]>;
 
  private dataStore: {
    scripts: Script[],
    analyses: Analyse[],
  };

  constructor(private http: Http,
              authenticationService: AuthenticationService,
              router : Router,
              filterService : FilterService,
              private spinnerService: Ng4LoadingSpinnerService,
              private urlService: UrlService) {
    super(authenticationService, router, filterService);

    this.init();
  }

  private init() {
    this.dataStore = {
      scripts: [], 
      analyses: [],
    };
    this._scripts = <BehaviorSubject<Script[]>> new BehaviorSubject([]);
    this._analyses = <BehaviorSubject<Analyse[]>> new BehaviorSubject([]);
  }

  public loadScripts() {
    this.spinnerService.show();
    this.http.get(this.urlService.getServerUrl() + this.getLanguage() + R_SCRIPTS_URL,
                { headers: this.getAuthorizationHeaders() })
                .map(response => response.json().scripts)
                .catch((error: Response) => this.catchError(error))
                .finally(() => {
                  this.spinnerService.hide();
                  console.log('loadScripts this.spinnerService.hide();');
                })
                .subscribe(
                  scripts => {
                    this.dataStore.scripts = this.jsonConvert.deserializeArray(scripts, Script); 
                    this._scripts.next(Object.assign({}, this.dataStore).scripts);
                  },
                  (error: AppError) => this.subscribeError(error));
  }

  public getScripts(): Observable<Script[]> {
    return this._scripts.asObservable();
  }

  public loadAnalyses() {
    this.spinnerService.show();
    this.http.get(this.urlService.getServerUrl() + this.getLanguage() + R_ANALYSES_URL,
                { headers: this.getAuthorizationHeaders() })
                .map(response => response.json().analyses)
                .catch((error: Response) => this.catchError(error))
                .finally(() => {
                  this.spinnerService.hide();
                  console.log('loadAnalyses this.spinnerService.hide();');
                })
                .subscribe(
                  analyses => {
                    this.dataStore.analyses = this.jsonConvert.deserializeArray(analyses, Analyse); 
                    this._analyses.next(Object.assign({}, this.dataStore).analyses);
                  },
                  (error: AppError) => this.subscribeError(error));
  }

  public getAnalyses(): Observable<Analyse[]> {
    return this._analyses.asObservable();
  }

  public downloadAnalyse(analyse: Analyse) {
    this.spinnerService.show();
    
    return this.http.get(this.urlService.getServerUrl() + this.getLanguage() + R_ANALYSES_DL_URL + analyse.getTitle() + '/' + analyse.getExtension(),
      { headers: this.getAuthorizationHeaders(), responseType: ResponseContentType.Blob })
      .map(response => {
        return {
          filename: analyse.getTitle() + '.' + analyse.getExtension(),
          data: response.blob()
        };
      })
    .catch((error: Response) => this.catchError(error))
    .finally(() => this.spinnerService.hide()); 
  }

}
