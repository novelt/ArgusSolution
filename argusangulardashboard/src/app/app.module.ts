import { AuthenticationService } from './core/authentication/authentication.service';
import { AnalysesModule } from './analyses/analyses.module';
import { BrowserModule } from '@angular/platform-browser';
import { NgModule, APP_INITIALIZER } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';
import { TranslateModule } from '@ngx-translate/core';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';

import { CoreModule } from './core/core.module';
import { SharedModule } from './shared/shared.module';
import { ValidationModule } from './validation/validation.module';
import { HomeModule } from './home/home.module';
import { AboutModule } from './about/about.module';
import { LoginModule } from './login/login.module';
import { AppConfig } from './core/app-config';

import { registerLocaleData } from '@angular/common';
import localeFR from '@angular/common/locales/fr';
import { Ng4LoadingSpinnerModule } from 'ng4-loading-spinner';
import { Daterangepicker } from 'ng2-daterangepicker';

import { MalihuScrollbarModule } from 'ngx-malihu-scrollbar';


registerLocaleData(localeFR);

@NgModule({
  imports: [
    BrowserModule,
    FormsModule,
    HttpModule,
    TranslateModule.forRoot(),
    NgbModule.forRoot(),
    CoreModule,
    SharedModule,
    HomeModule,
    AboutModule,
    ValidationModule,
    LoginModule,
    AnalysesModule,
    AppRoutingModule,
    Daterangepicker,
    Ng4LoadingSpinnerModule.forRoot(),
    MalihuScrollbarModule.forRoot(),
  ],
  declarations: [AppComponent],
  providers: [
    AppConfig,
    { 
      provide: APP_INITIALIZER, useFactory: (config: AppConfig) => () => config.load(), deps: [AppConfig], multi: true 
    },
    // {
    //   provide: PERFECT_SCROLLBAR_CONFIG,
    //   useValue: DEFAULT_PERFECT_SCROLLBAR_CONFIG
    // }
   /* {
      provide: ErrorHandler, useClass: AppErrorHandler
    },*/
  ],
  bootstrap: [AppComponent]
})
export class AppModule {
  constructor(private authenticationService : AuthenticationService) {
    // Re init moment & translations    
    if (this.authenticationService.isAuthenticated()) {
      this.authenticationService.init();
    }
  }
 }
