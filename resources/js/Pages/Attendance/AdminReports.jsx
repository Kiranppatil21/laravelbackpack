import React, {useEffect, useState} from 'react';

export default function AdminReports({tenantUuid}){
  const [rows, setRows] = useState([]);
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');

  const load = async () => {
    const q = new URLSearchParams({tenant_uuid: tenantUuid, from, to}).toString();
    const res = await fetch(`/api/attendance/reports?${q}`);
    const json = await res.json();
    setRows(json.data || []);
  };

  useEffect(()=>{load()}, []);

  const exportCSV = () => {
    const header = ['id','employee_id','check_in_at','check_out_at'];
    const lines = rows.map(r => [r.id, r.employee_id, r.check_in_at, r.check_out_at].join(','));
    const csv = [header.join(','), ...lines].join('\n');
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'attendance.csv'; a.click();
  };

  return (
    <div>
      <h1>Attendance Reports</h1>
      <label>From: <input type="date" value={from} onChange={e=>setFrom(e.target.value)} /></label>
      <label>To: <input type="date" value={to} onChange={e=>setTo(e.target.value)} /></label>
      <button onClick={load}>Filter</button>
      <button onClick={exportCSV}>Export CSV</button>
      <table>
        <thead><tr><th>ID</th><th>Employee</th><th>Check In</th><th>Check Out</th></tr></thead>
        <tbody>
          {rows.map(r => (
            <tr key={r.id}><td>{r.id}</td><td>{r.employee_id}</td><td>{r.check_in_at}</td><td>{r.check_out_at}</td></tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
