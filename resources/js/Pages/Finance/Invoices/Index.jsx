import React, {useEffect, useState} from 'react';
import axios from 'axios';

export default function Index() {
  const [invoices, setInvoices] = useState([]);
  const [periodStart, setPeriodStart] = useState('2025-10-01');
  const [periodEnd, setPeriodEnd] = useState('2025-10-31');
  const [reportLoading, setReportLoading] = useState(false);
  const [adhocType, setAdhocType] = useState('gst');
  const [adhocLoading, setAdhocLoading] = useState(false);

  useEffect(() => {
    axios.get('/api/finance/invoices').then(r => setInvoices(r.data.data || []));
  }, []);

  return (
    <div>
      <h1>Invoices</h1>
      <div style={{marginBottom: 16, padding: 8, border: '1px solid #eee'}}>
        <h3>Statutory Reports (GST)</h3>
        <label>Period start: <input type="date" value={periodStart} onChange={e => setPeriodStart(e.target.value)} /></label>
        <label style={{marginLeft:8}}>Period end: <input type="date" value={periodEnd} onChange={e => setPeriodEnd(e.target.value)} /></label>
        <button style={{marginLeft:8}} onClick={async () => {
          setReportLoading(true);
          try {
            const res = await axios.post('/api/finance/reports/statutory', {
              type: 'gst',
              period_start: periodStart,
              period_end: periodEnd
            });
            const id = res.data.id;
            // Open download in new tab
            window.open(`/api/finance/reports/statutory/${id}/download`, '_blank');
          } catch (err) {
            console.error(err);
            alert('Failed to generate report');
          } finally {
            setReportLoading(false);
          }
        }}>{reportLoading ? 'Generating...' : 'Generate GST CSV'}</button>

        <div style={{display: 'inline-block', marginLeft: 12}}>
          <label>Ad-hoc type:
            <select value={adhocType} onChange={e => setAdhocType(e.target.value)} style={{marginLeft:6}}>
              <option value="gst">GST</option>
              <option value="tds">TDS</option>
              <option value="pf">PF</option>
              <option value="esic">ESIC</option>
            </select>
          </label>
          <button style={{marginLeft:8}} onClick={async () => {
            setAdhocLoading(true);
            try {
              const res = await axios.post('/api/finance/reports/statutory/download', {
                type: adhocType,
                period_start: periodStart,
                period_end: periodEnd,
              }, { responseType: 'blob' });

              // derive filename from Content-Disposition or fallback
              const disposition = res.headers['content-disposition'] || '';
              let filename = `statutory-${adhocType}-${periodStart}-${periodEnd}.csv`;
              const match = /filename\*?=([^;]+)/i.exec(disposition);
              if (match && match[1]) {
                filename = match[1].replace(/UTF-8''/, '').replace(/"/g, "");
              }

              const blob = new Blob([res.data], { type: 'text/csv' });
              const url = window.URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = url;
              a.download = filename;
              document.body.appendChild(a);
              a.click();
              a.remove();
              window.URL.revokeObjectURL(url);
            } catch (err) {
              console.error(err);
              alert('Ad-hoc CSV download failed');
            } finally {
              setAdhocLoading(false);
            }
          }}>{adhocLoading ? 'Downloading...' : 'Download Ad-hoc CSV'}</button>
        </div>
      </div>
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
