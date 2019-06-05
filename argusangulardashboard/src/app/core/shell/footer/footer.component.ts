import { AuthenticationService } from './../../authentication/authentication.service';
import { Component, OnInit, AfterViewInit } from '@angular/core';
import { AppConfig } from '../../app-config';

@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html',
  styleUrls: ['./footer.component.scss']
})
export class FooterComponent implements OnInit, AfterViewInit {

  public lang = "";
  public imgOther = "";

  constructor(private authenticationService: AuthenticationService,
              private config: AppConfig) {
    this.lang = this.authenticationService.credentials.lang;
  }

  ngOnInit() {
    this.imgOther = this.config.getConfig("otherLogo") ;
  }

  ngAfterViewInit() {
  }
}
