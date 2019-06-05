import { ReportDetailsComponent } from './../shared/report/report-details/report-details.component';
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { extract } from '../core/i18n.service';
import { ArchivesComponent } from './archives.component';
import { ArchivesMobileComponent } from './archives-mobile.component';

const routesDesktop: Routes = [
  // Routes Desktop
  { path: '', component: ArchivesComponent, data: { title: extract('Archives'), pathMatch: 'full'},
    children: [
      { path: ':reportId', component: ReportDetailsComponent, data: { title: extract('Archives'), pathMatch: 'full'}}
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routesDesktop)],
  exports: [RouterModule],
  providers: []
})
export class ArchivesDesktopRoutingModule { }


const mobileRoutes : Routes = [
  // Routes Mobile 
  { path: '', component: ArchivesMobileComponent, data: { title: extract('Archives') }},
  { path: ':reportId', component: ReportDetailsComponent, data: { title: extract('Archives'), pathMatch: 'full' }},
];


@NgModule({
  imports: [RouterModule.forChild(mobileRoutes)],
  exports: [RouterModule],
  providers: []
})
export class ArchivesMobileRoutingModule { }
