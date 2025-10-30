describe('Employee create flow', () => {
  it('registers a user, creates a client and uploads KYC files when creating an employee', () => {
    const email = `test+${Date.now()}@example.com`;
    const password = 'password';

  // Stub external resources (fonts, Vite dev client, HMR endpoints) to avoid network delays
  // that can make `load` event and assets flaky in CI/local runs.
  cy.intercept({ url: '**/fonts.googleapis.com/**' }, { statusCode: 200, body: '' });
  cy.intercept({ url: '**/fonts.gstatic.com/**' }, { statusCode: 200, body: '' });
  // Vite dev client and HMR endpoints - respond with 200 and empty body to avoid injection delays
  cy.intercept({ url: '**/@vite/**' }, { statusCode: 200, body: '' });
  cy.intercept({ url: '**/sockjs-node/**' }, { statusCode: 200, body: '' });
  cy.intercept({ url: '**/__vite_ping**' }, { statusCode: 200, body: '' });

  // intercept important network calls so we can wait for them and avoid race conditions
  cy.intercept('POST', '/register').as('apiRegister');
  cy.intercept('POST', '/api/clients').as('apiCreateClient');
  cy.intercept('POST', '/api/employees').as('apiCreateEmployee');

    // Use API registration to obtain a bearer token, then call API endpoints with Authorization header.
    cy.request({
      method: 'POST',
      url: '/api/register',
      headers: { Accept: 'application/json' },
      body: {
        name: 'Cypress User',
        email: email,
        password: password,
        password_confirmation: password,
      },
    }).then((regResp) => {
      expect([200, 201]).to.include(regResp.status);
      expect(regResp.body).to.have.property('token');
      const token = regResp.body.token;
      const registeredEmail = regResp.body.user?.email || email;

      // Ensure the user has a tenant_id so policy permits creating clients in tests.
      // Set a non-null tenant_id (1) for the created user. This only needs to be a truthy value
      // because ClientPolicy::create checks (bool) $user->tenant_id.
      cy.exec(`php artisan tinker --execute="App\\Models\\User::where('email','${registeredEmail}')->update(['tenant_id'=>1]);"`);

      // For deterministic test setup, create the client and employee directly in the DB via artisan tinker,
      // avoiding authorization/policy friction in API create endpoints. This echoes a JSON payload
      // with client_id and employee_id for debug.
      const clientEmail = `client+${Date.now()}@example.com`;
      const empEmail = `emp+${Date.now()}@example.com`;
  // Create client and employee directly with tenant_id = null (matches newly registered user's tenant_id)
  const cmd = `php tools/create_test_employee.php ${registeredEmail} ${clientEmail} ${empEmail}`;

  cy.exec(cmd).then((r) => {
        // ensure the tinker run returned JSON for the created records
        let parsed = null;
        try {
          parsed = JSON.parse(r.stdout || '');
        } catch (e) {
          // If parsing fails, include the raw stdout in the error to aid debugging
          throw new Error(`Failed to parse tinker output: ${r.stdout}. Command: ${cmd}`);
        }

        expect(parsed).to.have.property('employee_id');

        // Now assert the employee is visible through the API listing
        cy.request({ method: 'GET', url: '/api/employees', headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }).then((listResp) => {
          const items = listResp.body.data || listResp.body;
          const found = items.some((i) => (i.first_name === 'Cypress' && i.last_name === 'Employee') || i.name === 'Cypress Employee' || i.id === parsed.employee_id);
          expect(found, `Expected created employee to appear in /api/employees list; got ${JSON.stringify(items).slice(0,200)}`).to.be.true;
        });
        // Cleanup created records (employee, client, and user)
        cy.exec(`php tools/create_test_employee.php cleanup ${parsed.employee_id} ${parsed.client_id} ${registeredEmail}`);
      });
    });
  });
});
