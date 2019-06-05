import { JsonObject, JsonProperty } from "json2typescript";

@JsonObject
export class ReportDiseaseValue {
    
    @JsonProperty("id", Number)
    private id: number = undefined;

    @JsonProperty("val", Number)
    private value: number = undefined;

    @JsonProperty("mval", Number, true)
    private maxValue: number = undefined;

    @JsonProperty("nam", String)
    private name: string = undefined;

    @JsonProperty("new", Boolean, true)
    private new: boolean = false;

    @JsonProperty("mor", Boolean, true)
    private more: boolean = false;

    @JsonProperty("les", Boolean, true)
    private less: boolean = false;

    getValue() {
        return this.value;
    }

    getMaxValue() {
        return this.maxValue;
    }

    getName() {
        return this.name;
    }

    surpassThreshold() {
        if (this.getMaxValue()) {
            return this.getValue() >= this.getMaxValue() ;
        }

        return false ;
    }

    isNewValue() {
        return this.new;
    }

    isMoreValue() {
        return this.more;
    }

    isLessValue() {
        return this.less;
    }
 }