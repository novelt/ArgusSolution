import { Component, OnInit, Input } from '@angular/core';
import { NgbActiveModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-alert',
  templateUrl: './alert.component.html',
  styleUrls: ['./alert.component.scss']
})
export class AlertComponent implements OnInit {

  @Input() body: string;
  @Input() title: string;
  @Input() close: string = 'global.button.close' ;

  constructor(public activeModal: NgbActiveModal) { }

  ngOnInit() { }

}
