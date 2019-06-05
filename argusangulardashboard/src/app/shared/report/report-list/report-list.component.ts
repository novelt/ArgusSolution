import { MalihuScrollbarService } from 'ngx-malihu-scrollbar';
import { FilterService } from './../../service/filter/filter.service';
import { Period } from './../../constant/period';
import { ISubscription } from 'rxjs/Subscription';
import { Router, ActivatedRoute } from '@angular/router';
import { Component, OnInit,  Output, EventEmitter, Input } from '@angular/core';
import 'rxjs/add/operator/finally';
import { Report } from '../../model/report';
import { AbstractReportService } from '../../service/report/abstract-report.service';

@Component({
  selector: 'app-report-list',
  templateUrl: './report-list.component.html',
  styleUrls: ['./report-list.component.scss']
})
export class ReportListComponent implements OnInit {
  public reportList: Report[];

  private selectedReport: Report ;
  private reportIndex: number ;
  private autoLoad: boolean ;
  private period: string;

  private periodObservable: ISubscription;
  private filterObservable: ISubscription;
  private reportsObservable: ISubscription;
  private queryParamObservable: ISubscription;

  @Output('numberOfReportChange') numberOfReportChange: EventEmitter<number> = new EventEmitter();
  // Activate the malihu scroll only on desktop
  @Input('malihuScroll') malihuScroll: boolean = true;
  
  constructor(private abstractReportService: AbstractReportService, 
              private router: Router,
              private activatedRoute: ActivatedRoute,
              private filterService: FilterService,
              private scrollbarService: MalihuScrollbarService) { 

    this.period = Period.WEEKLY;
    this.init();
  }

  private init() {
    this.reportList = new Array<Report>();
    this.selectedReport = null ;
    this.reportIndex = null ;
    this.autoLoad = false ;
  }

  ngOnInit() {
    console.log('ngOnInit ReportListComponent');

    this.periodObservable = this.abstractReportService.getPeriod().subscribe(
      period => {
        this.period = period;
        this.filterReportList();
      }
    );

    this.filterObservable = this.filterService.getFiltersChanged().subscribe(
      () => {
        console.log('subscribe getCombinedFilterChanged')
        this.abstractReportService.loadListOfReports(false);
      }
    );

    this.queryParamObservable = this.activatedRoute.queryParams.subscribe(
      params => {
        this.reportIndex = +params['sridx'] ;
        let reportId = +params['srid']
        this.autoLoad = params['load'] ;
        this.autoSelectReport();
      });

    this.abstractReportService.loadListOfReports(false); 
    this.bindListReports();
  }

  ngAfterViewInit() {
    if (this.malihuScroll) {
      this.scrollbarService.initScrollbar('#listReports', { axis: 'y', theme: 'minimal-dark', scrollButtons: { enable: true } });
    }
  }

  ngOnDestroy() {
    this.periodObservable != null ? this.periodObservable.unsubscribe() : null ;
    this.reportsObservable != null ? this.reportsObservable.unsubscribe() : null ;
    this.filterObservable != null ? this.filterObservable.unsubscribe() : null ;
    this.queryParamObservable != null ? this.queryParamObservable.unsubscribe() : null ;
    
    this.init();
  }

  private bindListReports() {
    console.log('bindListReports');

    this.reportsObservable = this.abstractReportService.getListOfReports()
      .subscribe(reports =>{
        console.log('bindListReports subscribe');
        console.log(reports);
      
        this.filterReportList();

        this.abstractReportService.loadStatusList(this.period);

        // emit the number of report to the parent component
        this.numberOfReportChange.emit(this.reportList.length);

        // select the report with index this.reportIndex
        this.autoSelectReport();
      });
  }

  private filterReportList() {
    // Take period into consideration
    this.reportList = this.abstractReportService.getLoadedListOfReportsReports().filter(
      (report: Report) => {
        return report.getPeriod() === this.period ;
      }
    );
  }

  private autoSelectReport() {
    if (this.reportList != null && this.reportIndex != null && this.reportList[this.reportIndex] != null) {
      let index = this.reportIndex;
      this.reportIndex = null;

      if (this.autoLoad) {
        this.autoLoad = false ;
        this.highlightAndGo(this.reportList[index]);
      } else {
        this.highlight(this.reportList[index]);
      }
    }
  }

  highlight(report: Report) {
    this.selectedReport = report;
  }

  highlightAndGo(report: Report) {
    this.highlight(report);
    this.router.navigate(['./' + report.getId()], { relativeTo: this.activatedRoute }) ; 
  }

  getPeriodColumnName(): string {
    if (this.period === Period.WEEKLY) {
      return "Week";
    }

    return "Month";
  }

  getPeriodColumnValue(report: Report) {
    if (this.period === Period.WEEKLY) {
      return report.getWeekNumber() ;
    }

    return report.getMonthNumber();
  }
}
