/*
 * Use the Page Object pattern to define the page under test.
 * See docs/coding-guide/e2e-tests.md for more info.
 */

import { browser, element, by } from 'protractor';

export class AppPage {
  usernameField = element(by.css('input[formControlName="username"]'));
  passwordField = element(by.css('input[formControlName="password"]'));
  loginButton = element(by.css('button[type="submit"]'));

  constructor() {
    // Forces default language
    this.navigateTo();
    browser.executeScript(() => localStorage.setItem('language', 'en-US'));
  }

  navigateTo() {
    return browser.get('/');
  }

  login() {
    this.usernameField.sendKeys('ArgusAdmin');
    this.passwordField.sendKeys('7mileGREAT96#');
    this.loginButton.click();
  }

  getParagraphText() {
    return element(by.css('app-root h1')).getText();
  }
}
