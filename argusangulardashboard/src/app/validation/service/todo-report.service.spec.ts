import { TodoReportService } from './todo-report.service';
import { TestBed, inject } from '@angular/core/testing';

describe('TodoReportService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [TodoReportService]
    });
  });

  it('should be created', inject([TodoReportService], (service: TodoReportService) => {
    expect(service).toBeTruthy();
  }));
});
