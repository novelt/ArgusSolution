import { ArchivesMobileComponent } from './archives-mobile.component';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';


describe('ArchivesMobileComponent', () => {
  let component: ArchivesMobileComponent;
  let fixture: ComponentFixture<ArchivesMobileComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [ArchivesMobileComponent]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ArchivesMobileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
