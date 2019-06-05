import { DiseaseService } from './disease.service';
import { TestBed, inject } from '@angular/core/testing';

describe('DiseaseService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [DiseaseService]
    });
  });

  it('should be created', inject([DiseaseService], (service: DiseaseService) => {
    expect(service).toBeTruthy();
  }));
});
