import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { FilterMobileComponent } from './filter-mobile.component';

describe('FilterMobileComponent', () => {
  let component: FilterMobileComponent;
  let fixture: ComponentFixture<FilterMobileComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [FilterMobileComponent]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FilterMobileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
});
