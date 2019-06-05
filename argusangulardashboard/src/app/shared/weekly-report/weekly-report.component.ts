import { EpidemiologicReportService } from './../service/epidemiologic/epidemiologic-report.service';
import { Component, OnInit} from '@angular/core';

@Component({
  selector: 'app-weekly-report',
  templateUrl: './weekly-report.component.html',
  styleUrls: ['./weekly-report.component.scss']
})
export class WeeklyReportComponent implements OnInit {

  constructor(private epidemiologicReportService : EpidemiologicReportService) { }

  ngOnInit() { }

  downloadReport($event:Event) {
    console.log('downloadReport');

    let pathReport;
    let reportTitle;
    let reportDetails;
    let siteName;
    let period;
     
     this.epidemiologicReportService.getWeeklyReportDetails().subscribe(
        data => 
        {
          console.log(data);
          
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
}
