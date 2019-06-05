import { JsonObject, JsonProperty } from "json2typescript";

@JsonObject
export class Script {

   @JsonProperty("tit", String)
    private title: string = undefined;

    @JsonProperty("dir", String)
    private directory: string = undefined;

    @JsonProperty("fil", String)
    private file: string = undefined;

    getTitle() {
        return this.title;
    }

    getDirectory() {
        return this.directory;
    }

    getFile() {
        return this.file;
    }
}
