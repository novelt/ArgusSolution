import { AuthenticationService } from './../../authentication/authentication.service';
import { TranslateService } from '@ngx-translate/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router } from '@angular/router';
import { AlertComponent } from '../../../shared/alert/alert.component';

@Component({
  selector: 'app-side',
  templateUrl: './side.component.html',
  styleUrls: ['./side.component.scss']
})
export class SideComponent implements OnInit, AfterViewInit {

  private onlyAvailableOnDestkopTitle: string ;
  private onlyAvailableOnDestkopBody: string ;

  constructor(private router: Router,
              private modalService: NgbModal,
              private translateService: TranslateService,
              private authenticationService: AuthenticationService) {
              }

  ngOnInit() { 
    this.initTranslation();
  }

  private initTranslation() {
    this.translateService.get('menu.only_desktop.title').subscribe((res: string) => {this.onlyAvailableOnDestkopTitle = res;});
    this.translateService.get('menu.only_desktop.body').subscribe((res: string) => {this.onlyAvailableOnDestkopBody = res;});
  }

  ngAfterViewInit() {

  }

  getVersion() {
    return this.authenticationService.credentials.version;
  }

  goToHome($event : Event) {
    this.tryRedirectTo('home', { });
  }

  goToValidation($event : Event) {
    this.tryRedirectTo('validation', { sridx : 0, load: true });
  }

  goToAnalyses($event : Event) {
    this.tryRedirectTo('analyses', {});
  }

  goToArchives($event : Event) {
    this.tryRedirectTo('archives', {});
  }

  goToAlerts($event : Event) {
    
  }

  private tryRedirectTo(name: string, params: { [key: string]: any;} ) {
    if (window.screen.width <= 480) {
      // Module Archives and Analyses no accessible
      if (name == 'analyses' ) { // || name == 'archives'
          // do nothing !
          // Display a alert message
          const modalRef = this.modalService.open(AlertComponent);
          modalRef.componentInstance.title = this.onlyAvailableOnDestkopTitle;
          modalRef.componentInstance.body = this.onlyAvailableOnDestkopBody;

          this.redirectTo('m/menu', params);
      } else {
        this.redirectTo('m/' + name, params);
      }
    } else {
      this.redirectTo('/' + name, params);
    }
  }

  private redirectTo(route: string, params: { [key: string]: any;} ) {
      this.router.navigate([route], { queryParams: params });

  }
}
