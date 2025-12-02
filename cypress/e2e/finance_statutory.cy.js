describe('Finance - Ad-hoc statutory CSV download', () => {
  const demoUserEmail = 'agency_owner@example.test';
  const demoUserPassword = 'password';

  before(() => {
    // Ensure demo users exist (same pattern as other Cypress tests)
    cy.exec('php artisan db:seed --class=Database\\Seeders\\DemoUsersSeeder');
  });

  it('logs in, opens finance invoices page and downloads ad-hoc CSV', () => {
    // Login
    cy.visit('/login');
    cy.get('input[name=email]').type(demoUserEmail);
    cy.get('input[name=password]').type(demoUserPassword);
    cy.get('button[type=submit]').click();

    // Go to the finance invoices page
    cy.visit('/admin/finance/invoices');
    cy.contains('Invoices', { timeout: 5000 });

    // Prepare to stub the ad-hoc CSV endpoint and capture anchor filename
    const expectedType = 'tds';
    const expectedStart = '2025-10-01';
    const expectedEnd = '2025-10-31';
    const expectedFilename = `statutory-${expectedType}-${expectedStart}-${expectedEnd}.csv`;

    cy.intercept('POST', '/api/finance/reports/statutory/download', (req) => {
      // Assert request body contains our params
      expect(req.body).to.include({ type: expectedType, period_start: expectedStart, period_end: expectedEnd });
      req.reply({
        statusCode: 200,
        headers: {
          'content-disposition': `attachment; filename="${expectedFilename}"`,
          'content-type': 'text/csv; charset=utf-8'
        },
        body: 'header1,header2\nvalue1,value2\n'
      });
    }).as('adhocReq');

    // Intercept creation of anchor to capture the download attribute.
    cy.window().then((win) => {
      const origCreate = win.document.createElement.bind(win.document);
      win.document.createElement = function (tag) {
        const el = origCreate(tag);
        if (tag === 'a') {
          // store reference for later assertion
          win.__lastCreatedAnchor = el;
        }
        return el;
      };
    });

    // Set the form values (dates are already defaulted in the page but set explicitly)
    cy.get('input[type=date]').first().clear().type(expectedStart);
    cy.get('input[type=date]').eq(1).clear().type(expectedEnd);
    cy.get('select').select(expectedType);

    // Click the Download Ad-hoc CSV button
    cy.contains('Download Ad-hoc CSV').click();

    // Wait for intercepted request and then assert anchor download filename
    cy.wait('@adhocReq');
    cy.window().its('__lastCreatedAnchor').should('exist').then((a) => {
      // The code sets a.download = filename earlier; assert that
      expect(a.download).to.equal(expectedFilename);
    });
  });
});
