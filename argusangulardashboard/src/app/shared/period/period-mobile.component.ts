import { PeriodComponent } from './period.component';
import { FilterService } from '../service/filter/filter.service';
import { Component } from '@angular/core';


@Component({
  selector: 'app-period-mobile',
  templateUrl: './period-mobile.component.html',
  styleUrls: ['./period-mobile.component.scss']
})
export class PeriodMobileComponent extends PeriodComponent {

  constructor(filterService: FilterService) {
      super(filterService);
  }
}
