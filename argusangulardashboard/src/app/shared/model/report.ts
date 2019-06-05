import { Observable } from 'rxjs/Rx';
import { BehaviorSubject } from 'rxjs';
import { ReportVersion } from './reportVersion';
import { JsonObject, JsonProperty } from "json2typescript";
import * as moment from 'moment';

@JsonObject
export class Report {

    @JsonProperty("id", Number)
    private id: number = undefined;

    @JsonProperty("per", String)
    private period: string = undefined;

    @JsonProperty("sid", Number)
    private siteId: number = undefined;

    @JsonProperty("sna", String)
    private siteName: string = undefined;

    @JsonProperty("srid", Number, true)
    private siteRelationShipId: number = undefined;

    @JsonProperty("sdat", String)
    private startDate: string = undefined;

    @JsonProperty("edat", String)
    private endDate: string = undefined;

    @JsonProperty("wnb", Number, true)
    private weekNumber: number = undefined;

    @JsonProperty("mnb", Number, true)
    private monthNumber: number = undefined;

    @JsonProperty("yea", Number)
    private year: number = undefined;

    @JsonProperty("sts", String)
    private status: string = undefined;

    @JsonProperty("val", Boolean)
    private validate: boolean = false;

    @JsonProperty("rej", Boolean)
    private reject: boolean = false;

    @JsonProperty("agg", Boolean, true)
    private aggregate: Boolean = false;

    private _reportVersions = <BehaviorSubject<ReportVersion[]>> new BehaviorSubject([]);
    private versions : ReportVersion[] = undefined;
   
    getId() {
        return this.id;
    }

    getSiteName() {
        return this.siteName;
    }

    getStatus() {
        return this.status;
    }

    getWeekNumber() {
        return this.weekNumber;
    }

    getMonthNumber() {
        return this.monthNumber;
    }

    getPeriod() {
        return this.period;
    }

    getYear() {
        return this.year;
    }

    getStartDate() {
        //return new Date(this.startDate);
        return moment(this.startDate);
    }

    getEndDate() {
        // return new Date(this.endDate);
        return moment(this.endDate);
    }

    isAggregate() {
        return this.aggregate;
    }

    canBeValidated() {
        return this.validate;
    }

    canBeRejected() {
        return this.reject;
    }

    /**
     * Set Report Versions to the report
     * 
     * @param versions 
     */
    setReportVersions(versions : ReportVersion[]) {
        this.versions = versions;
        this._reportVersions.next(Object.assign({}, this).versions);
    }

    getReportVersions() : Observable<ReportVersion[]> {
        return this._reportVersions.asObservable();
    }

    getVersions() {
        return this.versions;
    }
}
