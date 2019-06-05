import { ReportDetailsComponent } from './../shared/report/report-details/report-details.component';
import { ValidationMobileComponent } from './validation-mobile.component';
import { ShellMobileComponent } from './../core/shell/shell-mobile.component';
import { AuthenticationGuard } from './../core/authentication/authentication.guard';
import { ShellComponent } from './../core/shell/shell.component';
import { NgModule } from '@angular/core';
import { Routes, RouterModule, Router } from '@angular/router';
import { extract } from '../core/i18n.service';
import { ValidationComponent } from './validation.component';

const routes : Routes = [
  // Routes Desktop
  { path: '', component: ShellComponent, canActivate: [AuthenticationGuard], // data: { reuse: true }, 
    children:  [
      { path: 'validation', component: ValidationComponent, data: { title: extract('Validation'), pathMatch: 'full'},
        children: [
          { path: ':reportId', component: ReportDetailsComponent, data: { title: extract('Validation'), pathMatch: 'full'}}
        ]
      },
    ]
  },

  // Routes Mobile 
  { path: 'm', component: ShellMobileComponent, canActivate: [AuthenticationGuard], data: { reuse: true }, 
    children: [
      { path: 'validation', component: ValidationMobileComponent, data: { title: extract('Validation')} },
      { path: 'validation/:reportId', component: ReportDetailsComponent, data: { title: extract('Validation'), pathMatch: 'full'} }
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
  providers: [],
})
export class ValidationRoutingModule { 

  constructor() {
  }
}
