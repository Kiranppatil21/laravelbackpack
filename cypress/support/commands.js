// Test fixtures directory structure:
// cypress/fixtures/test-documents/aadhar.jpg
// cypress/fixtures/test-documents/pan.jpg
// cypress/fixtures/test-documents/invalid.txt

// Custom commands extension for file attachments
import 'cypress-file-upload';

// Global test configuration
Cypress.config('defaultCommandTimeout', 10000);
Cypress.config('requestTimeout', 10000);
Cypress.config('responseTimeout', 10000);

// Custom task for cleanup
Cypress.Commands.add('cleanupTestData', (data) => {
  cy.task('cleanupTestData', data);
});