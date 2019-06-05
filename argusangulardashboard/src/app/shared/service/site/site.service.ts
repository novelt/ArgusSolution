import { UrlService } from './../url/url.service';
import { FilterService } from '../filter/filter.service';
import { JsonConvert } from 'json2typescript';
import { AppError } from '../../common/Error/app-error';
import { BaseService } from '../base.service';
import { AuthenticationService } from '../../../core/authentication/authentication.service';
import { Http } from '@angular/http';
import { Injectable } from '@angular/core';
import { Ng4LoadingSpinnerService } from 'ng4-loading-spinner';
import { Router } from '@angular/router';
import 'rxjs/add/operator/map';
import { BehaviorSubject } from 'rxjs';
import { Site } from '../../model/site';

const SITES_DATA_URL = 'api/sites';

@Injectable()
export class SiteService extends BaseService { 

    /** JSON converter */
    private jsonConvert: JsonConvert = new JsonConvert();

    private _sites: BehaviorSubject<Site[]>;

    private dataStore: {
        sites: Site[],
    };

    constructor(private http: Http, 
              authenticationService: AuthenticationService,
              router : Router,
              filterService: FilterService,
              private spinnerService: Ng4LoadingSpinnerService,
              private urlService: UrlService) {
        super(authenticationService, router, filterService);

        this.init();
    }

    private init() {
        this.dataStore = { sites: [] };
        this._sites = <BehaviorSubject<Site[]>> new BehaviorSubject([]);
    }

    getListOfSites() {
        return this._sites.asObservable();
    }

    loadListOfSites() {  
        console.log('loadListOfSites');
            
        this.spinnerService.show();
        this.http.get(this.urlService.getServerUrl() + SITES_DATA_URL,
            { headers: this.getAuthorizationHeaders() })
            .map(response => response.json().sites)
            .catch((error: Response) => this.catchError(error))
            .finally(() => { 
                this.spinnerService.hide(); 
                console.log('loadListOfSites spinnerService.hide(); ') ;
            })
            .subscribe(
            sites => {
                this.dataStore.sites = this.jsonConvert.deserializeArray(sites, Site); 
                this._sites.next(Object.assign({}, this.dataStore).sites);
            },
            (error: AppError) => this.subscribeError(error));
    }

    getSelectedHomeSites() {
        let selectedSites:Site[] = new Array<Site>();
        let currentSite = this.dataStore.sites[0];

        // Add RootSite
        selectedSites[0] = currentSite;
        
        while (currentSite != null && !currentSite.isHome()) {
            let site = currentSite;
            currentSite = null ;

            site.getChildren().forEach(element => {
                if (element.isHomePath()) {
                    selectedSites[element.getLevel()] = element;
                    currentSite = element;
                }
            });
        }

        return selectedSites;
    }

    emptyData() {
        this.init();
    }
}
