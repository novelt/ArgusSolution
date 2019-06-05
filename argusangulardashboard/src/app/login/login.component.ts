import { FilterService } from './../shared/service/filter/filter.service';
import { SiteService } from './../shared/service/site/site.service';
import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';
import { finalize } from 'rxjs/operators';

import { environment } from '../../environments/environment';
import { Logger } from '../core/logger.service';
import { I18nService } from '../core/i18n.service';
import { AuthenticationService } from '../core/authentication/authentication.service';
import { AbstractReportService } from '../shared/service/report/abstract-report.service';

const log = new Logger('Login');

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

  version: string = environment.version;
  error: string;
  loginForm: FormGroup;
  isLoading = false;

  constructor(private router: Router,
              private activatedRoute: ActivatedRoute,
              private formBuilder: FormBuilder,
              private i18nService: I18nService,
              private authenticationService: AuthenticationService,
              private siteService: SiteService,
              private filterService: FilterService,
              private reportService: AbstractReportService) {
    this.createForm();

    this.emptyServiceData();
  }

  ngOnInit() { 
    // SSO authentication
    this.activatedRoute.queryParams.subscribe(params => {
      let token = params['bearer'];
      if (token) {
        this.ssoLogin(token);
      }
    });
  }

  login() {
    this.isLoading = true;
    this.authenticationService.login(this.loginForm.value)
      .pipe(finalize(() => {
        this.loginForm.markAsPristine();
        this.isLoading = false;
      }))
      .subscribe(
        response => this.redirectUser(), 
        error => this.manageLoginError(error)
      );
  }

  ssoLogin(token: string){
    this.isLoading = true ;
    this.authenticationService.ssoLogin(token)
    .pipe(finalize(() => {
      this.isLoading = false;
    }))
    .subscribe(
      response => this.redirectUser(), 
      error => this.manageLoginError(error)
    );
  }

  private redirectUser() {
    let redirectRoute = 'validation';
    // Now redirect to mobile or desktop views
    if (window.screen.width <= 480) {
      // Mobile routes
      redirectRoute = 'm/menu';
    }
    this.router.navigate([redirectRoute], { replaceUrl: true, queryParams : { sridx : 0, load : true } });
  }

  private manageLoginError(error: any) {
    log.debug(`Login error: ${error}`);
    this.error = error;
  }

  setLanguage(language: string) {
    this.i18nService.language = language;
  }

  get currentLanguage(): string {
    return this.i18nService.language;
  }

  get languages(): string[] {
    return this.i18nService.supportedLanguages;
  }

  private createForm() {
    this.loginForm = this.formBuilder.group({
      username: ['', Validators.required],
      password: ['', Validators.required],
      remember: false
    });
  }

  private emptyServiceData() {
    this.siteService.emptyData();
    this.filterService.emptyData();
    this.reportService.emptyData();
  }
}
