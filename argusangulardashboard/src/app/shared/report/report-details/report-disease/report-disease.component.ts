import { ReportDiseaseValue } from './../../../model/reportDiseaseValue';
import { ReportDisease } from './../../../model/reportDisease';
import { Component, OnInit, Input } from '@angular/core';
import { forEach } from '@angular/router/src/utils/collection';

@Component({
  selector: 'app-report-disease',
  templateUrl: './report-disease.component.html',
  styleUrls: ['./report-disease.component.scss']
})
export class ReportDiseaseComponent implements OnInit {

  @Input('reportDisease') reportDisease: ReportDisease;

  constructor() { }

  ngOnInit() {
     // console.log(this.reportDisease);
  }

  isHighlighted(diseaseValue: ReportDiseaseValue) {
      return (diseaseValue.getValue() > 0) ;
  }

  diseaseSurpassThreshold() {   
    let result = false;
    
    this.reportDisease.getDiseaseValues().forEach(diseaseValue => {
      if (diseaseValue.surpassThreshold()) {
        result = true;
      }
    });
    
    return result;
  }

  valueSurpassThreshold(diseaseValue: ReportDiseaseValue) {
      return diseaseValue.surpassThreshold();
  }

}
