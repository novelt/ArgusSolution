import { Observable } from 'rxjs/Rx';
import { BehaviorSubject } from 'rxjs';
import { ReportVersion } from './reportVersion';
import { JsonObject, JsonProperty } from "json2typescript";

@JsonObject
export class DiseaseValue {

    @JsonProperty("id", Number)
    private id: number = undefined;

    @JsonProperty("val", String)
    private value: string = undefined;

    @JsonProperty("nam", String)
    private name: string = undefined;

    @JsonProperty("per", String)
    private period: string = undefined;

    getId() {
        return this.id;
    }

    getName() {
        return this.name;
    }

    getValue() {
        return this.value;
    }

    getPeriod() {
        return this.period;
    }
}
