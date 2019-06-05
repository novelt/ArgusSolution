import { DiseaseValue } from './../model/diseaseValue';
import { Disease } from './../model/disease';
import { DiseaseService } from './../service/disease/disease.service';
import { PeriodComponent, SelectedPeriodChangedEventArgs } from './../period/period.component';
import { FilterService } from './../service/filter/filter.service';
import { AuthenticationService } from './../../core/authentication/authentication.service';
import { SelectedDateRangeChangedEventArgs, WeeklyDateRangePicker } from './../date-range-picker/weekly/weekly-date-range-picker.component';
import { SelectedSitesChangedEventArgs, SiteCascadeComponent } from './../site/cascade/site-cascade.component';
import { Component, OnInit, ChangeDetectorRef, ViewChild } from '@angular/core';
import { Site } from '../model/site';
import { Moment } from 'moment';
import * as moment from 'moment';
import { Period } from '../constant/period';

export interface DateRange {
  dateStart: Moment;
  dateEnd: Moment;
}

@Component({
  selector: 'app-filter',
  templateUrl: './filter.component.html',
  styleUrls: ['./filter.component.scss']
})
export class FilterComponent implements OnInit {

  @ViewChild(WeeklyDateRangePicker) protected weeklyDateRangePicker:  WeeklyDateRangePicker;
  @ViewChild(SiteCascadeComponent) protected siteCascadeComponent:  SiteCascadeComponent;
  @ViewChild(PeriodComponent) protected periodComponent:  PeriodComponent;

  private epiFirstDay:number = this.authenticationService.credentials.epiFirstDay;

  selectedSites: Site[] = new Array<Site>();
  selectedDateRange: DateRange = { dateStart: moment(), dateEnd: moment() } ;
  selectedPeriod: string;
 
  startWeekNumber:string;
  endWeekNumber:string;

  showFilter:boolean = false ;

  // Depend on Disease Configuration
  showTypeFilter:boolean = false;

  constructor(private cdr: ChangeDetectorRef,
              private authenticationService: AuthenticationService,
              private filterService: FilterService,
              private diseaseService: DiseaseService) { }

  ngOnInit() {
    this.initTypeFilterVisibility(); 
  }

  private initTypeFilterVisibility() {
    this.diseaseService.getListOfDiseases().subscribe(
      diseases => {
        let weekly = false ;
        let monthly = false ;
        diseases.forEach((disease: Disease) => {
          disease.getDiseaseValues().forEach((value: DiseaseValue) => {
            if (value.getPeriod() == Period.WEEKLY) {
              weekly = true;
            }
            if (value.getPeriod() == Period.MONTHLY) {
              monthly = true;
            }
          });
          this.showTypeFilter = weekly && monthly;
        })
      }
    );
  }

  selectedSitesChange(eventArgs: SelectedSitesChangedEventArgs) {
    this.selectedSites = eventArgs.selectedSites;
    this.filterService.setSelectedSites(this.selectedSites);
  }

  selectedDateRangeChange(eventArgs: SelectedDateRangeChangedEventArgs) {
    this.selectedDateRange.dateStart = eventArgs.dateStart;
    this.selectedDateRange.dateEnd = eventArgs.dateEnd;
       
    let startWeek = moment(this.selectedDateRange.dateStart).isoWeekday(this.epiFirstDay);
    let endWeek = moment(this.selectedDateRange.dateEnd).isoWeekday(this.epiFirstDay);

    this.startWeekNumber = startWeek.startOf("isoWeek").format('WW');
    this.endWeekNumber = endWeek.startOf("isoWeek").format('WW');

    this.filterService.setSelectedDateRange(this.selectedDateRange);
  }

  selectedPeriodChange(eventArgs: SelectedPeriodChangedEventArgs) {
    this.selectedPeriod = eventArgs.period;
    this.filterService.setSelectedPeriod(this.selectedPeriod);
  }

  showFilterContent($event:Event) {
    this.showFilter = !this.showFilter;
  }

  applyFilters($event:Event) {
    if (this.weeklyDateRangePicker != null) {
      this.weeklyDateRangePicker.emitSelectedDateRange();
    }
    if (this.siteCascadeComponent != null) {
      this.siteCascadeComponent.emitSelectedSites();
    }
    if (this.periodComponent != null) {
      this.periodComponent.emitSelectedPeriod();
    }
    
    this.showFilter = !this.showFilter;

    this.filterService.setFilterChanged(true);
  }

  ngAfterViewInit() {
    this.cdr.detectChanges();
  }
}
