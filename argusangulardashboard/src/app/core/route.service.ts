import { Routes } from '@angular/router';
import { ShellMobileComponent } from './shell/shell-mobile.component';
import { ShellComponent } from './shell/shell.component';
import { AuthenticationGuard } from './authentication/authentication.guard';

/**
 * Provides helper methods to create routes.
 */
export class Route {

  /**
   * Creates routes using the shell component and authentication.
   * @param routes The routes to add.
   * @return {Routes} The new routes using shell as the base.
   */
  static withShell(routes: Routes): Routes {
    return [{
      path: '',
      component: ShellComponent,
      children: routes,
      canActivate: [AuthenticationGuard],
      // Reuse ShellComponent instance when navigating between child views
      data: { reuse: true }
    }];
  }

  static withShellMobile(routes : Routes) : Routes {
    return [{
      path: '',
      component: ShellMobileComponent,
      children: routes,
      canActivate: [AuthenticationGuard],
      // Reuse ShellComponent instance when navigating between child views
      data: { reuse: true }
    }];
  }

}
