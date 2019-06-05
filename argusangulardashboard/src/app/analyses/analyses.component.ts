import { Analyse } from './../shared/model/analyse';
import { ISubscription } from 'rxjs/Subscription';
import { ScriptService } from './../shared/service/r/script.service';
import { MalihuScrollbarService } from 'ngx-malihu-scrollbar';
import { Period } from './../shared/constant/period';
import { DiseaseValue } from './../shared/model/diseaseValue';
import { EpidemiologicReportService } from './../shared/service/epidemiologic/epidemiologic-report.service';
import { Disease } from './../shared/model/disease';
import { DiseaseService } from './../shared/service/disease/disease.service';
import { ReportModalComponent } from './../shared/report-modal/report-modal.component';
import { NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { FilterService } from './../shared/service/filter/filter.service';
import { Component, OnInit } from '@angular/core';
import { AnalyseReportService } from '../shared/service/analyse/analyse-report.service';
import { Moment } from 'moment';
import * as moment from 'moment';

/**
 * Analyses class component.
 *
 * @class ArchiveComponent
 * @constructor
 */
@Component({
  selector: 'app-analyses',
  templateUrl: './analyses.component.html',
  styleUrls: ['./analyses.component.scss']
})
export class AnalysesComponent implements OnInit {

  private modalRef: NgbModalRef = null ;
 
  public diseases = new Array();
  
  public years = new Array();
  public selectedYear: number;
  public weeks = new Array();
  public selectedWeek: number;

  private analyseObservable: ISubscription;
  public analyseList: Analyse[];
  public selectedAnalyse: Analyse;

  constructor(private filterService: FilterService,
              private analyseReportService: AnalyseReportService,
              private epidemiologicReportService: EpidemiologicReportService,
              private modalService: NgbModal,
              private diseaseService: DiseaseService,
              private scrollBarService: MalihuScrollbarService,
              private scriptService: ScriptService) { }

  ngOnInit() {
    this.bindListDiseases(null);
    this.diseaseService.loadListOfDiseases();

    this.bindAnalyses();
    this.scriptService.loadAnalyses();

    // bind diseases on Period Filter
    this.filterService.getPeriod().subscribe(
      period => { this.bindListDiseases(null) }
    );

    this.selectedYear = moment().subtract(7,'d').year();
    this.selectedWeek = moment().subtract(7,'d').week();
    this.initListYears();
    this.initListWeeks();
  }

  private bindAnalyses() {
    this.analyseObservable = this.scriptService.getAnalyses().subscribe(analyses => {
        this.analyseList = analyses;
        this.selectedAnalyse = this.analyseList[0];
    });
  }

  downloadAnalyse() {
    this.scriptService.downloadAnalyse(this.selectedAnalyse).subscribe(
      res => this.epidemiologicReportService.downloadFile(res)
    );
  }

  ngAfterViewInit() {
    this.scrollBarService.initScrollbar('#listDiseases', { axis: 'y', theme: 'rounded-dots-dark', scrollButtons: { enable: true } });
  }

  private bindListDiseases(searchValue: string) {
    this.diseaseService.getListOfDiseases().subscribe(
      diseases => {
          let diseasesTmp;
          if (searchValue == null || searchValue == '') {
            diseasesTmp = diseases;
          } else {
            diseasesTmp = diseases.filter((disease: Disease) =>{
              return disease.getName().toLowerCase().startsWith(searchValue.toLocaleLowerCase()) ;
            })
          }

          this.diseases = diseasesTmp.filter((disease: Disease) => {
            let result = false ;
            disease.getDiseaseValues().forEach((value: DiseaseValue) => {
                if (value.getPeriod() == this.filterService.getSelectedPeriod()) {
                  result = true;
                }
            });
            return result ;
          })
      }
    );
  }

  private refreshAnalyseUrl(selectedReportId: number) {
    let report = this.analyseReportService.getAnalyseReportsAvailable().find(report => report.id === selectedReportId);

    if (report != null) {
      this.modalRef = this.modalService.open(ReportModalComponent, { size : 'lg' });

      this.modalRef.componentInstance.analyseUrl = this.analyseReportService.getAnalyseUrl(report);
      this.modalRef.componentInstance.prefix = report.prefix ;
      this.modalRef.componentInstance.name =  this.filterService.getSelectedPeriod() == Period.WEEKLY ? report.name.weekly : report.name.monthly;
      this.modalRef.componentInstance.site = this.filterService.getSelectedSite().getName() ;
      this.modalRef.componentInstance.startDate = this.filterService.getSelectedDateRangeStart().format('L') ;
      this.modalRef.componentInstance.endDate = this.filterService.getSelectedDateRangeEnd().format('L') ;
    } 
  }


  private initListYears() {
    let startYear: number = 2015;
    let currentYear: number = moment().year();

    while (startYear <= currentYear) {
      this.years.push(startYear);
      startYear++;
    }
  }

  private initListWeeks() {
    let startWeek = 1;
    let year: Moment = moment().set('year', this.selectedYear) ;
    let numberOfWeekInYear = year.weeksInYear();

    if (this.selectedYear == moment().year()) {
      numberOfWeekInYear = (moment().week() - 1) > 0 ? (moment().week() - 1) : 1  ;

      if (this.selectedWeek > numberOfWeekInYear) {
        this.selectedWeek = numberOfWeekInYear;
      }
    }

    this.weeks = new Array();

    while(startWeek <= numberOfWeekInYear) {
      this.weeks.push(startWeek);
      startWeek++;
    }
  }

  changeYear() {
    this.initListWeeks();
  }

  viewReport(reportId: number) {
    this.refreshAnalyseUrl(reportId);
  }

  viewDiseaseReport() {
    this.refreshAnalyseUrl(5);
  }

  downloadReport($event:Event) {
    let pathReport;
    let reportTitle;
    let reportDetails;
    let siteName;
    let period;
     
     this.epidemiologicReportService.getWeeklyReportDetails(this.filterService.getSelectedSite().getId(), this.selectedYear, this.selectedWeek)
        .subscribe(
        data => 
        {          
          pathReport = data.pathReport;
          reportTitle = data.reportTitle;
          reportDetails = data.reportDetails;
          siteName = data.siteName;
          period = data.period;

          this.epidemiologicReportService.downloadWeeklyReport(pathReport, reportDetails).subscribe(
            res => this.epidemiologicReportService.downloadFile(res)
          );
        }
     );
     
  }

  selectDisease(checked: boolean, id: number) {
    this.analyseReportService.setSelectedDisease(id, checked);
  }

  filterDiseases(searchValue: string) {
    this.bindListDiseases(searchValue);
  }

  isAtLeastOneDiseaseSelected() {
    return this.analyseReportService.getNumberOfSelectedDiseases() > 0;
  }

  isDiseaseSelected(id: number) {
    return this.analyseReportService.isDiseaseSelected(id);
  }

  isAnalyseSelected() {
    return this.selectedAnalyse != null;
  }

  ngOnDestroy() {
    if (this.modalRef != null) {
      this.modalRef.close();
    }
  }
}
