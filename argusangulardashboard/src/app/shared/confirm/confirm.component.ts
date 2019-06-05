import { Component, OnInit, Input } from '@angular/core';
import { NgbActiveModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-confirm',
  templateUrl: './confirm.component.html',
  styleUrls: ['./confirm.component.scss']
})
export class ConfirmComponent implements OnInit {

  @Input() body: string;
  @Input() title: string;
  @Input() no: string = 'global.button.no' ;
  @Input() yes: string = 'global.button.yes' ;

  constructor(public activeModal: NgbActiveModal) { }

  ngOnInit() { }

}
