import { DiseaseValue } from './diseaseValue';
import { Observable } from 'rxjs/Rx';
import { BehaviorSubject } from 'rxjs';
import { ReportVersion } from './reportVersion';
import { JsonObject, JsonProperty } from "json2typescript";

@JsonObject
export class Disease {

    @JsonProperty("id", Number)
    private id: number = undefined;

    @JsonProperty("ref", String)
    private reference: string = undefined;

    @JsonProperty("nam", String)
    private name: string = undefined;

    @JsonProperty("dval", [DiseaseValue], true)
    private values: DiseaseValue[] = null;

    getId() {
        return this.id;
    }

    getName() {
        return this.name;
    }

    getReference() {
        return this.reference;
    }

    getDiseaseValues() {
        return this.values;
    }
}
