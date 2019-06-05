import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router } from '@angular/router';

import { AuthenticationService } from '../../authentication/authentication.service';
import { I18nService } from '../../i18n.service';

import * as $ from 'jquery';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss']
})
export class HeaderComponent implements OnInit, AfterViewInit {

  menuHidden = true;

  constructor(private router: Router,
    private authenticationService: AuthenticationService,
    private i18nService: I18nService) {

  }

  ngOnInit() { }

  ngAfterViewInit() {
    // Open navbarSide when button is clicked
    $('#navbarSideButton').on('click', function () {
      $('#navbarSide').addClass('reveal');
      $('.overlay').show();
    });

    // Close navbarSide when the outside of menu is clicked
    $('.overlay').on('click', function () {
      $('#navbarSide').removeClass('reveal');
      $('.overlay').hide();
    });
  }

  toggleMenu() {
    this.menuHidden = !this.menuHidden;
  }

  setLanguage(language: string) {
    this.i18nService.language = language;
  }
  
  gotToMenu($event: Event) {
    this.router.navigate(['m/menu']);
    this.hideMenu();
  }

  hideMenu() {
    $('#navbarSide').removeClass('reveal');
    $('.overlay').hide();
  }

  logout($event: Event) {
    this.authenticationService.logout()
      .subscribe(() => this.router.navigate(['/login'], { replaceUrl: true }));
  }

  get currentLanguage(): string {
    return this.i18nService.language;
  }

  get languages(): string[] {
    return this.i18nService.supportedLanguages;
  }

  get username(): string | null {
    const credentials = this.authenticationService.credentials;
    return credentials ? credentials.username : null;
  }

}
