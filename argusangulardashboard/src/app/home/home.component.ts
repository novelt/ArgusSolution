import { UrlService } from './../shared/service/url/url.service';
import { Router } from '@angular/router';
import { Script } from './../shared/model/script';
import { ISubscription } from 'rxjs/Subscription';
import { ScriptService } from '../shared/service/r/script.service';
import { Component, OnInit } from '@angular/core';

/**
 * Home page class component.
 *
 * @class HomeComponent
 * @implements OnInit
 * @constructor
 **/
@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})
export class HomeComponent implements OnInit {

  public scriptList: Script[];
  public indexActive = 0;
  public scriptUrl = '';

  isScriptsCollapsed = true;

  private scriptObservable: ISubscription;

  constructor(private scriptService: ScriptService,
              private router: Router,
              private urlService: UrlService) {  }

  ngOnInit() {
    this.bindScripts();
    this.scriptService.loadScripts();
    
    this.init();
  }

  private init() {
    this.scriptList = new Array<Script>();
  }

  private bindScripts() {
    this.scriptObservable = this.scriptService.getScripts().subscribe(scripts => {
        this.scriptList = scripts;
        this.selectScript(0);
    });
  }

  isActive(index: number) {
    return index == this.indexActive;
  }

  selectScript(index: number) {
    this.indexActive = index;
    if (this.scriptList.length > 0 && this.indexActive >= 0 && this.indexActive <= this.scriptList.length) {
      let activeScript = this.scriptList[this.indexActive];
      this.scriptUrl = this.urlService.getRScriptUrl()
                        + activeScript.getDirectory() + '/'
                        + activeScript.getFile();
    }

    this.isScriptsCollapsed = true;
  }

  goToMenu() {
    this.router.navigate(['m/menu']);
  }

  collapseMenuScripts() {
    this.isScriptsCollapsed = !this.isScriptsCollapsed;
  }

  ngOnDestroy() {
    this.scriptObservable != null ? this.scriptObservable.unsubscribe() : null ;
  }
}

// Get config file
    // this.server = this.config.getConfig('server');
    // console.log(this.server);
    //  console.log('Home Init');

    //var accessControlHeader = new Headers();
    //accessControlHeader.set('Access-Control-Allow-Origin', '*');
    //accessControlHeader.set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Access-Control-Allow-Headers, Access-Control-Allow-Origin');
    // accessControlHeader.set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    /* this.http.get("http://localhost/dashboard-test-gh-pages/index.php", { headers: accessControlHeader })
     .subscribe(
      html => { 
        console.log(html)
        this.myTemplate = html.text();
    });*/
