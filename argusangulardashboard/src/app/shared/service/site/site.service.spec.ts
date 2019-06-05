import { SiteService } from './site.service';
import { TestBed, inject } from '@angular/core/testing';

describe('SiteService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [SiteService]
    });
  });

  it('should be created', inject([SiteService], (service: SiteService) => {
    expect(service).toBeTruthy();
  }));
});
