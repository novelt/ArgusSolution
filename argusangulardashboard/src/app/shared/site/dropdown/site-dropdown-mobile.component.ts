import { Component } from '@angular/core';
import { SiteDropDownComponent } from './site-dropdown.component';

@Component({
  selector: 'app-site-dropdown-mobile',
  templateUrl: './site-dropdown.component.html', // Use Site-dropdown component.html
  styleUrls: ['./site-dropdown-mobile.component.scss'] // Just redefine css
})
export class SiteDropDownMobileComponent extends SiteDropDownComponent {

  constructor() { 
    super();
  }
}
