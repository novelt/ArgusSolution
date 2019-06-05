import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { SiteCascadeMobileComponent } from './site-cascade-mobile.component';

describe('SiteCascadeMobileComponent', () => {
  let component: SiteCascadeMobileComponent;
  let fixture: ComponentFixture<SiteCascadeMobileComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [SiteCascadeMobileComponent]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SiteCascadeMobileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
});
