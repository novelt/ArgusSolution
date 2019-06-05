import { ShellMobileComponent } from './../core/shell/shell-mobile.component';
import { AuthenticationGuard } from './../core/authentication/authentication.guard';
import { ShellComponent } from './../core/shell/shell.component';
import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { extract } from '../core/i18n.service';
import { HomeComponent } from './home.component';

const routes : Routes = [
  // Routes Desktop
  { path: '', component: ShellComponent, canActivate: [AuthenticationGuard],
    children : [
      { path : 'home', component: HomeComponent, data: { title: extract('Home') }, pathMatch: 'full' }
    ]
  },

  // Routes Mobile
  { path: 'm', component: ShellMobileComponent, canActivate: [AuthenticationGuard], data: { reuse: true },
    children: [
      { path: 'home', component: HomeComponent, data: { title: extract('Home') }, pathMatch: 'full' }
    ]
  } 
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
  providers: []
})
export class HomeRoutingModule { }
