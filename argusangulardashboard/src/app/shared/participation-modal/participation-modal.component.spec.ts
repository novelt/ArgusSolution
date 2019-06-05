import { ParticipationModalComponent } from './participation-modal.component';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

describe('ParticipationModalComponent', () => {
  let component: ParticipationModalComponent;
  let fixture: ComponentFixture<ParticipationModalComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
        declarations: [ParticipationModalComponent]
      })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ParticipationModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
});
