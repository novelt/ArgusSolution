import { DiseaseService } from './../shared/service/disease/disease.service';
import { Component, OnInit } from '@angular/core';

/**
 * Archive class component.
 *
 * @class ArchiveComponent
 * @constructor
 */
@Component({
  selector: 'app-archives',
  templateUrl: './archives.component.html',
  styleUrls: ['./archives.component.scss']
})
export class ArchivesComponent implements OnInit {

  public isActive = false ;

  constructor(private diseaseService: DiseaseService) { }

  ngOnInit() { 
    this.diseaseService.loadListOfDiseases();
  }
}
