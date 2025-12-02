import React, {useState} from 'react';

export default function PayslipsAdminRun(){
  const [periodStart, setPeriodStart] = useState('2025-10-01');
  const [periodEnd, setPeriodEnd] = useState('2025-10-31');
  const [regime, setRegime] = useState('old');
  const [status, setStatus] = useState(null);

  const runPayroll = async () => {
    setStatus('running');
    try {
      const res = await fetch('/api/payslips/run', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({period_start: periodStart, period_end: periodEnd, regime})
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data?.message || 'Failed');
      setStatus('done: created ' + (data.created?.length || 0));
    } catch (e) {
      setStatus('error');
    }
  };

  return (
    <div>
      <h1>Run Payroll (Admin)</h1>
      <div>
        <label>Period Start <input value={periodStart} onChange={e=>setPeriodStart(e.target.value)} /></label>
      </div>
      <div>
        <label>Period End <input value={periodEnd} onChange={e=>setPeriodEnd(e.target.value)} /></label>
      </div>
      <div>
        <label>Tax Regime
          <select value={regime} onChange={e=>setRegime(e.target.value)}>
            <option value="old">Old</option>
            <option value="new">New</option>
          </select>
        </label>
      </div>
      <div>
        <button onClick={runPayroll}>Run Payroll</button>
      </div>
      <div>Status: {status}</div>
    </div>
  );
}
