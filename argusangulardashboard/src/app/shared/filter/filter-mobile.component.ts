import { PeriodMobileComponent } from './../period/period-mobile.component';
import { SiteCascadeMobileComponent } from './../site/cascade/site-cascade-mobile.component';
import { WeeklyDateRangePickerMobile } from './../date-range-picker/weekly/weekly-date-range-picker-mobile.component';
import { DiseaseService } from '../service/disease/disease.service';
import { FilterService } from '../service/filter/filter.service';
import { AuthenticationService } from '../../core/authentication/authentication.service';
import { Component, ChangeDetectorRef, ViewChild, Output, EventEmitter } from '@angular/core';
import { FilterComponent } from './filter.component';

export interface MobileApplyFilterEventArgs {
  closeMenu: boolean;
}

@Component({
  selector: 'app-filter-mobile',
  templateUrl: './filter-mobile.component.html',
  styleUrls: ['./filter-mobile.component.scss']
})
export class FilterMobileComponent extends FilterComponent {
  
  @ViewChild(WeeklyDateRangePickerMobile) protected weeklyDateRangePicker:  WeeklyDateRangePickerMobile;
  @ViewChild(SiteCascadeMobileComponent) protected siteCascadeComponent:  SiteCascadeMobileComponent;
  @ViewChild(PeriodMobileComponent) protected periodComponent:  PeriodMobileComponent;

  @Output('mobileApplyFilter') mobileApplyFilter: EventEmitter<MobileApplyFilterEventArgs> = new EventEmitter();

  constructor(cdr: ChangeDetectorRef,
              authenticationService: AuthenticationService,
              filterService: FilterService,
              diseaseService: DiseaseService) { 
                super(cdr, authenticationService, filterService, diseaseService);
              }

  applyMobileFilters($event:Event){
      this.applyFilters($event);
      
      this.mobileApplyFilter.emit({
        closeMenu: true
      });
  }
}
