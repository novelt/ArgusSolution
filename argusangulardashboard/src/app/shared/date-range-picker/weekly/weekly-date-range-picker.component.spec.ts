import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { WeeklyDateRangePicker } from './weekly-date-range-picker.component';

describe('WeeklyDateRangePicker', () => {
  let component: WeeklyDateRangePicker;
  let fixture: ComponentFixture<WeeklyDateRangePicker>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [WeeklyDateRangePicker]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WeeklyDateRangePicker);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

});
