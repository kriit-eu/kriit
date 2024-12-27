import { Page } from '@playwright/test';

export class AssignmentListPage {
    constructor(private page: Page) {}

    async openAssignment(assignmentName: string) {
        await this.page.getByText(assignmentName).click();
        await this.page.waitForLoadState('networkidle');
    }

    async verifyAssignmentVisible(assignmentName: string) {
        await this.page.getByText(assignmentName).waitFor({state: 'visible'});
    }
} 