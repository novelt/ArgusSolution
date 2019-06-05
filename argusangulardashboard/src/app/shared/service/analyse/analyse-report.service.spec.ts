import { AnalyseReportService } from './analyse-report.service';
import { TestBed, inject } from '@angular/core/testing';

describe('AnalyseReportService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [AnalyseReportService]
    });
  });

  it('should be created', inject([AnalyseReportService], (service: AnalyseReportService) => {
    expect(service).toBeTruthy();
  }));
});
