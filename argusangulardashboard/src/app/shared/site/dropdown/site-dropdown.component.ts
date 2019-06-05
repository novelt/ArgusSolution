import { Site } from './../../model/site';
import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';

export interface SiteChangedEventArgs {
  siteId: number,
  level: number,
}

@Component({
  selector: 'app-site-dropdown',
  templateUrl: './site-dropdown.component.html',
  styleUrls: ['./site-dropdown.component.scss']
})
export class SiteDropDownComponent implements OnInit {

  @Input('currentSiteId') currentSiteId:number = null ;
  @Input('sites') sites:Site[];
  @Input('level') level:number;
  @Input('select') select:boolean;

  @Output('selectedSiteChange') selectedSiteChange: EventEmitter<SiteChangedEventArgs> = new EventEmitter();

  constructor() { }

  ngOnInit() {
    // Init first child if currentSiteId == -1 but no "select" option is present
    // that means that current level is selectable but not the parent site.
    if (this.currentSiteId === -1 && !this.select) { 
      this.currentSiteId = this.sites[0].getId();
    }
  }

  onSiteChange(siteId: number) {
    this.selectedSiteChange.emit(
      { 
        siteId : siteId, 
        level: this.level,
      }
    );
  }

  existSites() {
    return this.sites != null && this.sites.length > 0;
  }

  isOneSiteAccessible() {
    let accessible = false ;

    if (this.sites != null) {
      this.sites.forEach(element => {
        if (element.isAccessible()) {
            accessible = true ;
        }
      });
    }

    return accessible ;
  }

  siteOrChildrenAreAccessible(site : Site){
    let isAccessible = false ;
    
    if (site.isAccessible()) {
      isAccessible = true;
      return isAccessible;
    }
    else {
      if (site.getChildren() != null) {
        site.getChildren().forEach(element => {
          if (this.siteOrChildrenAreAccessible(element)) {
            isAccessible = true ;
            return isAccessible;
          }
        });
      }
    }

    return isAccessible;
  }
}
