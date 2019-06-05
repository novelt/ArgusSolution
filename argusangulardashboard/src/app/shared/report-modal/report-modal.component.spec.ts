import { ReportModalComponent } from './report-modal.component';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

describe('ReportModalComponent', () => {
  let component: ReportModalComponent;
  let fixture: ComponentFixture<ReportModalComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [ReportModalComponent]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReportModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
});
