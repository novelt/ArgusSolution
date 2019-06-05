import { ValidationComponent } from './validation.component';
import { Report } from './../shared/model/report';
import { Component, OnInit, ViewChild, ChangeDetectorRef } from '@angular/core';
import { AbstractReportService } from '../shared/service/report/abstract-report.service';

@Component({
  selector: 'app-validation-mobile',
  templateUrl: './validation-mobile.component.html',
  styleUrls: ['./validation-mobile.component.scss']
})
export class ValidationMobileComponent extends ValidationComponent implements OnInit {

  isActive = false;

  constructor(reportService: AbstractReportService,
              cdr: ChangeDetectorRef) {
    super(reportService, cdr);
  }

  ngOnInit() {
    super.ngOnInit();
  }

  menuOpen($event: boolean) {
    this.isActive = $event;
  }
}
