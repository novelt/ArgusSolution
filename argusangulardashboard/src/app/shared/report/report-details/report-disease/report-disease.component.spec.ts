import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReportDiseaseComponent } from './report-disease.component';

describe('ReportDiseaseComponent', () => {
  let component: ReportDiseaseComponent;
  let fixture: ComponentFixture<ReportDiseaseComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReportDiseaseComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReportDiseaseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
