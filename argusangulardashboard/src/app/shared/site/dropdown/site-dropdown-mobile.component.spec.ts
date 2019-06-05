import { SiteDropDownMobileComponent } from './site-dropdown-mobile.component';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

describe('SiteDropDownMobileComponent', () => {
  let component: SiteDropDownMobileComponent;
  let fixture: ComponentFixture<SiteDropDownMobileComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [SiteDropDownMobileComponent]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SiteDropDownMobileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
});
