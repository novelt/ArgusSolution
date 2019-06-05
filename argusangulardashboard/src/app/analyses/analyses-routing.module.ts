import { AnalysesComponent } from './analyses.component';
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { Route } from '../core/route.service';
import { extract } from '../core/i18n.service';

const routes: Routes = Route.withShell([
  { path: 'analyses', component: AnalysesComponent, data: { title: extract('Analyses') }, pathMatch: 'full' }
]);

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
  providers: []
})
export class AnalysesRoutingModule { }
