import { ShellComponent } from './core/shell/shell.component';
import { SideComponent } from './core/shell/side/side.component';
import { AuthenticationGuard } from './core/authentication/authentication.guard';
import { ShellMobileComponent } from './core/shell/shell-mobile.component';
import { NgModule } from '@angular/core';
import { Routes, RouterModule, Router } from '@angular/router';


const routes: Routes = [
  { path: 'm', component: ShellMobileComponent, canActivate: [AuthenticationGuard], data: { reuse: true }, 
    children: [
      { path: 'menu', component: SideComponent, data: { title: 'Menu', pathMatch: 'full'}},
      { path: 'archives', loadChildren: 'app/archives/archives.module#ArchivesMobileModule' }
    ]
  },

  { path: '', component: ShellComponent, canActivate: [AuthenticationGuard], // data: { reuse: true }, 
    children:  [
      { path: 'archives', loadChildren: 'app/archives/archives.module#ArchivesDesktopModule' },
    ]  
  },
    
  { path: '**', redirectTo: '/login'}
];

// export const routing: ModuleWithProviders = RouterModule.forRoot(routes);

@NgModule({
  imports: [RouterModule.forRoot(routes, { useHash: true })], // , 
  exports: [RouterModule],
  providers: []
})
export class AppRoutingModule {   

  constructor(private router :Router) {
    console.log('AppRoutingModule');
    console.log(this.router);

    this.router.config.unshift( { path: '', redirectTo:'/login', pathMatch: 'full'});
  }
}
