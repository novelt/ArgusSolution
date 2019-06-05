import { JsonObject, JsonProperty } from "json2typescript";

@JsonObject
export class Analyse {

   @JsonProperty("tit", String)
    private title: string = undefined;

    @JsonProperty("siz", Number)
    private size: Number = undefined;

    @JsonProperty("ext", String)
    private extension: string = undefined;

    @JsonProperty("dat", String)
    private date: string = undefined;

    getTitle() {
        return this.title;
    }

    getSize() {
        return this.size;
    }

    getExtension() {
        return this.extension;
    }

    getDate() {
        return this.date;
    }
}
