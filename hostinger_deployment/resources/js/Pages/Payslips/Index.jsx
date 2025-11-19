import React, {useEffect, useState} from 'react';

export default function PayslipsIndex(){
  const [payslips, setPayslips] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/payslips')
      .then(r => r.json())
      .then(data => {
        setPayslips(data.data || data);
      })
      .catch(() => setPayslips([]))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Loading...</div>;

  return (
    <div>
      <h1>Payslips</h1>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Period</th>
            <th>Net</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {payslips.map(p => (
            <tr key={p.id}>
              <td>{p.id}</td>
              <td>{p.period_start} — {p.period_end}</td>
              <td>{p.net}</td>
              <td>
                <a href={`/api/payslips/${p.id}/download`} target="_blank" rel="noopener noreferrer">Download PDF</a>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
