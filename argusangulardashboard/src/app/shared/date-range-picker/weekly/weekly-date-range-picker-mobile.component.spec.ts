import { WeeklyDateRangePickerMobile } from './weekly-date-range-picker-mobile.component';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

describe('WeeklyDateRangePickerMobile', () => {
  let component: WeeklyDateRangePickerMobile;
  let fixture: ComponentFixture<WeeklyDateRangePickerMobile>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [WeeklyDateRangePickerMobile]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WeeklyDateRangePickerMobile);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

});
