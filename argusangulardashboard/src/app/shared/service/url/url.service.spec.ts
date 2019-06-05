import { UrlService } from './url.service';
import { TestBed, inject } from '@angular/core/testing';

describe('UrlService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [UrlService]
    });
  });

  it('should be created', inject([UrlService], (service: UrlService) => {
    expect(service).toBeTruthy();
  }));
});
