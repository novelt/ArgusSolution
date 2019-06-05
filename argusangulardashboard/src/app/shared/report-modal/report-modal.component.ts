import { Component, OnInit, Input, ViewEncapsulation } from '@angular/core';
import {NgbModal, NgbActiveModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-report-modal',
  templateUrl: './report-modal.component.html',
  encapsulation:  ViewEncapsulation.None,
  styleUrls: ['./report-modal.component.scss']
})
export class ReportModalComponent implements OnInit {

  @Input('prefix') prefix: string;
  @Input('name') name: string;
  @Input('site') site: string;
  @Input('startDate') startDate: string;
  @Input('endDate') endDate: string;

  @Input('body') body: string;

  @Input('analyseUrl') analyseUrl: string;

  constructor(public activeModal: NgbActiveModal) { }

  ngOnInit() { }

}
