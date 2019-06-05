import { Observable } from 'rxjs/Observable';
import { of } from 'rxjs/observable/of';

import { Credentials, LoginContext } from './authentication.service';

export class MockAuthenticationService {

  credentials: Credentials | null = {
    username: 'ArgusAdmin',
    token: '',
    refresh_token: '',
    lang: 'fr',
    epiFirstDay: 1,
    homeSiteId: 723,
    homeSiteName: 'Home',
    translations: [],
  };

  login(context: LoginContext): Observable<Credentials> {
    return of(this.credentials);
  }

  logout(): Observable<boolean> {
    this.credentials = null;
    return of(true);
  }

  isAuthenticated(): boolean {
    return !!this.credentials;
  }

}
