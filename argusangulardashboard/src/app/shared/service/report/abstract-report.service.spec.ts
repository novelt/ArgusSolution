import { TestBed, inject } from '@angular/core/testing';
import { AbstractReportService } from './abstract-report.service';

describe('AbstractReportService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [AbstractReportService]
    });
  });

  it('should be created', inject([AbstractReportService], (service: AbstractReportService) => {
    expect(service).toBeTruthy();
  }));
});
