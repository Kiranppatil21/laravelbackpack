import React, {useState} from 'react';
import {Inertia} from '@inertiajs/inertia';

export default function CheckIn({employeeId, tenantUuid}){
  const [status, setStatus] = useState(null);

  const handleCheckIn = async () => {
    setStatus('sending');
    try {
      const res = await fetch('/api/attendance/checkin', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({employee_id: employeeId, tenant_uuid: tenantUuid, check_in_type: 'manual'})
      });
      if (!res.ok) throw new Error('Checkin failed');
      setStatus('checked-in');
    } catch (err) {
      setStatus('error');
    }
  };

  const handleCheckOut = async () => {
    setStatus('sending');
    try {
      const res = await fetch('/api/attendance/checkout', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({employee_id: employeeId, tenant_uuid: tenantUuid})
      });
      if (!res.ok) throw new Error('Checkout failed');
      setStatus('checked-out');
    } catch (err) {
      setStatus('error');
    }
  };

  return (
    <div>
      <h1>Attendance</h1>
      <p>Status: {status}</p>
      <button onClick={handleCheckIn}>Check In</button>
      <button onClick={handleCheckOut}>Check Out</button>
    </div>
  );
}
