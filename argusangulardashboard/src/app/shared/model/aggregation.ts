import { JsonObject, JsonProperty } from "json2typescript";

@JsonObject
export class Aggregation {

    @JsonProperty("sna", String)
    private siteName: string = "";

    @JsonProperty("hcp", Number)
    private nbParticatingHF: number = undefined;

    @JsonProperty("hct", Number)
    private nbTotalHF: number = undefined;

    getSiteName() {
        return this.siteName;
    }

    getNbParticipatingHF() {
        return this.nbParticatingHF;
    }

    getNbTotalHF() {
        return this.nbTotalHF;
    }

    getPercentage() {
        return Math.round(this.nbParticatingHF / this.nbTotalHF * 100) ;
    }
}