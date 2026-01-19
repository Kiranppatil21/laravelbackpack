describe('Visitor check-in flow', () => {
  it('allows a kiosk to POST a checkin and then checkout', () => {
    const payload = {
      name: 'Cypress Visitor',
      email: 'cypress@example.test',
      phone: '9000000000',
      source: 'cypress'
    };

    // create checkin via API
    const headers = {};
    if (Cypress.env('VISITOR_API_KEY')) {
      headers['X-VISITOR-API-KEY'] = Cypress.env('VISITOR_API_KEY');
    }

    cy.request({ method: 'POST', url: '/api/visitors/checkin', body: payload, headers }).then((resp) => {
      expect(resp.status).to.equal(201);
      expect(resp.body).to.have.property('visitor');
      expect(resp.body).to.have.property('visit');
      const visitId = resp.body.visit.id;

      // then checkout
      cy.request('POST', `/api/visitors/${visitId}/checkout`).then((r2) => {
        expect(r2.status).to.be.oneOf([200,201]);
        expect(r2.body).to.have.property('visit');
      });
    });
  });
});
