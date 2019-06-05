import { MalihuScrollbarService } from 'ngx-malihu-scrollbar';
import { ReportDiseaseComponent } from './../shared/report/report-details/report-disease/report-disease.component';
import { WeeklyReportComponent } from './../shared/weekly-report/weekly-report.component';
import { ValidationMobileComponent } from './validation-mobile.component';
import { AlertComponent } from './../shared/alert/alert.component';
import { ConfirmComponent } from './../shared/confirm/confirm.component';
import { FormsModule } from '@angular/forms';

import { RouterModule } from '@angular/router';
import { ValidationComponent } from './validation.component';
import { ValidationRoutingModule } from './validation-routing.module';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule, TranslateService } from '@ngx-translate/core';

import { CoreModule } from '../core/core.module';
import { SharedModule } from '../shared/shared.module';

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { ParticipationModalComponent } from '../shared/participation-modal/participation-modal.component';
import { ReportListComponent } from '../shared/report/report-list/report-list.component';
import { ReportDetailsComponent } from '../shared/report/report-details/report-details.component';
import { AbstractReportService } from '../shared/service/report/abstract-report.service';
import { TodoReportService } from './service/todo-report.service';

@NgModule({
  imports: [
    CommonModule,
    TranslateModule,
    CoreModule,
    ValidationRoutingModule,
    SharedModule,
    RouterModule,
    FormsModule,
    NgbModule,
  ],
  declarations: [
    ValidationComponent, 
    ValidationMobileComponent,
  ],
  providers: [
    {
      provide: AbstractReportService, useClass: TodoReportService // Use TodoReportService for validation module
    },
    TranslateService,
    MalihuScrollbarService
  ],
  entryComponents: [
    ValidationComponent, 
    ReportDetailsComponent, 
    ReportDiseaseComponent,
    ConfirmComponent, 
    AlertComponent,
    ParticipationModalComponent, 
    WeeklyReportComponent,
    ReportListComponent,
  ]
})
export class ValidationModule { }
