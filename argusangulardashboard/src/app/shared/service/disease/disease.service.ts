import { UrlService } from './../url/url.service';
import { FilterService } from './../filter/filter.service';
import { Disease } from './../../model/disease';
import { JsonConvert } from 'json2typescript';
import { AppError } from './../../common/Error/app-error';
import { BaseService } from './../base.service';
import { AuthenticationService } from './../../../core/authentication/authentication.service';
import { Http } from '@angular/http';
import { Injectable } from '@angular/core';
import { Ng4LoadingSpinnerService } from 'ng4-loading-spinner';
import { Router } from '@angular/router';
import 'rxjs/add/operator/map';
import { BehaviorSubject } from 'rxjs';

 const DISEASES_DATA_URL = 'api/diseases';

@Injectable()
export class DiseaseService extends BaseService { 

    /** JSON converter */
    private jsonConvert: JsonConvert = new JsonConvert();

    private _diseases: BehaviorSubject<Disease[]>;

    private dataStore: {
        diseases: Disease[],
    };

    constructor(private http: Http, 
                authenticationService: AuthenticationService,
                router : Router,
                filterService: FilterService,
                private spinnerService: Ng4LoadingSpinnerService,
                private urlService: UrlService) {
        super(authenticationService, router, filterService);

        this.dataStore = { diseases: [] };
        this._diseases = <BehaviorSubject<Disease[]>> new BehaviorSubject([]);
    }

    getListOfDiseases() {
        return this._diseases.asObservable();
    }

    loadListOfDiseases() {  
        console.log('loadListOfDiseases');
        this.spinnerService.show();
        this.http.get(this.urlService.getServerUrl() + this.getLanguage() + '/' + DISEASES_DATA_URL,
            { headers: this.getAuthorizationHeaders() })
            .map(response => response.json().diseases)
            .catch((error: Response) => this.catchError(error))
            .finally(() => this.spinnerService.hide())
            .subscribe(
            diseases => {
                this.dataStore.diseases = this.jsonConvert.deserializeArray(diseases, Disease); 
                this._diseases.next(Object.assign({}, this.dataStore).diseases);
            },
            (error: AppError) => this.subscribeError(error));
    }
}
