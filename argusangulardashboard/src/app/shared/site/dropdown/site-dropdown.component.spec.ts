import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SiteDropDownComponent } from './site-dropdown.component';

describe('SiteDropDownComponent', () => {
  let component: SiteDropDownComponent;
  let fixture: ComponentFixture<SiteDropDownComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [SiteDropDownComponent]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SiteDropDownComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
});
