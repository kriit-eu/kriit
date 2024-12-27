import { Page } from '@playwright/test';

export class AssignmentDetailPage {
    constructor(private page: Page) {}

    async completeCriteria() {
        const criteria = this.page.locator('.list-group-item input[type="checkbox"]');
        const count = await criteria.count();
        
        for (let i = 0; i < count; i++) {
            await criteria.nth(i).check();
            await this.page.waitForLoadState('networkidle');
        }
    }

    async submitSolution(solutionUrl: string) {
        await this.page.locator('#solutionUrl').fill(solutionUrl);
        await this.page.locator('#submitSolutionButton').click();
        await this.page.waitForLoadState('networkidle');
    }
} 