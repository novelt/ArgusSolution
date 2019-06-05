import { Component, OnInit, ChangeDetectorRef, Output, Input, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { AbstractReportService } from '../../service/report/abstract-report.service';
import { Period } from '../../constant/period';
import { Report } from '../../model/report';

@Component({
  selector: 'app-report-header',
  templateUrl: './report-header.component.html',
  styleUrls: ['./report-header.component.scss'],
})
export class ReportHeaderComponent implements OnInit {

  @Output('isMenuOpen') isMenuOpen: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Input('displayHomeSiteName') displayHomeSiteName: boolean;
  @Input('title') title: string;
  @Input('withFilters') withFilters: boolean;

  reportStatusList : Array<{status :string, count:number}> ;
  period: string;

  isStatusesCollapsed = true;
  isPeriodCollapsed = true ;
  isFilterCollapsed = true ;

  constructor(private cdr: ChangeDetectorRef,
              private reportService: AbstractReportService,
              private router: Router) { 
    this.reportStatusList = new Array();
    this.period = Period.WEEKLY;
  }

  ngOnInit() {
    // https://github.com/angular/angular/issues/21788
    this.cdr.detectChanges();

    this.reportService.getStatusList().subscribe(
      statusList => this.reportStatusList = statusList
    );
  }

  onPeriodChange(period: string) {
    this.period = period;
    this.reportService.changeSelectedPeriod(period);
    this.hideMenus();
  }

  existsReport(period: string) {
    return this.reportService.getLoadedListOfReportsReports()
      .filter((report: Report) => report.getPeriod() == period).length > 0 ;
  }

  goToMenu() {
    this.router.navigate(['m/menu']);
  }

  collapseMenuStatuses() {
    this.isStatusesCollapsed = !this.isStatusesCollapsed;
    this.isPeriodCollapsed = true;
    this.isFilterCollapsed = true;
    this.emitMenuEvent();
  }

  collapseMenuPeriod() {
    this.isPeriodCollapsed = !this.isPeriodCollapsed;
    this.isStatusesCollapsed = true;
    this.isFilterCollapsed = true;
    this.emitMenuEvent();
  }

  collapseMenuFilter() {
    this.isFilterCollapsed = !this.isFilterCollapsed;
    this.isStatusesCollapsed = true;
    this.isPeriodCollapsed = true;
    this.emitMenuEvent();
  }

  getHomeSiteName() {
    return this.reportService.getHomeSiteName();
  }

  hideMenus() {
    this.isPeriodCollapsed = true;
    this.isStatusesCollapsed = true;
    this.isFilterCollapsed = true;
    this.emitMenuEvent();
  }

  private emitMenuEvent() {
    this.isMenuOpen.emit(!this.isStatusesCollapsed || !this.isPeriodCollapsed || !this.isFilterCollapsed);
  }

}