import { TestBed, inject, fakeAsync, tick } from '@angular/core/testing';
import { MockBackend, MockConnection } from '@angular/http/testing';
import {
  Http, HttpModule, XHRBackend, ResponseOptions,
  Response, BaseRequestOptions
} from '@angular/http';

import { AuthenticationService, Credentials} from './authentication.service';

const credentialsKey = 'credentials';

describe('AuthenticationService', () => {
  let authenticationService: AuthenticationService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [
        {
          provide: Http, useFactory: (backend : any, options : any) => {
          return new Http(backend, options);
          },
          deps: [MockBackend, BaseRequestOptions]
        },
        MockBackend,
        BaseRequestOptions,
        AuthenticationService
        ]
    });
  });

  beforeEach(inject([
    AuthenticationService
  ], (_authenticationService: AuthenticationService) => {
    authenticationService = _authenticationService;
  }));

  afterEach(() => {
    // Cleanup
    localStorage.removeItem(credentialsKey);
    sessionStorage.removeItem(credentialsKey);
  });

  describe('login', () => {
    it('should return credentials', fakeAsync(() => {
      // Act
      const request = authenticationService.login({
        username: 'ArgusAdmin',
        password: '7mileGREAT96#'
      });
      tick();

      // Assert
      request.subscribe(credentials => {
        expect(credentials).toBeDefined();
        expect(credentials.token).toBeDefined();
      });
    }));

    it('should authenticate user', fakeAsync(() => {
      expect(authenticationService.isAuthenticated()).toBe(false);

      // Act
      const request = authenticationService.login({
        username: 'ArgusAdmin',
        password: '7mileGREAT96#'
      });
      tick();

      // Assert
      request.subscribe(() => {
        expect(authenticationService.isAuthenticated()).toBe(true);
        expect(authenticationService.credentials).toBeDefined();
        expect(authenticationService.credentials).not.toBeNull();
        expect((<Credentials>authenticationService.credentials).token).toBeDefined();
        expect((<Credentials>authenticationService.credentials).token).not.toBeNull();
      });
    }));

    it('should persist credentials for the session', fakeAsync(() => {
      // Act
      const request = authenticationService.login({
        username: 'ArgusAdmin',
        password: '7mileGREAT96#'
      });
      tick();

      // Assert
      request.subscribe(() => {
        expect(sessionStorage.getItem(credentialsKey)).not.toBeNull();
      });
    }));

    it('should persist credentials across sessions', fakeAsync(() => {
      // Act
      const request = authenticationService.login({
        username: 'ArgusAdmin',
        password: '7mileGREAT96#',
        remember: true
      });
      tick();

      // Assert
      request.subscribe(() => {
        expect(localStorage.getItem(credentialsKey)).not.toBeNull();
      });
    }));
  });

  describe('logout', () => {
    it('should clear user authentication', fakeAsync(() => {
      // Arrange
      const loginRequest = authenticationService.login({
        username: 'ArgusAdmin',
        password: '7mileGREAT96#'
      });
      tick();

      // Assert
      loginRequest.subscribe(() => {
        expect(authenticationService.isAuthenticated()).toBe(true);

        const request = authenticationService.logout();
        tick();

        request.subscribe(() => {
          expect(authenticationService.isAuthenticated()).toBe(false);
          expect(authenticationService.credentials).toBeNull();
          expect(sessionStorage.getItem(credentialsKey)).toBeNull();
          expect(localStorage.getItem(credentialsKey)).toBeNull();
        });
      });
    }));

    it('should clear persisted user authentication', fakeAsync(() => {
      // Arrange
      const loginRequest = authenticationService.login({
        username: 'ArgusAdmin',
        password: '7mileGREAT96#',
        remember: true
      });
      tick();

      // Assert
      loginRequest.subscribe(() => {
        expect(authenticationService.isAuthenticated()).toBe(true);

        const request = authenticationService.logout();
        tick();

        request.subscribe(() => {
          expect(authenticationService.isAuthenticated()).toBe(false);
          expect(authenticationService.credentials).toBeNull();
          expect(sessionStorage.getItem(credentialsKey)).toBeNull();
          expect(localStorage.getItem(credentialsKey)).toBeNull();
        });
      });
    }));
  });
});
