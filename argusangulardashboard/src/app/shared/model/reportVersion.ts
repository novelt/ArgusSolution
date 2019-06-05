import { Aggregation } from './aggregation';
import { ReportDisease } from './reportDisease';
import { JsonObject, JsonProperty } from "json2typescript";
import * as moment from 'moment';

@JsonObject
export class ReportVersion {
    
    @JsonProperty("id", Number)
    private id: number = undefined;

    @JsonProperty("cna", String, true)
    private contactName: string = "";

    @JsonProperty("cpn", String, true)
    private contactPhoneNumber: string = "";

    @JsonProperty("sts", String)
    private status: string = undefined;

    @JsonProperty("rid", Number)
    private reportId: number = undefined;

    @JsonProperty("agg", Boolean, true)
    private aggregate: Boolean = false;

    @JsonProperty("dis", [ReportDisease], true)
    private diseases: ReportDisease[] = undefined;

    @JsonProperty("crd", String, true)
    private createdDate: string = undefined;

    @JsonProperty("val", Boolean)
    private validate: boolean = false;

    @JsonProperty("rej", Boolean)
    private reject: boolean = false;

    @JsonProperty("thcp", Number)
    private totalNbParticatingHF: number = undefined;

    @JsonProperty("thct", Number)
    private totalNbHF: number = undefined;

    @JsonProperty("aggs", [Aggregation], true)
    private aggregations: Aggregation[] = undefined;

    getId() {
        return this.id;
    }

    getVersionDate() {
       /* if (this.createdDate) {
            return new Date(this.createdDate);
        }

        return new Date();*/
        return moment(this.createdDate);
    }

    getStatus() {
        return this.status;
    }

    getContactName() {
        return this.contactName;
    }

    getContactPhoneNumber() {
        return this.contactPhoneNumber;
    }

    getDiseases() {
        return this.diseases;
    }

    isAggregate() {
        return this.aggregate;
    }

    getAggregations() {
        return this.aggregations;
    }

    getParticipatingHF() {
        return this.totalNbParticatingHF;
    }

    getTotalHF() {
        return this.totalNbHF;
    }

    getPercentage() {
        return Math.round(this.totalNbParticatingHF / this.totalNbHF * 100) ;
    }

    canBeValidated() {
        return this.validate;
    }

    canBeRejected() {
        return this.reject;
    }
 }