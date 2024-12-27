// @ts-check
const {test, expect} = require('@playwright/test');

test('can login as student', async ({page}) => {

    // Define base URL constant
    const baseUrl = 'http://localhost/kriit/';

    // Navigate to login page
    await page.goto(baseUrl);

    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');

    // Check that login form elements are visible
    await expect(page.locator('#userPersonalCode')).toBeVisible();

    // Fill in personal code and wait for password field to appear
    await page.locator('#userPersonalCode').fill('31111111114');
    await page.locator('#password-field').waitFor({state: 'visible'});

    // Fill in password
    await page.locator('#userPassword').fill('demo');

    // Verify button is clickable before clicking
    await expect(page.locator('#submitButton')).toBeEnabled();
    await page.locator('#submitButton').click();

    // Wait for page load after login
    await page.waitForLoadState('networkidle');

    // Verify successful login
    await expect(page.getByText('TAK99')).toBeVisible();
});
