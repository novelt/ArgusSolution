import { Observable } from 'rxjs/Rx';
import { BehaviorSubject } from 'rxjs';
import { ReportVersion } from './reportVersion';
import { JsonObject, JsonProperty } from "json2typescript";

@JsonObject
export class Site {

    @JsonProperty("id", Number)
    private id: number = undefined;

    @JsonProperty("nam", String)
    private name: string = undefined;

    @JsonProperty("lvl", Number)
    private level: number = undefined;

    @JsonProperty("hom", Boolean)
    private home: boolean = false;

    @JsonProperty("hpa", Boolean)
    private homePath: boolean = false;

    @JsonProperty("acc", Boolean)
    private accessible: boolean = false;

    @JsonProperty("exp", Boolean, true)
    private export: boolean = false;

    @JsonProperty("chi", [Site], true)
    private children: Site[] = null;
   
    getId() {
        return this.id;
    }

    getName() {
        return this.name;
    }

    getLevel() {
        return this.level;
    }

    isHome() {
        return this.home;
    }

    isHomePath() {
        return this.homePath;
    }

    isAccessible() {
        return this.accessible;
    }

    isExportable() {
        return this.export;
    }

    getChildren() {
        return this.children;
    }
}
