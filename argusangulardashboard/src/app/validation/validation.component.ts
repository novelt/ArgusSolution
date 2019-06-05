import { Period } from './../shared/constant/period';
import { Report } from './../shared/model/report';
import { Component, OnInit, ViewChild, ChangeDetectorRef } from '@angular/core';
import { AbstractReportService } from '../shared/service/report/abstract-report.service';

@Component({
  selector: 'app-validation',
  templateUrl: './validation.component.html',
  styleUrls: ['./validation.component.scss']
})
export class ValidationComponent implements OnInit {

  existReports: boolean = true;
  private period: string;

  constructor(private reportService: AbstractReportService,
              private cdr: ChangeDetectorRef) {

    this.period = Period.WEEKLY;
  }

  ngOnInit() {
    // https://github.com/angular/angular/issues/21788
    this.cdr.detectChanges();

    this.reportService.getPeriod().subscribe(
      period => {
        this.period = period;
      }
    );
  }

  displayReports() {
    return this.existReports;
  }

  changeVisibility(reportNumber: number) {
    this.existReports = (reportNumber > 0);
  }

  displayWeeklyEpidemiologicReport() {
    return (!this.existReports && this.period == Period.WEEKLY);
  }
}
