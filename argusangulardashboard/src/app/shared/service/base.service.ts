import { FilterService } from './filter/filter.service';
import { UnauthorizedError } from '../common/Error/unauthorized-error';
import { NotFoundError } from '../common/Error/not-found-error';
import { Observable } from 'rxjs/Observable';
import { Headers } from '@angular/http';
import { AuthenticationService } from '../../core/authentication/authentication.service';
import { AppError } from '../common/Error/app-error';
import { Router } from '@angular/router';

export class BaseService {

    constructor(private authenticationService: AuthenticationService,
                protected router : Router,
                protected filterService : FilterService) {
    }

    getAuthorizationHeaders(): Headers {
       return this.authenticationService.getAuthorizationHeaders();
    }

    getLanguage() {
        return this.authenticationService.credentials.lang;
    }

    getHomeSiteName() {
        return this.authenticationService.credentials.homeSiteName; 
    }

    catchError(error : Response) {
        if (error.status === 404) {
            return Observable.throw(new NotFoundError(error));
        } else if (error.status === 401) {
            return Observable.throw(new UnauthorizedError(error));
        }
        
        return Observable.throw(new AppError(error));  
    }

    subscribeError(error : AppError) {
        if (error instanceof UnauthorizedError) {
            this.authenticationService.logout()
                .subscribe(() => this.router.navigate(['/login'], { replaceUrl: true }));
            // try to refresh the token and re-try
        }
    }

    protected getSelectedSiteId() {
        let selectedSite = this.filterService.getSelectedSite();
        if (selectedSite != null) {
            return selectedSite.getId();
        }

        return null ;
    }

    protected getSelectedDateRangeStart() {
        let dateRange = this.filterService.getSelectedDateRangeStart();
        if (dateRange != null) {
            return dateRange.format('YYYY-MM-DD');
        }

        return null;
    }

    protected getSelectedDateRangeEnd() {
        let dateRange = this.filterService.getSelectedDateRangeEnd();
        if (dateRange != null) {
            return dateRange.format('YYYY-MM-DD');
        }
        return null;
    }

    protected getSelectedPeriod() {
        return this.filterService.getSelectedPeriod();
    }
}