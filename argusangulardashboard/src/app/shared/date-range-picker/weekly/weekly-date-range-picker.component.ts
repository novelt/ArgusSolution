import { TranslateService } from '@ngx-translate/core';
import { FilterService } from './../../service/filter/filter.service';
import { AuthenticationService } from './../../../core/authentication/authentication.service';
import { Component, OnInit,  Output, EventEmitter } from '@angular/core';
import { DaterangepickerConfig } from 'ng2-daterangepicker';
import * as moment from 'moment';
import { Observable } from 'rxjs';

export interface SelectedDateRangeChangedEventArgs {
  dateStart: moment.Moment;
  dateEnd: moment.Moment;
}


@Component({
  selector: 'app-weekly-date-picker',
  templateUrl: './weekly-date-range-picker.component.html',
  styleUrls: ['./weekly-date-range-picker.component.scss']
})
export class WeeklyDateRangePicker implements OnInit {

  private epiFirstDay = this.authenticationService.credentials.epiFirstDay;
  private dateStart: moment.Moment; 
  private dateEnd: moment.Moment; 

  @Output('selectedDateRangeChange') selectedDateRangeChange: EventEmitter<SelectedDateRangeChangedEventArgs> = new EventEmitter();

  public options: any ;

  constructor(private authenticationService: AuthenticationService,
              private filterService: FilterService,
              private translateService: TranslateService,
              private daterangepickerOptions: DaterangepickerConfig) {

    // Init data
    let dateRange = this.filterService.getSelectedDateRange();

    if (dateRange != null && dateRange.dateStart != null && dateRange.dateEnd != null)  {    
      console.log('init with service store');
      this.dateStart = dateRange.dateStart;
      this.dateEnd = dateRange.dateEnd;
    } else {
      this.initPickerLast4Weeks();
    }

    // Init Options
    this.options = {
      alwaysShowCalendars: false,
      showDropdowns: true,
      showWeekNumbers: true,
      linkedCalendars: false,
      startDate: this.dateStart,
      endDate: this.dateEnd,
      opens: "center",
    };
  }

  ngOnInit() {

    Observable.combineLatest(
      this.translateService.get('filter.calendar.weekly.last_week'),
      this.translateService.get('filter.calendar.weekly.last_4_weeks'),
      this.translateService.get('filter.calendar.weekly.year_to_date'),
      this.translateService.get('filter.calendar.button.apply'),
      this.translateService.get('filter.calendar.button.cancel'),
      this.translateService.get('filter.calendar.custom'))
        .subscribe(
            result => { 
              console.log(result);
              this.daterangepickerOptions.settings = {
                ranges: {
                  [result[0]]: [moment().subtract(7, 'days').day(this.epiFirstDay), moment().subtract(7, 'days').day(this.epiFirstDay + 6)],
                  [result[1]]: [moment().subtract(28, 'days').day(this.epiFirstDay), moment().subtract(7, 'days').day(this.epiFirstDay + 6)],
                  [result[2]]: [moment().startOf('year').add(3, 'days').day(this.epiFirstDay), moment().day(this.epiFirstDay + 6)],
                },
                locale: {
                  applyLabel: result[3],
                  cancelLabel: result[4],
                  customRangeLabel: result[5],
                },
              };
            }
    );

    this.emitSelectedDateRange();
  }

  private initPickerLast4Weeks() {
    console.log('init with last 4 weeks');
    this.dateStart = moment().subtract(28, 'days').day(this.epiFirstDay);
    this.dateEnd = moment().subtract(7, 'days').day(this.epiFirstDay + 6);
  } 

  public selectedDate(value: any) {
    // any object can be passed to the selected event and it will be passed back here
    this.dateStart = value.start;
    this.dateEnd = value.end
  }

  /**
   * When calendar apply, update range to take full weeks
   * 
   * @param event 
   */
  public calendarApplied(event:any) {
    console.log('calendarApplied');
    console.log(event.picker);

    let picker = event.picker;
    picker.setStartDate(picker.startDate.day(this.epiFirstDay));

    if (picker.endDate.day() != ((this.epiFirstDay +6) % 7)) {
      picker.setEndDate(picker.endDate.day(this.epiFirstDay +6));
    }
  }

  public emitSelectedDateRange() {
    this.selectedDateRangeChange.emit({
      dateStart: this.dateStart,
      dateEnd: this.dateEnd
    });
  }
}
