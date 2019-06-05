import { AppConfig } from './../../../core/app-config';
import { environment } from './../../../../environments/environment';
import { Injectable } from '@angular/core';

@Injectable()
export class UrlService { 

    constructor(private config: AppConfig) {
    }

    public getServerUrl() {
        return this.getHostName() + this.config.getConfig('serverUrl'); // environment.serverUrl;
    }

    public getServerReportUrl() {
        return this.getHostName() + this.config.getConfig('serverReportUrl'); // environment.serverReportUrl;
    }

    public getRScriptUrl() {
        return this.getHostName() + this.config.getConfig('rScriptsUrl'); // environment.rScriptsUrl;
    }

    private getHostName(): string {
        let hostName = location.protocol + '//' + environment.hostname;
        console.log(hostName);
        return hostName;
    }
}
