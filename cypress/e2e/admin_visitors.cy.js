describe('Admin Visitors - filters & pagination', () => {
  const demoUserEmail = 'agency_owner@example.test';
  const demoUserPassword = 'password';

  before(() => {
    // Ensure demo users exist
    cy.exec('php artisan db:seed --class=Database\\Seeders\\DemoUsersSeeder');
    // Create sample visitor and 30 visit logs for pagination with host set to demo user
    const tinker = `
      $host = App\\Models\\User::where('email','agency_owner@example.test')->first();
      if(! $host) { $host = App\\Models\\User::factory()->create(['email'=>'agency_owner@example.test','name'=>'Agency Owner Demo','password'=>bcrypt('password')]); }
      $v = App\\Models\\Visitor::factory()->create(['name' => 'Cypress Visitor', 'email' => 'cv@example.test']);
      App\\Models\\VisitLog::factory()->count(30)->create(['visitor_id' => $v->id, 'host_id' => $host->id]);
    `;
    cy.exec(`php artisan tinker --execute "${tinker.replace(/"/g, '\\"')}"`);
  });

  after(() => {
    // Cleanup created test data
    const cleanup = `
      App\\Models\\VisitLog::whereHas('visitor', function($q){ $q->where('email','cv@example.test'); })->delete();
      App\\Models\\Visitor::where('email','cv@example.test')->delete();
    `;
    cy.exec(`php artisan tinker --execute "${cleanup.replace(/"/g, '\\"')}"`);
  });

  it('logs in and visits the visitors admin page', () => {
    cy.visit('/login');
    cy.get('input[name=email]').type(demoUserEmail);
    cy.get('input[name=password]').type(demoUserPassword);
    cy.get('button[type=submit]').click();

    // After login, visit the visitors page
    cy.visit('/admin/visitors');
    cy.contains('Visitors', { timeout: 5000 });
  });

  it('applies search filter and pagination', () => {
    // Ensure visitor rows are present
    cy.get('[data-cy^="visit-row-"]').should('exist');

    // Search for our Cypress Visitor
    cy.get('input[name=search]').clear().type('Cypress Visitor');
    cy.get('button').contains('Apply').click();

    // Assert visible rows contain our visitor
    cy.get('[data-cy^="visit-row-"]').should('contain.text', 'Cypress Visitor');

    // Test pagination: set per page to 10, then navigate pages and assert counts
    cy.get('select').select('10');
    cy.wait(500);
    cy.get('[data-cy^="visit-row-"]').its('length').should('be.lte', 10);

    // Navigate to next page if available
    cy.get('[data-cy="visitors-next"]').then(($btn) => {
      if (!$btn.prop('disabled')) {
        cy.wrap($btn).click();
        cy.wait(500);
        cy.get('[data-cy^="visit-row-"]').its('length').should('be.lte', 10);
      }
    });

    // Assert the UI shows total count text
    cy.contains(/Showing \d+ of \d+/).should('exist');
  });
});
