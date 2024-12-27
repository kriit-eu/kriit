import { Page } from '@playwright/test';

export class LoginPage {
    constructor(private page: Page) {}

    async login(personalCode: string, password: string) {
        await this.page.goto('http://localhost/kriit/');
        await this.page.locator('#userPersonalCode').fill(personalCode);
        await this.page.locator('#password-field').waitFor({state: 'visible'});
        await this.page.locator('#userPassword').fill(password);
        await this.page.locator('#submitButton').click();
        await this.page.waitForLoadState('networkidle');
    }
} 