import { FilterService } from './../service/filter/filter.service';
import { Component, OnInit, Output, EventEmitter } from '@angular/core';

export interface SelectedPeriodChangedEventArgs {
  period: string;
}

@Component({
  selector: 'app-period',
  templateUrl: './period.component.html',
  styleUrls: ['./period.component.scss']
})
export class PeriodComponent implements OnInit {

  period: string = 'Weekly';

  @Output('selectedPeriodChange') selectedPeriodChange: EventEmitter<SelectedPeriodChangedEventArgs> = new EventEmitter();

  constructor(private filterService: FilterService) {

    // Init data
    if (this.filterService.getSelectedPeriod() != null) {
      this.period = this.filterService.getSelectedPeriod();
    }
  }

  ngOnInit() { 
    this.emitSelectedPeriod();
  }

  setPeriod(period: string) {
    this.period = period;
  }

  public emitSelectedPeriod() {
    this.selectedPeriodChange.emit({
      period: this.period
    });
  }
}
