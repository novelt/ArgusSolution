import { UrlService } from './../../shared/service/url/url.service';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { of } from 'rxjs/observable/of';
import { tap } from 'rxjs/operators';
import { Http, Headers } from '@angular/http';
import { TranslateService } from '@ngx-translate/core' ;
import * as moment from 'moment';

export interface Credentials {
  // Customize received credentials here
  username: string;
  token: string;
  refresh_token: string;
  lang: string;
  epiFirstDay: number;
  homeSiteId: number;
  homeSiteName: string;
  translations: any;
  version: string;
}

export interface LoginContext {
  username: string;
  password: string;
  remember?: boolean;
}

const credentialsKey = 'credentials';
const LOGIN_URL = 'api/login_check';
const INFORMATION_URL = 'api/information';

/**
 * Provides a base for authentication workflow.
 * The Credentials interface as well as login/logout methods should be replaced with proper implementation.
 */
@Injectable()
export class AuthenticationService {

  private _credentials: Credentials | null;

  constructor(private http: Http,
              private translateService: TranslateService,
              private urlService: UrlService) {

    const savedCredentials = sessionStorage.getItem(credentialsKey) || localStorage.getItem(credentialsKey);
    if (savedCredentials) {
      this._credentials = JSON.parse(savedCredentials);
    }
  }

  /**
   * Authenticates the user.
   * @param {LoginContext} context The login parameters.
   * @return {Observable<Credentials>} The user credentials.
   */
  login(context: LoginContext): Observable<Credentials> {
    let formData: FormData = new FormData();
    formData.append('username', context.username);
    formData.append('password', context.password);

    return this.http.post(this.urlService.getServerUrl() + LOGIN_URL, formData)
      .map(response => response.json())
      .pipe(
        tap((response: any) => this.succesfullLogin(response, context)
      ));
  }

  /**
   * Authenticates the user via SSO
   * 
   * @param token 
   */
  ssoLogin(token: string) {
    return this.http.get(this.urlService.getServerUrl() + INFORMATION_URL, { headers: this.getAuthorizationHeaders(token) })
    .map(response => response.json())
    .pipe(
      tap((response: any) => this.succesfullLogin(response)
    ));
  }

  /**
   * SuccessFullLogin operation
   * 
   * @param response 
   * @param context 
   */
  private succesfullLogin(response :any, context? : LoginContext) {
    console.log("succesfullLogin");
    const data: Credentials = {
      username: response.user_data.userName,
      token: response.token,
      refresh_token: response.refresh_token,
      lang: response.user_data.locale,
      epiFirstDay: response.platform_data.epiFirstDay,
      homeSiteId: response.user_data.homeSiteId,
      homeSiteName: response.user_data.homeSiteName,
      translations: response.validation_translation,
      version: response.platform_data.version,
    };
    this.setCredentials(data, context? context.remember : false);
    this.init();
  }

  /**
   * Logs out the user and clear credentials.
   * @return {Observable<boolean>} True if the user was logged out successfully.
   */
  logout(): Observable<boolean> {
    // Customize credentials invalidation here
    this.setCredentials();
    return of(true);
  }

  /**
   * Checks is the user is authenticated.
   * @return {boolean} True if the user is authenticated.
   */
  isAuthenticated(): boolean {
    return !!this.credentials;
  }

  /**
   * Gets the user credentials.
   * @return {Credentials} The user credentials or null if the user is not authenticated.
   */
  get credentials(): Credentials | null {
    this._credentials = JSON.parse(sessionStorage.getItem(credentialsKey) || localStorage.getItem(credentialsKey));
    return this._credentials;
  }

  /**
   * Sets the user credentials.
   * The credentials may be persisted across sessions by setting the `remember` parameter to true.
   * Otherwise, the credentials are only persisted for the current session.
   * @param {Credentials=} credentials The user credentials.
   * @param {boolean=} remember True to remember credentials across sessions.
   */
  private setCredentials(credentials?: Credentials, remember?: boolean) {
    this._credentials = credentials || null;

    if (credentials) {
      const storage = remember ? localStorage : sessionStorage;
      storage.setItem(credentialsKey, JSON.stringify(credentials));
    } else {
      sessionStorage.removeItem(credentialsKey);
      localStorage.removeItem(credentialsKey);
    }
  }

  /**
   * Get Authorization Headers
   * 
   * @param token
   */
  getAuthorizationHeaders(token?: string): Headers {
     let headers = new Headers();
     let bearer = 'Bearer ' + (token? token : this.credentials.token) ;
     headers.set('Authorization', bearer);
     return headers;
 }

  public init() {
    this.setTranslations(this.credentials.lang, this.credentials.translations);
    this.initMoment(this.credentials.lang, this.credentials.epiFirstDay);
  }

  /**
   * Add the langage defined for the user
   * Add new translations
   * Use new langage
   * 
   * @param lang 
   * @param translations 
   */
  private setTranslations(lang: string, translations :Object) {
    console.log('setTranslations');
    this.translateService.addLangs([lang]);
    this.translateService.setTranslation(lang, translations, true);
    this.translateService.setDefaultLang('gb');
    this.translateService.use(lang);
    console.log(this.translateService);

  }

  /**
   * Init moment with lang
   * 
   * @param lang 
   * @param epiFirstDay 
   */
  private initMoment(lang:string, epiFirstDay:number) {    
    moment.locale(lang);

    moment.updateLocale(moment.locale(), {
      week: { 
        dow: epiFirstDay, // override first day of week
        doy: 4 // first week with at least 4 days in the year
      }
    });
  }

}
