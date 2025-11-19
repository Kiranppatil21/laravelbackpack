describe('Security Service SaaS - Comprehensive E2E Tests', () => {
  let testData = {
    userEmail: '',
    clientId: '',
    employeeId: ''
  };

  beforeEach(() => {
    // Visit application
    cy.visit('/');
    
    // Set up test data
    const timestamp = Date.now();
    testData.userEmail = `testuser+${timestamp}@example.com`;
  });

  describe('User Authentication Flow', () => {
    it('should complete registration and login flow', () => {
      // Test registration
      cy.visit('/register');
      cy.get('[data-cy=name]').type('Test User');
      cy.get('[data-cy=email]').type(testData.userEmail);
      cy.get('[data-cy=password]').type('password123');
      cy.get('[data-cy=password_confirmation]').type('password123');
      cy.get('[data-cy=register]').click();

      // Should redirect to dashboard after registration
      cy.url().should('include', '/dashboard');
      cy.contains('Welcome');

      // Test logout
      cy.get('[data-cy=user-dropdown]').click();
      cy.get('[data-cy=logout]').click();
      cy.url().should('include', '/');

      // Test login
      cy.visit('/login');
      cy.get('[data-cy=email]').type(testData.userEmail);
      cy.get('[data-cy=password]').type('password123');
      cy.get('[data-cy=login]').click();

      cy.url().should('include', '/dashboard');
    });
  });

  describe('Agency & Client Management', () => {
    beforeEach(() => {
      // Login first
      cy.login(testData.userEmail, 'password123');
    });

    it('should create and manage agencies', () => {
      cy.visit('/admin/agency');
      
      // Create new agency
      cy.get('[data-cy=create-agency]').click();
      cy.get('[data-cy=name]').type('Test Security Agency');
      cy.get('[data-cy=save]').click();

      // Verify agency created
      cy.contains('Test Security Agency');
      cy.contains('successfully created');
    });

    it('should create and manage clients', () => {
      cy.visit('/admin/client');
      
      // Create new client
      cy.get('[data-cy=create-client]').click();
      cy.get('[data-cy=name]').type('Corporate Client Ltd');
      cy.get('[data-cy=email]').type('client@corporate.com');
      cy.get('[data-cy=phone]').type('+91-9876543210');
      cy.get('[data-cy=address]').type('123 Business District, Mumbai');
      cy.get('[data-cy=save]').click();

      // Store client ID for later tests
      cy.url().then((url) => {
        testData.clientId = url.split('/').pop();
      });

      cy.contains('Corporate Client Ltd');
      cy.contains('successfully created');
    });
  });

  describe('Comprehensive Employee Management', () => {
    beforeEach(() => {
      cy.login(testData.userEmail, 'password123');
    });

    it('should create employee with complete details and dynamic sections', () => {
      cy.visit('/admin/employee/create');

      // Fill basic employee information
      cy.get('[data-cy=designation]').select('Security Guard');
      cy.get('[data-cy=education]').type('High School');
      cy.get('[data-cy=name]').type('John Security Guard');
      cy.get('[data-cy=father_name]').type('Robert Guard');
      cy.get('[data-cy=nationality]').type('Indian');
      cy.get('[data-cy=current_address]').type('123 Guard Colony, Mumbai');
      cy.get('[data-cy=permanent_address]').type('456 Village, Pune');

      // Contact information
      cy.get('[data-cy=email]').type('john.guard@example.com');
      cy.get('[data-cy=phone]').type('+91-9876543210');
      cy.get('[data-cy=date_of_birth]').type('1990-05-15');

      // Employment details
      cy.get('[data-cy=client_id]').select('Corporate Client Ltd');
      cy.get('[data-cy=monthly_salary]').type('25000');
      cy.get('[data-cy=joining_date]').type('2025-01-01');

      // Identity Proofs Section
      cy.get('[data-cy=add-identity-proof]').click();
      cy.get('[data-cy=identity_proof_type_0]').select('aadhar_card');
      cy.get('[data-cy=identity_proof_no_0]').type('123456789012');
      
      // Upload identity document
      cy.fixture('test-documents/aadhar.jpg').then(fileContent => {
        cy.get('[data-cy=identity_image_0]').attachFile({
          fileContent: fileContent.toString(),
          fileName: 'aadhar.jpg',
          mimeType: 'image/jpeg'
        });
      });

      // Add second identity proof
      cy.get('[data-cy=add-identity-proof]').click();
      cy.get('[data-cy=identity_proof_type_1]').select('pan_card');
      cy.get('[data-cy=identity_proof_no_1]').type('ABCDE1234F');

      // Family Members Section
      cy.get('[data-cy=add-family-member]').click();
      cy.get('[data-cy=family_name_0]').type('Jane Guard');
      cy.get('[data-cy=family_relationship_0]').select('spouse');
      cy.get('[data-cy=family_age_0]').type('28');
      cy.get('[data-cy=family_phone_0]').type('+91-9876543211');
      cy.get('[data-cy=family_nominee_0]').check();

      // Add child
      cy.get('[data-cy=add-family-member]').click();
      cy.get('[data-cy=family_name_1]').type('Baby Guard');
      cy.get('[data-cy=family_relationship_1]').select('son');
      cy.get('[data-cy=family_age_1]').type('5');

      // Acquaintances Section
      cy.get('[data-cy=add-acquaintance]').click();
      cy.get('[data-cy=acquaintance_name_0]').type('Reference Person');
      cy.get('[data-cy=acquaintance_relationship_0]').select('friend');
      cy.get('[data-cy=acquaintance_phone_0]').type('+91-9876543212');
      cy.get('[data-cy=acquaintance_address_0]').type('789 Reference Street, Mumbai');

      // Uniform Allocation Section
      cy.get('[data-cy=add-uniform]').click();
      cy.get('[data-cy=uniform_client_0]').select('Corporate Client Ltd');
      cy.get('[data-cy=uniform_type_0]').type('Security Uniform Set');
      cy.get('[data-cy=uniform_size_0]').select('L');
      cy.get('[data-cy=uniform_quantity_0]').type('2');
      cy.get('[data-cy=uniform_issue_date_0]').type('2025-01-01');

      // Banking Information
      cy.get('[data-cy=bank_name]').type('State Bank of India');
      cy.get('[data-cy=account_number]').type('1234567890123456');
      cy.get('[data-cy=ifsc_code]').type('SBIN0001234');
      cy.get('[data-cy=account_holder_name]').type('John Security Guard');

      // Save employee
      cy.get('[data-cy=save-employee]').click();

      // Verify creation
      cy.contains('John Security Guard');
      cy.contains('successfully created');

      // Store employee ID
      cy.url().then((url) => {
        testData.employeeId = url.split('/').pop();
      });
    });

    it('should edit employee and verify dynamic sections', () => {
      cy.visit(`/admin/employee/${testData.employeeId}/edit`);

      // Verify existing data is loaded
      cy.get('[data-cy=name]').should('have.value', 'John Security Guard');
      cy.get('[data-cy=email]').should('have.value', 'john.guard@example.com');

      // Verify identity proofs loaded
      cy.get('[data-cy=identity_proof_no_0]').should('have.value', '123456789012');
      cy.get('[data-cy=identity_proof_no_1]').should('have.value', 'ABCDE1234F');

      // Verify family members loaded
      cy.get('[data-cy=family_name_0]').should('have.value', 'Jane Guard');
      cy.get('[data-cy=family_name_1]').should('have.value', 'Baby Guard');

      // Add new family member
      cy.get('[data-cy=add-family-member]').click();
      cy.get('[data-cy=family_name_2]').type('Mother Guard');
      cy.get('[data-cy=family_relationship_2]').select('mother');
      cy.get('[data-cy=family_age_2]').type('55');

      // Update salary
      cy.get('[data-cy=monthly_salary]').clear().type('30000');

      // Save changes
      cy.get('[data-cy=save-employee]').click();

      // Verify updates
      cy.contains('successfully updated');
      cy.contains('Mother Guard');
    });

    it('should delete employee dynamic section items', () => {
      cy.visit(`/admin/employee/${testData.employeeId}/edit`);

      // Remove second identity proof
      cy.get('[data-cy=remove-identity-proof-1]').click();

      // Remove child from family members
      cy.get('[data-cy=remove-family-member-1]').click();

      // Save changes
      cy.get('[data-cy=save-employee]').click();

      // Verify removals
      cy.get('[data-cy=identity_proof_no_1]').should('not.exist');
      cy.get('[data-cy=family_name_1]').should('not.contain', 'Baby Guard');
    });
  });

  describe('Attendance & Payroll Integration', () => {
    beforeEach(() => {
      cy.login(testData.userEmail, 'password123');
    });

    it('should handle employee check-in and check-out', () => {
      cy.visit('/attendance/checkin');

      // Manual check-in
      cy.get('[data-cy=employee-select]').select('John Security Guard');
      cy.get('[data-cy=manual-checkin]').click();

      cy.contains('checked-in');

      // Check-out
      cy.get('[data-cy=checkout]').click();
      cy.contains('checked-out');
    });

    it('should generate payroll for employee', () => {
      cy.visit('/payslips/admin-run');

      // Set payroll period
      cy.get('[data-cy=period-start]').type('2025-01-01');
      cy.get('[data-cy=period-end]').type('2025-01-31');
      cy.get('[data-cy=tax-regime]').select('old');

      // Generate payroll
      cy.get('[data-cy=run-payroll]').click();

      cy.contains('created');
      cy.contains('done');
    });

    it('should view attendance reports', () => {
      cy.visit('/attendance/admin-reports');

      // Set report parameters
      cy.get('[data-cy=report-from]').type('2025-01-01');
      cy.get('[data-cy=report-to]').type('2025-01-31');

      // Generate report
      cy.get('[data-cy=load-reports]').click();

      // Should show attendance data
      cy.get('[data-cy=attendance-table]').should('be.visible');
    });
  });

  describe('Finance & Compliance Features', () => {
    beforeEach(() => {
      cy.login(testData.userEmail, 'password123');
    });

    it('should create invoice with multiple line items', () => {
      cy.visit('/finance/invoices');

      cy.get('[data-cy=create-invoice]').click();

      // Invoice details
      cy.get('[data-cy=client-id]').select('Corporate Client Ltd');
      cy.get('[data-cy=issued-date]').type('2025-01-01');
      cy.get('[data-cy=due-date]').type('2025-01-31');

      // First line item
      cy.get('[data-cy=item-description-0]').type('Security Guard Services - January');
      cy.get('[data-cy=item-qty-0]').type('31');
      cy.get('[data-cy=item-unit-price-0]').type('1000');
      cy.get('[data-cy=item-tax-rate-0]').type('18');

      // Add second line item
      cy.get('[data-cy=add-line-item]').click();
      cy.get('[data-cy=item-description-1]').type('Night Shift Premium');
      cy.get('[data-cy=item-qty-1]').type('15');
      cy.get('[data-cy=item-unit-price-1]').type('500');
      cy.get('[data-cy=item-tax-rate-1]').type('18');

      // Save invoice
      cy.get('[data-cy=save-invoice]').click();

      // Verify totals
      cy.contains('₹31,000'); // Base amount first item
      cy.contains('₹7,500');  // Base amount second item
      cy.contains('₹6,930');  // Tax amount
      cy.contains('₹45,430'); // Total amount
    });

    it('should record payment against invoice', () => {
      // Assuming invoice was created in previous test
      cy.visit('/finance/invoices');
      
      cy.get('[data-cy=invoice-row]:first').click();
      cy.get('[data-cy=record-payment]').click();

      // Payment details
      cy.get('[data-cy=payment-amount]').type('45430');
      cy.get('[data-cy=payment-method]').select('bank_transfer');
      cy.get('[data-cy=payment-reference]').type('TXN123456789');
      cy.get('[data-cy=payment-date]').type('2025-01-15');

      cy.get('[data-cy=save-payment]').click();

      // Verify payment recorded
      cy.contains('Payment recorded');
      cy.contains('Paid'); // Status should change to paid
    });

    it('should generate GST statutory report', () => {
      cy.visit('/finance/invoices');

      // Set report period
      cy.get('[data-cy=report-period-start]').type('2025-01-01');
      cy.get('[data-cy=report-period-end]').type('2025-01-31');

      // Generate GST report
      cy.get('[data-cy=generate-gst-csv]').click();

      // Should trigger download
      cy.contains('Generating...');
      
      // Wait for completion
      cy.get('[data-cy=generate-gst-csv]').should('not.contain', 'Generating...');
    });

    it('should generate adhoc statutory reports', () => {
      cy.visit('/finance/invoices');

      // Test different report types
      const reportTypes = ['gst', 'tds', 'pf', 'esic'];

      reportTypes.forEach(type => {
        cy.get('[data-cy=adhoc-type]').select(type.toUpperCase());
        cy.get('[data-cy=generate-adhoc-csv]').click();
        
        // Should trigger download
        cy.get('[data-cy=adhoc-status]').should('contain', 'downloading');
        
        // Wait a bit for next iteration
        cy.wait(1000);
      });
    });
  });

  describe('Security & Authorization', () => {
    it('should enforce proper access control', () => {
      // Test unauthenticated access
      cy.visit('/admin/employee');
      cy.url().should('include', '/login');

      // Test with wrong credentials
      cy.get('[data-cy=email]').type('wrong@example.com');
      cy.get('[data-cy=password]').type('wrongpassword');
      cy.get('[data-cy=login]').click();

      cy.contains('credentials do not match');
    });

    it('should maintain tenant data isolation', () => {
      // Login as first user
      cy.login(testData.userEmail, 'password123');
      cy.visit('/admin/employee');
      
      // Should see their employees
      cy.contains('John Security Guard');
      
      // Logout and create second user
      cy.logout();
      
      const secondUserEmail = `testuser2+${Date.now()}@example.com`;
      cy.register(secondUserEmail, 'password123');
      
      // Second user should not see first user's data
      cy.visit('/admin/employee');
      cy.should('not.contain', 'John Security Guard');
    });
  });

  describe('Performance & Error Handling', () => {
    beforeEach(() => {
      cy.login(testData.userEmail, 'password123');
    });

    it('should handle file upload validation', () => {
      cy.visit('/admin/employee/create');

      // Try uploading invalid file type
      cy.fixture('test-documents/invalid.txt').then(fileContent => {
        cy.get('[data-cy=identity_image_0]').attachFile({
          fileContent: fileContent.toString(),
          fileName: 'invalid.txt',
          mimeType: 'text/plain'
        });
      });

      cy.get('[data-cy=save-employee]').click();

      // Should show validation error
      cy.contains('must be a file of type');
    });

    it('should handle form validation errors gracefully', () => {
      cy.visit('/admin/employee/create');

      // Try submitting empty form
      cy.get('[data-cy=save-employee]').click();

      // Should show validation errors
      cy.contains('field is required');
      cy.get('[data-cy=name]').should('have.class', 'is-invalid');
    });

    it('should handle network errors gracefully', () => {
      // Simulate network failure
      cy.intercept('/api/employees', { forceNetworkError: true });

      cy.visit('/admin/employee');

      // Should show error message
      cy.contains('error loading');
    });
  });

  // Cleanup
  after(() => {
    // Clean up test data if needed
    cy.task('cleanupTestData', {
      userEmail: testData.userEmail,
      clientId: testData.clientId,
      employeeId: testData.employeeId
    });
  });
});

// Custom commands for common operations
Cypress.Commands.add('login', (email, password) => {
  cy.session([email, password], () => {
    cy.visit('/login');
    cy.get('[data-cy=email]').type(email);
    cy.get('[data-cy=password]').type(password);
    cy.get('[data-cy=login]').click();
    cy.url().should('include', '/dashboard');
  });
});

Cypress.Commands.add('register', (email, password) => {
  cy.visit('/register');
  cy.get('[data-cy=name]').type('Test User');
  cy.get('[data-cy=email]').type(email);
  cy.get('[data-cy=password]').type(password);
  cy.get('[data-cy=password_confirmation]').type(password);
  cy.get('[data-cy=register]').click();
  cy.url().should('include', '/dashboard');
});

Cypress.Commands.add('logout', () => {
  cy.get('[data-cy=user-dropdown]').click();
  cy.get('[data-cy=logout]').click();
  cy.url().should('include', '/');
});