import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidationMobileComponent } from './validation-mobile.component';

describe('ValidationMobileComponent', () => {
  let component: ValidationMobileComponent;
  let fixture: ComponentFixture<ValidationMobileComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ValidationMobileComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ValidationMobileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
