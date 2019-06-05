import { EpidemiologicReportService } from './epidemiologic-report.service';
import { TestBed, inject } from '@angular/core/testing';

describe('EpidemiologicReportService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [EpidemiologicReportService]
    });
  });

  it('should be created', inject([EpidemiologicReportService], (service: EpidemiologicReportService) => {
    expect(service).toBeTruthy();
  }));
});
