import { FilterService } from './../../service/filter/filter.service';
import { forEach } from '@angular/router/src/utils/collection';
import { element } from 'protractor';
import { SiteChangedEventArgs } from './../dropdown/site-dropdown.component';
import { Site } from './../../model/site';
import { SiteService } from './../../service/site/site.service';
import { Component, OnInit, Output, EventEmitter } from '@angular/core';

export interface SelectedSitesChangedEventArgs {
  selectedSites:Site[]
}

@Component({
  selector: 'app-site-cascade',
  templateUrl: './site-cascade.component.html',
  styleUrls: ['./site-cascade.component.scss']
})
export class SiteCascadeComponent implements OnInit {

  rootSite:Site = null ;
  selectedSites:Site[] = new Array<Site>();

  @Output('selectedSitesChange') selectedSitesChange: EventEmitter<SelectedSitesChangedEventArgs> = new EventEmitter();

  constructor(private siteService: SiteService,
            private filterService: FilterService) {

    if (this.filterService.getSelectedSites().length > 0) {
      this.selectedSites = this.filterService.getSelectedSites();
    }
  }

  ngOnInit() { 
    console.log('ngOnInit SiteCascadeComponent');

    this.siteService.getListOfSites().subscribe(
        sites => {
            if (sites.length > 0) {
              this.rootSite = sites[0];
              this.loadHomeSite();
            }
        }
    );

    this.siteService.loadListOfSites();
  }

  hasChildren(site:Site) {
    if (site != null && site.getChildren() != null) {
      return site.getChildren().length > 0;
    }

    return false ;
  }

  isOneChildrenAccessible(site : Site) {
    console.log('isOneChildrenAccessible');
    console.log(site);
    let isAccessible = false ;
    
    if (site != null && site.getChildren() != null) {
      site.getChildren().forEach(element => {
        if (element.isAccessible()) {
          isAccessible = true ;
          return isAccessible;
        }
      });
    }

    return isAccessible ;
  }

  loadHomeSite() {
    if (this.selectedSites.length == 0) {
      this.selectedSites = this.siteService.getSelectedHomeSites();
    }
    this.emitSelectedSites();
  }

  getChildrenLevel(site:Site) {
    if (site != null) {
      return site.getLevel() +1;
    }
  }

  getSelectedSiteAtLevel(level:number) {
    let site = this.selectedSites[level];
    if (site != null) {
      return site.getId();
    }

    return -1;
  }

  changeSiteSelection(eventArgs: SiteChangedEventArgs) {
    let level = + eventArgs.level;
    let siteId = + eventArgs.siteId;

    this.selectedSites = this.selectedSites.slice(0, level);

    if (siteId > -1) {
      // get Children of level parent       
      let parentLevel = level - 1;
      let siteParent = this.selectedSites[parentLevel];

      siteParent.getChildren().forEach(element => {
        if (element.getId() === siteId) {
          // Selected Site
          this.selectedSites[level] = element;
        }
      });
    }
  }

  getSelectedSite() {
    return this.selectedSites[this.selectedSites.length -1];
  }

  public emitSelectedSites() {
    this.selectedSitesChange.emit(
      { 
        selectedSites : this.selectedSites
      }
    );
  }
}
