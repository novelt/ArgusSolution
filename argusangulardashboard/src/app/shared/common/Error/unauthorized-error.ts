import { AppError } from './app-error';

export class UnauthorizedError extends AppError {
    constructor(originalError?:any) {
        super(originalError);
        alert('UnauthorizedError Error');
    }
}