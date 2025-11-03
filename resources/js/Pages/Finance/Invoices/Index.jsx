import React, {useEffect, useState} from 'react';
import axios from 'axios';

export default function Index() {
  const [invoices, setInvoices] = useState([]);

  useEffect(() => {
    axios.get('/api/finance/invoices').then(r => setInvoices(r.data.data || []));
  }, []);

  return (
    <div>
      <h1>Invoices</h1>
      <table>
        <thead>
          <tr><th>ID</th><th>Date</th><th>Total</th><th>Status</th></tr>
        </thead>
        <tbody>
          {invoices.map(inv => (
            <tr key={inv.id}><td>{inv.id}</td><td>{inv.date}</td><td>{inv.total}</td><td>{inv.status}</td></tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
