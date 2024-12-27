import { test, expect } from '@playwright/test';
import { LoginPage } from '../page-objects/LoginPage';
import { AssignmentListPage } from '../page-objects/AssignmentListPage';
import { AssignmentDetailPage } from '../page-objects/AssignmentDetailPage';
import testData from '../fixtures/test-data.json';

test.describe('Ülesannete esitamise töövoog', () => {
    test('Õpilane saab sisse logida ja esitada ülesande lahenduse', async ({ page }) => {
        const loginPage = new LoginPage(page);
        const assignmentListPage = new AssignmentListPage(page);
        const assignmentDetailPage = new AssignmentDetailPage(page);

        // Logi sisse
        await loginPage.login(testData.student.personalCode, testData.student.password);
        
        // Kontrolli, et oleme õigel lehel
        await expect(page.getByText('TAK99')).toBeVisible();

        // Ava ülesanne
        await assignmentListPage.openAssignment(testData.assignment.name);
        
        // Täida kriteeriumid
        await assignmentDetailPage.completeCriteria();
        
        // Esita lahendus
        await assignmentDetailPage.submitSolution(testData.assignment.solutionUrl);
        
        // Kontrolli edukat esitamist
        await expect(page.getByText('Solution submitted successfully!')).toBeVisible();
    });
}); 