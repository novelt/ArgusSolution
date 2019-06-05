import { Observable } from 'rxjs';
import { DateRange } from '../../filter/filter.component';
import { Subject } from 'rxjs/Subject';
import { Site } from '../../model/site';
import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';


@Injectable()
export class FilterService  { 

    private _sites: BehaviorSubject<Site[]> ;
    private _dateRange: Subject<DateRange> ;
    private _period: Subject<string> ;

    private _filterChanged : Subject<boolean>;

    private dataStore: {
        sites: Site[],
        dateRange: DateRange,
        period: string,
    };

    constructor() {
        this.init();
    }

    private init() {
        this.dataStore = { sites: [], dateRange: { dateStart: null, dateEnd: null }, period: null };
        this._sites = new BehaviorSubject([]);
        this._dateRange = new Subject<DateRange>() ;
        this._period = new Subject<string>() ;

        this._filterChanged = new Subject<boolean>() ;
    }

    /** Sites  */

    getSites() {
        return this._sites.asObservable();
    }

    setSelectedSites(sites: Site[]) {
        this.dataStore.sites = sites ; 
        this._sites.next(Object.assign({}, this.dataStore).sites);
    }

    getSelectedSite() {
        return this.dataStore.sites[this.dataStore.sites.length-1];
    }

    getSelectedSites() {
        return this.dataStore.sites;
    }

    /** Date Range */

    getDateRange() {
        return this._dateRange.asObservable();
    }

    setSelectedDateRange(dateRange: DateRange) {
        this.dataStore.dateRange = dateRange ; 
        this._dateRange.next(Object.assign({}, this.dataStore).dateRange);
    }

    getSelectedDateRange() {
        return this.dataStore.dateRange;
    }

    getSelectedDateRangeStart() {
        return this.dataStore.dateRange.dateStart;
    }

    getSelectedDateRangeEnd() {
        return this.dataStore.dateRange.dateEnd;
    }

    /** Period */
    getPeriod() {
        return this._period.asObservable();
    }

    setSelectedPeriod(period: string) {
        this.dataStore.period = period ; 
        this._period.next(Object.assign({}, this.dataStore).period);
    }

    getSelectedPeriod() {
        return this.dataStore.period;
    }

    /**********************/
    getFiltersChanged() : Observable<boolean> {
        return this._filterChanged.asObservable();
    }

    setFilterChanged(value: boolean) {
        this._filterChanged.next(Object.assign({}, value));
    }


    /******** Empty Data */
    emptyData() {
        this.init();
    }
}
