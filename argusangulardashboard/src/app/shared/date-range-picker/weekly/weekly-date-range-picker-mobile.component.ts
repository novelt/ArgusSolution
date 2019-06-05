import { WeeklyDateRangePicker } from './weekly-date-range-picker.component';
import { TranslateService } from '@ngx-translate/core';
import { FilterService } from '../../service/filter/filter.service';
import { AuthenticationService } from '../../../core/authentication/authentication.service';
import { Component } from '@angular/core';
import { DaterangepickerConfig } from 'ng2-daterangepicker';

@Component({
  selector: 'app-weekly-date-picker-mobile',
  templateUrl: './weekly-date-range-picker-mobile.component.html',
  styleUrls: ['./weekly-date-range-picker-mobile.component.scss']
})
export class WeeklyDateRangePickerMobile extends WeeklyDateRangePicker {

  constructor(authenticationService: AuthenticationService,
              filterService: FilterService,
              translateService: TranslateService,
              daterangepickerOptions: DaterangepickerConfig) {
        super(authenticationService, filterService, translateService, daterangepickerOptions);
  }
}
