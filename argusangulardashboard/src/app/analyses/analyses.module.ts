import { MalihuScrollbarService } from 'ngx-malihu-scrollbar';
import { FormsModule } from '@angular/forms';
import { ReportModalComponent } from './../shared/report-modal/report-modal.component';
import { FilterComponent } from './../shared/filter/filter.component';
import { AnalysesComponent } from './analyses.component';
import { AnalysesRoutingModule } from './analyses-routing.module';
import { SharedModule } from './../shared/shared.module';
import { CoreModule } from './../core/core.module';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';

@NgModule({
  imports: [
    CommonModule,
    TranslateModule,
    AnalysesRoutingModule,
    CoreModule,
    SharedModule,
    FormsModule
  ],
  declarations: [
    AnalysesComponent
  ],
  entryComponents: [
    FilterComponent,
    ReportModalComponent
  ],
  providers: [
    MalihuScrollbarService
  ]
})
export class AnalysesModule { }
