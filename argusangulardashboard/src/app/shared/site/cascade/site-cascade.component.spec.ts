import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SiteCascadeComponent } from './site-cascade.component';

describe('SiteCascadeComponent', () => {
  let component: SiteCascadeComponent;
  let fixture: ComponentFixture<SiteCascadeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [SiteCascadeComponent]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SiteCascadeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
});
