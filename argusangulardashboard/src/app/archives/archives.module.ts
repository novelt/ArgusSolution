import { RouterModule } from '@angular/router';
import { ReportService } from './service/report.service';
import { SharedModule } from './../shared/shared.module';
import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { ArchivesComponent } from './archives.component';
import { AbstractReportService } from '../shared/service/report/abstract-report.service';
import { ArchivesMobileRoutingModule, ArchivesDesktopRoutingModule } from './archives-routing.module';
import { ArchivesMobileComponent } from './archives-mobile.component';

@NgModule({
  imports: [
    SharedModule,
    RouterModule,
    TranslateModule
  ],
  declarations: [
    ArchivesComponent,
    ArchivesMobileComponent
  ],
  entryComponents: [
  ],
})
export class ArchivesModule { }

@NgModule({
  imports: [
    ArchivesMobileRoutingModule
  ],
  providers: [
    {
      // provide ReportService for Token AbstractReportService. --> Lazy load the module to override this provider
      provide: AbstractReportService, useClass: ReportService 
    },
  ]
})
export class ArchivesMobileModule extends ArchivesModule { }

@NgModule({
  imports: [
    ArchivesDesktopRoutingModule
  ],
  providers: [
    {
      // provide ReportService for Token AbstractReportService. --> Lazy load the module to override this provider
      provide: AbstractReportService, useClass: ReportService 
    },
  ]
})
export class ArchivesDesktopModule extends ArchivesModule { }
