import { JsonObject, JsonProperty } from "json2typescript";
import { ReportDiseaseValue } from "./reportDiseaseValue";

@JsonObject
export class ReportDisease {
    
    @JsonProperty("id", Number)
    private id: number = undefined;

    @JsonProperty("ref", String)
    private reference: string = undefined;

    @JsonProperty("nam", String)
    private name: string = undefined;

    @JsonProperty("dval", [ReportDiseaseValue])
    private diseaseValues: ReportDiseaseValue[] = undefined;

    getName() {
        return this.name;
    }

    getDiseaseValues() {
        return this.diseaseValues;
    }
 }