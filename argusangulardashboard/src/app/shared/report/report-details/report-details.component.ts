import { AppError } from './../../common/Error/app-error';
import { ConfirmComponent } from './../../confirm/confirm.component';
import { ParticipationModalComponent } from './../../participation-modal/participation-modal.component';
import { ReportVersion } from './../../model/reportVersion';
import { ISubscription } from 'rxjs/Subscription';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { TranslateService } from '@ngx-translate/core';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { Component, OnInit } from '@angular/core';
import { Report } from '../../model/report';
import { AbstractReportService } from '../../service/report/abstract-report.service';

@Component({
  selector: 'app-report-details',
  templateUrl: './report-details.component.html',
  styleUrls: ['./report-details.component.scss']
})
export class ReportDetailsComponent implements OnInit {
  report: Report;
  reportVersions: Array<ReportVersion>;
  currentVersion: ReportVersion = null;
  currentVersionId: number;
 
  private sub : ISubscription = null ;
  private subParams : ISubscription = null ;

  /** Translation strings */
  private validationTitle: string;
  private validationQuestion: string;
  private rejectionTitle: string;
  private rejectionQuestion: string;

  private hfProportion: string;
  private hfProportionDetails: string;

  constructor(private reportService: AbstractReportService, 
              private activatedRoute: ActivatedRoute,
              private router: Router,
              private translateService:TranslateService,
              private modalService: NgbModal) { 

      this.reportVersions = new Array<ReportVersion>();
      this.currentVersionId = 0;
  }

  ngOnInit() {
    console.log("ReportDetailsComponent : ");

    this.subParams = this.activatedRoute.params.subscribe(params => {
      let reportId = + params['reportId'];
      this.reportVersions = new Array<ReportVersion>();

      this.report = this.reportService.getReport(reportId);
    
      let reportVersionsObservable = this.reportService.getReportVersions(reportId);
      if (reportVersionsObservable != null) {
        this.sub = reportVersionsObservable.subscribe(reportVersions => {
          console.log('getReportVersions subscribe');
          this.reportVersions = reportVersions;
          this.currentVersion = this.reportVersions[0];
          this.currentVersionId = this.currentVersion ? this.currentVersion.getId() : 0;
       });
      }

      this.reportService.loadReportVersions(reportId);
    });

    this.router.events
      .filter(event => event instanceof NavigationEnd)
      .subscribe((event: NavigationEnd) => {
          window.scroll(0, 0);
      });

      this.initTranslation();
  }

  private initTranslation(): void {
    this.translateService.get('details.confirm.validation.title').subscribe((res: string) => {this.validationTitle = res;});
    this.translateService.get('details.confirm.validation.question').subscribe((res: string) => {this.validationQuestion = res;});
    this.translateService.get('details.confirm.rejection.title').subscribe((res: string) => {this.rejectionTitle = res;});
    this.translateService.get('details.confirm.rejection.question').subscribe((res: string) => {this.rejectionQuestion = res;});
    this.translateService.get('details.aggregation.hf_proportion').subscribe((res: string) => {this.hfProportion = res;});
    this.translateService.get('details.aggregation.hf_details').subscribe((res: string) => {this.hfProportionDetails = res;});
  }

  ngOnDestroy() {
    this.sub != null ? this.sub.unsubscribe() : null ;
    this.subParams != null ? this.subParams.unsubscribe() : null ;
  }

  getNumberOfReportVersions() {
    return this.reportVersions? this.reportVersions.length : '-';
  }

  onVersionChange(versionId : number) {
    this.currentVersion = this.reportVersions.find(version => version.getId() == versionId);
    this.currentVersionId = versionId;
  }

  openDetailsParticipation($event:Event) {
    console.log('openDetailsParticipation');

    const modalRef = this.modalService.open(ParticipationModalComponent);
    modalRef.componentInstance.title = this.hfProportionDetails;
    modalRef.componentInstance.details = this.hfProportion + ' : ' 
                                    + this.currentVersion.getPercentage() +' % (' 
                                    + this.currentVersion.getParticipatingHF() + '/'
                                    + this.currentVersion.getTotalHF() + ')' ;
    modalRef.componentInstance.aggregations = this.currentVersion.getAggregations();
  }

  canValidate() {
    return this.report && this.currentVersion ? 
            this.report.canBeValidated() && this.currentVersion.canBeValidated() 
          : false ; 
  }

  canReject() {
    return this.report && this.currentVersion ?  
        this.report.canBeRejected() && this.currentVersion.canBeRejected() 
        : false ; 
  }

  goToReportIndex(newIndex: number) {
    let index = this.reportService.getLoadedListOfReportsReports().findIndex(report => this.report.getId() == report.getId());

    index += newIndex;
    if (index < 0) {
      index = 0;
    } else if (index >= this.reportService.getLoadedListOfReportsReports().length) {
      index = this.reportService.getLoadedListOfReportsReports().length -1;
    }

    let newReportId = this.reportService.getLoadedListOfReportsReports()[index].getId();
    this.router.navigate(['../' + newReportId], { relativeTo: this.activatedRoute, queryParams: { sridx: index } });
  }

  goToList() {
    this.redirectToList({});    
  }

  validateCurrentVersion($event:Event) {
    console.log('validateCurrentVersion');

    const modalRef = this.modalService.open(ConfirmComponent);
    modalRef.componentInstance.title = this.validationTitle;
    modalRef.componentInstance.body = this.validationQuestion;
    modalRef.result.then((result) => {
      console.log(result);
      if (result === true) {
        this.reportService.validateReportVersion(this.report.getId(), this.currentVersion.getId())
        .subscribe(result => {
          console.log(result);
          this.reportService.manageAction(this.report, this.activatedRoute);
         },
        (error: AppError) => this.reportService.subscribeError(error));
      } 
    }, (reason) => {
      console.log(reason);
    });
  }

  rejectCurrentVersion($event:Event) {
    console.log('rejectCurrentVersion');
    
    const modalRef = this.modalService.open(ConfirmComponent);
    modalRef.componentInstance.title = this.rejectionTitle;
    modalRef.componentInstance.body = this.rejectionQuestion;
    modalRef.result.then((result) => {
      console.log(result);
      if (result === true) {
        this.reportService.rejectReportVersion(this.report.getId(), this.currentVersion.getId())
        .subscribe(result => {
          console.log(result);
          this.reportService.manageAction(this.report, this.activatedRoute);
        },
        (error: AppError) => this.reportService.subscribeError(error));
      }
    }, (reason) => {
      console.log(reason);
    });
  }

  private redirectToList(params: { [key: string]: any;}) {
    this.report = null;
    this.reportVersions = new Array<ReportVersion>();
    this.currentVersion = null ;
    this.router.navigate(['../'], { relativeTo: this.activatedRoute, queryParams: params });
  }
}
