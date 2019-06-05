import { Component, OnInit, Input } from '@angular/core';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-participation-modal',
  templateUrl: './participation-modal.component.html',
  styleUrls: ['./participation-modal.component.scss']
})
export class ParticipationModalComponent implements OnInit {

  @Input('title') title: string;
  @Input('details') details: string;
  @Input('aggregations') aggregations: string;
  @Input() close: string = 'global.button.close' ;

  constructor(public activeModal: NgbActiveModal) { }

  ngOnInit() { }

}
