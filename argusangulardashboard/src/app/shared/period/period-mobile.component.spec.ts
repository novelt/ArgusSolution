import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { PeriodMobileComponent } from './period-mobile.component';

describe('PeriodMobileComponent', () => {
  let component: PeriodMobileComponent;
  let fixture: ComponentFixture<PeriodMobileComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [PeriodMobileComponent]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PeriodMobileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
});
