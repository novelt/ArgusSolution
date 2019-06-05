import { SiteCascadeComponent } from './site-cascade.component';
import { FilterService } from '../../service/filter/filter.service';
import { SiteService } from '../../service/site/site.service';
import { Component } from '@angular/core';

@Component({
  selector: 'app-site-cascade-mobile',
  templateUrl: './site-cascade-mobile.component.html',
  styleUrls: ['./site-cascade-mobile.component.scss']
})
export class SiteCascadeMobileComponent extends SiteCascadeComponent {

  constructor(siteService: SiteService,
              filterService: FilterService) {
          super(siteService, filterService);
  }
}
