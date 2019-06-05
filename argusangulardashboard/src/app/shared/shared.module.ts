import { SiteDropDownMobileComponent } from './site/dropdown/site-dropdown-mobile.component';
import { WeeklyDateRangePickerMobile } from './date-range-picker/weekly/weekly-date-range-picker-mobile.component';
import { PeriodMobileComponent } from './period/period-mobile.component';
import { MalihuScrollbarModule, MalihuScrollbarService } from 'ngx-malihu-scrollbar';
import { RouterModule } from '@angular/router';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { ReportHeaderComponent } from './report/report-header/report-header.component';
import { ReportDiseaseComponent } from './report/report-details/report-disease/report-disease.component';
import { DiseaseService } from './service/disease/disease.service';
import { FilterService } from './service/filter/filter.service';
import { SafePipe } from './pipe/safe-pipe.pipe';
import { AnalyseReportService } from './service/analyse/analyse-report.service';
import { Daterangepicker } from 'ng2-daterangepicker';
import { FilterComponent } from './filter/filter.component';
import { SiteDropDownComponent } from './site/dropdown/site-dropdown.component';
import { EpidemiologicReportService } from './service/epidemiologic/epidemiologic-report.service';
import { WeeklyReportComponent } from './weekly-report/weekly-report.component';
import { AlertComponent } from './alert/alert.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LoaderComponent } from './loader/loader.component';
import { LocalizedDatePipe } from './pipe/localized-date-pipe.pipe';
import { TranslateService, TranslateModule } from '@ngx-translate/core';
import { ConfirmComponent } from './confirm/confirm.component';
import { SiteCascadeComponent } from './site/cascade/site-cascade.component';
import { SiteService } from './service/site/site.service';
import { FormsModule } from '@angular/forms';
import { WeeklyDateRangePicker } from './date-range-picker/weekly/weekly-date-range-picker.component';
import { PeriodComponent } from './period/period.component';
import { ReportModalComponent } from './report-modal/report-modal.component';
import { ParticipationModalComponent } from './participation-modal/participation-modal.component';
import { ReportListComponent } from './report/report-list/report-list.component';
import { ReportDetailsComponent } from './report/report-details/report-details.component';
import { AutofocusDirective } from './directive/autofocus.directive';
import { UrlService } from './service/url/url.service';
import { FilterMobileComponent } from './filter/filter-mobile.component';
import { SiteCascadeMobileComponent } from './site/cascade/site-cascade-mobile.component';
import { ScriptService } from './service/r/script.service';

@NgModule({
  imports: [
    FormsModule,
    CommonModule,
    Daterangepicker,
    NgbModule,
    RouterModule,
    TranslateModule,
    MalihuScrollbarModule
  ],
  declarations: [
    LoaderComponent,
    LocalizedDatePipe,
    SafePipe,
    ConfirmComponent,
    AlertComponent,
    WeeklyReportComponent,
    SiteCascadeComponent,
    SiteCascadeMobileComponent,
    SiteDropDownComponent,
    SiteDropDownMobileComponent,
    FilterComponent,
    FilterMobileComponent,
    WeeklyDateRangePicker,
    WeeklyDateRangePickerMobile,
    PeriodComponent,
    PeriodMobileComponent,
    ReportModalComponent,
    ParticipationModalComponent,
    ReportListComponent,
    ReportDetailsComponent,
    ReportDiseaseComponent,
    ReportHeaderComponent,
    AutofocusDirective
    
  ],
  exports: [
    LoaderComponent, 
    LocalizedDatePipe,
    SafePipe, 
    ConfirmComponent, 
    AlertComponent, 
    WeeklyReportComponent, 
    SiteCascadeComponent,
    SiteDropDownComponent,
    FilterComponent,
    WeeklyDateRangePicker,
    PeriodComponent,
    ReportModalComponent,
    ParticipationModalComponent,
    ReportListComponent,
    ReportDetailsComponent,
    ReportDiseaseComponent,
    ReportHeaderComponent,
    TranslateModule
  ],
  providers: [
    TranslateService, 
    EpidemiologicReportService, 
    SiteService, 
    AnalyseReportService, 
    FilterService, 
    DiseaseService, 
    UrlService,
    MalihuScrollbarService,
    ScriptService
  ]
})
export class SharedModule { }
