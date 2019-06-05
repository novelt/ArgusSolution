import { DiseaseService } from '../shared/service/disease/disease.service';
import { Component, OnInit } from '@angular/core';
import { ArchivesComponent } from './archives.component';


/**
 * Archive Mobile class component.
 *
 * @class ArchiveMobileComponent
 * @constructor
 */
@Component({
  selector: 'app-archives-mobile',
  templateUrl: './archives-mobile.component.html',
  styleUrls: ['./archives-mobile.component.scss']
})
export class ArchivesMobileComponent extends ArchivesComponent implements OnInit {

  constructor(diseaseService: DiseaseService) { 
    super(diseaseService);
  }

  ngOnInit() { 
  }

  menuOpen($event: boolean) {
    this.isActive = $event;
  }
}
